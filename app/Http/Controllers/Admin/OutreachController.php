<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\OutreachMail;
use App\Models\ClientEmail;
use App\Models\OutreachProspect;
use App\Models\OutreachSuppression;
use App\Support\Outreach\Outreach;
use App\Support\Outreach\ProspectChecker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Throwable;

/** „Потенциални клиенти“: внос на бизнеси от Harvey и първо писмо до всеки, изпратено ръчно след преглед. */
class OutreachController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status');
        $clicked = $request->boolean('clicked');
        $followup = $request->boolean('followup');

        return view('admin.outreach.index', [
            'prospects' => OutreachProspect::query()
                ->when($status && array_key_exists($status, OutreachProspect::STATUSES), fn ($q) => $q->where('status', $status))
                ->when($clicked, fn ($q) => $q->whereNotNull('clicked_at')->orderByDesc('last_clicked_at'))
                ->when($followup, fn ($q) => $q->dueForFollowUp()->orderBy('sent_at'))
                ->orderByRaw("status = 'new' desc")->orderByDesc('score')->latest()
                ->paginate(30)->withQueryString(),
            'clicked' => $clicked,
            'followup' => $followup,
            'followupIds' => OutreachProspect::dueForFollowUp()->pluck('id')->all(),
            'clickedCount' => OutreachProspect::whereNotNull('clicked_at')->count(),
            'counts' => OutreachProspect::selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status'),
            'status' => $status,
            'configured' => Outreach::configured(),
            'remaining' => Outreach::remainingToday(),
            'unchecked' => ProspectChecker::pending()->count(),
            'serverCsv' => ($time = collect(self::serverFiles())->map(fn ($p) => filemtime($p))->max()) ? \Illuminate\Support\Carbon::createFromTimestamp($time) : null,
        ]);
    }

    public function import(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:csv,txt|max:5120'], ['file.required' => 'Изберете CSV файл от Harvey.']);

        $result = Outreach::import($request->file('file')->getRealPath());

        return redirect()->route('admin.outreach.index')->with('success', "Внесени: {$result['added']} нови, {$result['updated']} обновени, {$result['skipped']} пропуснати (невалидни, повтарящи се или отписани).");
    }

    /** Внася последните списъци, изнесени от Harvey на сървъра (без качване на файл). */
    public function importServer()
    {
        $files = self::serverFiles();
        if ($files === []) {
            return redirect()->route('admin.outreach.index')->with('send_error', 'Няма списък от Harvey на сървъра (' . config('creatium.outreach.harvey_csv') . '). Изнася се всеки ден в 06:30.');
        }

        $result = ['added' => 0, 'updated' => 0, 'skipped' => 0];
        foreach ($files as $path) {
            foreach (Outreach::import($path) as $key => $count) {
                $result[$key] += $count;
            }
        }

        return redirect()->route('admin.outreach.index')->with('success', "От сървъра: {$result['added']} нови, {$result['updated']} обновени, {$result['skipped']} пропуснати.");
    }

    /** Списъците с имейли на сървъра: хората, намерени от Harvey, и имейлите от сайтовете. */
    private static function serverFiles(): array
    {
        return array_values(array_filter(
            [config('creatium.outreach.harvey_csv'), config('creatium.outreach.harvey_site_emails_csv')],
            fn ($path) => $path && is_readable($path),
        ));
    }

    /** Една порция проверки (до ~20 секунди); страницата я вика отново, докато не остане непроверен бизнес. */
    public function check(ProspectChecker $checker)
    {
        ignore_user_abort(true);
        set_time_limit(120);

        return response()->json($checker->work());
    }

    public function compose(Request $request, OutreachProspect $prospect)
    {
        return view('admin.outreach.compose', [
            'prospect' => $prospect,
            'defaults' => [
                'subject' => self::defaultSubject($prospect),
                'first_name' => $prospect->first_name,
                'observation' => $prospect->suggestedObservation(),
                'sender' => $request->user()->name,
            ],
            'configured' => Outreach::configured(),
            'remaining' => Outreach::remainingToday(),
        ]);
    }

    public function preview(Request $request, OutreachProspect $prospect)
    {
        $data = [
            'first_name' => trim((string) $request->input('first_name')),
            'observation' => trim((string) $request->input('observation')) ?: '[Какво забелязахте за бизнеса им]',
            'sender' => trim((string) $request->input('sender')) ?: '[Подпис]',
        ];

        return (new OutreachMail($prospect, $data, (string) $request->input('subject')))->render();
    }

    public function send(Request $request, OutreachProspect $prospect)
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:150',
            'first_name' => 'nullable|string|max:60',
            'observation' => 'required|string|min:20|max:900',
            'sender' => 'required|string|max:100',
        ], [
            'observation.required' => 'Напишете какво забелязахте за бизнеса им. Това е причината да им пишем.',
            'observation.min' => 'Наблюдението е твърде кратко. Едно-две конкретни изречения.',
        ]);

        $data = ['first_name' => $validated['first_name'] ?? null, 'observation' => trim($validated['observation']), 'sender' => $validated['sender']];
        if ($error = $this->deliver($prospect, $validated['subject'], $data, $request->user()->id)) {
            return back()->withInput()->with('send_error', $error);
        }

        return redirect()->route('admin.outreach.index')->with('success', 'Писмото до ' . $prospect->displayName() . ' е изпратено.');
    }

    /** Изпраща на избраните с готовото писмо от проверката на сайта им, до дневния лимит. */
    public function sendSelected(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array|max:50',
            'ids.*' => 'integer',
        ], ['ids.required' => 'Изберете поне един бизнес.']);

        $sent = 0;
        $errors = [];
        foreach (OutreachProspect::whereIn('id', $validated['ids'])->orderByDesc('score')->get() as $prospect) {
            $observation = $prospect->suggestedObservation();
            if ($observation === '') {
                $errors[] = $prospect->displayName() . ': няма проверка на сайта, напишете писмото ръчно';
                continue;
            }
            $data = ['first_name' => $prospect->first_name, 'observation' => $observation, 'sender' => $request->user()->name];
            if ($error = $this->deliver($prospect, self::defaultSubject($prospect), $data, $request->user()->id)) {
                $errors[] = $prospect->displayName() . ': ' . $error;
                if (! Outreach::configured() || Outreach::remainingToday() < 1) {
                    break;
                }
                continue;
            }
            $sent++;
        }

        $redirect = redirect()->route('admin.outreach.index');
        if ($errors) {
            $redirect->with('send_error', implode(' · ', array_slice($errors, 0, 5)) . (count($errors) > 5 ? ' …' : ''));
        }

        return $redirect->with('success', "Изпратени: {$sent}.");
    }

    public static function defaultSubject(OutreachProspect $prospect): string
    {
        return $prospect->findings
            ? 'Резултати от проверка на сайта на ' . $prospect->displayName()
            : 'Видимост на ' . $prospect->displayName() . ' в Google и Google Maps';
    }

    /** Изпраща едно писмо през отделната поща и го записва. Връща текста на грешката или null. */
    /** Едно напомняне до бизнес, който не е отговорил (бутон „Напомни“). */
    public function followUp(Request $request, OutreachProspect $prospect)
    {
        if ($error = $this->deliverFollowUp($prospect, $request->user())) {
            return back()->with('send_error', $error);
        }

        return back()->with('success', 'Напомнянето до ' . $prospect->displayName() . ' е изпратено.');
    }

    /** Напомняне до всички, на които е време, в рамките на дневния лимит. */
    public function followUpAll(Request $request)
    {
        $sent = 0;
        $errors = [];
        foreach (OutreachProspect::dueForFollowUp()->orderByDesc('last_clicked_at')->orderBy('sent_at')->limit(50)->get() as $prospect) {
            if ($error = $this->deliverFollowUp($prospect, $request->user())) {
                $errors[] = $prospect->displayName() . ': ' . $error;
                if (! Outreach::configured() || Outreach::remainingToday() < 1) {
                    break;
                }
                continue;
            }
            $sent++;
        }

        $redirect = redirect()->route('admin.outreach.index');
        if ($errors) {
            $redirect->with('send_error', implode(' · ', array_slice($errors, 0, 3)) . (count($errors) > 3 ? ' …' : ''));
        }

        return $redirect->with('success', "Изпратени напомняния: {$sent}.");
    }

    private function deliverFollowUp(OutreachProspect $prospect, $user): ?string
    {
        if (! Outreach::configured()) {
            return 'Пощата за потенциални клиенти не е настроена (OUTREACH_MAIL_*).';
        }
        if (! $prospect->canFollowUp()) {
            return 'Не е време за напомняне: още не са минали ' . config('creatium.outreach.followup_after_days') . ' дни, вече е напомнено, отговорили са или са отписани.';
        }
        if (Outreach::remainingToday() < 1) {
            return 'Дневният лимит от ' . config('creatium.outreach.daily_limit') . ' писма е достигнат. Продължете утре.';
        }

        $first = ClientEmail::where('outreach_prospect_id', $prospect->id)->where('template', 'outreach')->whereNotNull('sent_at')->latest('sent_at')->first();
        $subject = 'Re: ' . ($first?->subject ?? self::defaultSubject($prospect));
        $data = [
            'first_name' => $prospect->first_name,
            'sender' => $user->name,
            'first_date' => $prospect->sent_at->timezone('Europe/Sofia')->format('d.m.Y'),
            'finding' => $prospect->findings[0]['title'] ?? null,
        ];

        $log = ClientEmail::create([
            'outreach_prospect_id' => $prospect->id, 'user_id' => $user->id, 'template' => 'outreach-followup',
            'to_email' => $prospect->email, 'subject' => $subject, 'data' => $data,
        ]);
        try {
            Mail::mailer('outreach')->to($prospect->email)->send(new \App\Mail\OutreachFollowUpMail($prospect, $data, $subject));
        } catch (Throwable $e) {
            Log::error('Outreach follow-up failed', ['prospect_id' => $prospect->id, 'error' => $e->getMessage()]);
            $log->forceFill(['error' => mb_substr($e->getMessage(), 0, 1000)])->save();

            return 'Напомнянето не беше изпратено: ' . $e->getMessage();
        }
        $log->forceFill(['sent_at' => now()])->save();
        $prospect->forceFill(['followup_sent_at' => now()])->save();

        return null;
    }

    private function deliver(OutreachProspect $prospect, string $subject, array $data, int $userId): ?string
    {
        if (! Outreach::configured()) {
            return 'Пощата за потенциални клиенти не е настроена (OUTREACH_MAIL_*). Пуснете на сървъра bash deploy/configure-outreach-mail.sh.';
        }
        if (! $prospect->canBeEmailed()) {
            return 'На този адрес не може да се пише: вече е изпратено, адресът не е потвърден или е отписан.';
        }
        if (Outreach::remainingToday() < 1) {
            return 'Дневният лимит от ' . config('creatium.outreach.daily_limit') . ' писма е достигнат. Продължете утре, за да не попаднем в спам.';
        }

        $log = ClientEmail::create([
            'outreach_prospect_id' => $prospect->id,
            'user_id' => $userId,
            'template' => 'outreach',
            'to_email' => $prospect->email,
            'subject' => $subject,
            'data' => $data,
        ]);

        try {
            Mail::mailer('outreach')->to($prospect->email)->send(new OutreachMail($prospect, $data, $subject));
        } catch (Throwable $e) {
            Log::error('Outreach email failed', ['prospect_id' => $prospect->id, 'error' => $e->getMessage()]);
            $log->forceFill(['error' => mb_substr($e->getMessage(), 0, 1000)])->save();

            return 'Писмото не беше изпратено: ' . $e->getMessage();
        }

        $log->forceFill(['sent_at' => now()])->save();
        $prospect->forceFill(['status' => 'sent', 'sent_at' => now()])->save();

        return null;
    }

    public function update(Request $request, OutreachProspect $prospect)
    {
        $validated = $request->validate(['status' => ['required', Rule::in(['new', 'replied', 'skipped', 'unsubscribed'])]]);

        if ($validated['status'] === 'unsubscribed') {
            OutreachSuppression::add($prospect->email, 'manual');
            $prospect->forceFill(['unsubscribed_at' => now()]);
        }
        if ($validated['status'] === 'new' && ($prospect->sent_at || OutreachSuppression::where('email', $prospect->email)->exists())) {
            return back()->with('send_error', 'Писмото вече е изпратено или адресът е отписан: не може да се върне като нов.');
        }
        $prospect->forceFill(['status' => $validated['status']])->save();

        return back()->with('success', $prospect->displayName() . ': ' . OutreachProspect::STATUSES[$validated['status']] . '.');
    }

    /** Проявен интерес: прави запитване с данните на бизнеса и отваря го (повторно натискане отваря същото). */
    public function toLead(Request $request, OutreachProspect $prospect)
    {
        $lead = \App\Support\Admin\LeadConverter::fromProspect($prospect, $request->user());

        return redirect()->route('admin.projects.show', $lead)->with('success', 'Запитването е създадено. Запишете следващата стъпка.');
    }
}
