<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\OutreachMail;
use App\Models\ClientEmail;
use App\Models\OutreachProspect;
use App\Models\OutreachSuppression;
use App\Support\Outreach\Outreach;
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

        return view('admin.outreach.index', [
            'prospects' => OutreachProspect::query()
                ->when($status && array_key_exists($status, OutreachProspect::STATUSES), fn ($q) => $q->where('status', $status))
                ->orderByRaw("status = 'new' desc")->orderByDesc('score')->latest()
                ->paginate(30)->withQueryString(),
            'counts' => OutreachProspect::selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status'),
            'status' => $status,
            'configured' => Outreach::configured(),
            'remaining' => Outreach::remainingToday(),
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

    public function compose(Request $request, OutreachProspect $prospect)
    {
        return view('admin.outreach.compose', [
            'prospect' => $prospect,
            'defaults' => [
                'subject' => 'Въпрос за сайта на ' . $prospect->displayName(),
                'first_name' => $prospect->first_name,
                'observation' => '',
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
            'observation' => 'required|string|min:20|max:600',
            'sender' => 'required|string|max:100',
        ], [
            'observation.required' => 'Напишете какво забелязахте за бизнеса им. Това е причината да им пишем.',
            'observation.min' => 'Наблюдението е твърде кратко. Едно-две конкретни изречения.',
        ]);

        if (! Outreach::configured()) {
            return back()->withInput()->with('send_error', 'Пощата за потенциални клиенти не е настроена (OUTREACH_MAIL_*). Пуснете на сървъра bash deploy/configure-outreach-mail.sh.');
        }
        if (! $prospect->canBeEmailed()) {
            return back()->withInput()->with('send_error', 'На този адрес не може да се пише: вече е изпратено, адресът не е потвърден или е отписан.');
        }
        if (Outreach::remainingToday() < 1) {
            return back()->withInput()->with('send_error', 'Дневният лимит от ' . config('creatium.outreach.daily_limit') . ' писма е достигнат. Продължете утре, за да не попаднем в спам.');
        }

        $data = ['first_name' => $validated['first_name'] ?? null, 'observation' => trim($validated['observation']), 'sender' => $validated['sender']];
        $log = ClientEmail::create([
            'outreach_prospect_id' => $prospect->id,
            'user_id' => $request->user()->id,
            'template' => 'outreach',
            'to_email' => $prospect->email,
            'subject' => $validated['subject'],
            'data' => $data,
        ]);

        try {
            Mail::mailer('outreach')->to($prospect->email)->send(new OutreachMail($prospect, $data, $validated['subject']));
        } catch (Throwable $e) {
            Log::error('Outreach email failed', ['prospect_id' => $prospect->id, 'error' => $e->getMessage()]);
            $log->forceFill(['error' => mb_substr($e->getMessage(), 0, 1000)])->save();

            return back()->withInput()->with('send_error', 'Писмото не беше изпратено: ' . $e->getMessage());
        }

        $log->forceFill(['sent_at' => now()])->save();
        $prospect->forceFill(['status' => 'sent', 'sent_at' => now()])->save();

        return redirect()->route('admin.outreach.index')->with('success', 'Писмото до ' . $prospect->displayName() . ' е изпратено.');
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
}
