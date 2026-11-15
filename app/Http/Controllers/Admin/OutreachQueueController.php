<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OutreachCall;
use App\Models\OutreachProspect;
use App\Support\Outreach\Drafts;
use App\Support\Outreach\Markets;
use App\Support\Outreach\Outreach;
use App\Support\Outreach\Sender;
use Illuminate\Http\Request;

/**
 * „За одобрение“: бизнесите, които автопилотът (или „Търсене“) е намерил, оценил и за които е подготвил чернова.
 * Нищо не излиза без човек: „Одобри и изпрати“ минава през Support\Outreach\Sender (отделна поща, дневен лимит, правилата на пазара).
 */
class OutreachQueueController extends Controller
{
    public function index(Request $request)
    {
        $country = Markets::exists($request->query('country')) ? $request->query('country') : null;

        $prospects = OutreachProspect::awaitingApproval()->with('approver:id,name')
            ->when($country, fn ($q) => $q->where('country', $country))
            ->orderByDesc('score')->orderBy('id')->paginate(15)->withQueryString();

        return view('admin.outreach.queue', [
            'prospects' => $prospects,
            'country' => $country,
            'total' => OutreachProspect::awaitingApproval()->count(),
            'byCountry' => OutreachProspect::awaitingApproval()->selectRaw('country, count(*) as total')->groupBy('country')->pluck('total', 'country'),
            'configured' => Outreach::configured(),
            'remaining' => Outreach::remainingToday(),
            'summaries' => $prospects->mapWithKeys(fn ($p) => [$p->id => Drafts::summaryBg($p)]),
        ]);
    }

    /** „Запази черновата“: темата и писмото, без да се праща. */
    public function saveDraft(Request $request, OutreachProspect $prospect)
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:150',
            'observation' => 'required|string|min:20|max:900',
        ]);
        $prospect->forceFill(['draft_subject' => $validated['subject'], 'draft_observation' => trim($validated['observation'])])->save();

        return $this->back('Черновата за ' . $prospect->displayName() . ' е запазена.', $prospect);
    }

    /** „Одобри и изпрати“: единственото място в тази страница, което изпраща, и то само с клик. */
    public function approve(Request $request, OutreachProspect $prospect)
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:150',
            'first_name' => 'nullable|string|max:60',
            'observation' => 'required|string|min:20|max:900',
            'sender' => 'required|string|max:100',
        ], ['observation.min' => 'Писмото е твърде кратко. Едно-две конкретни изречения.']);

        $data = ['first_name' => $validated['first_name'] ?? $prospect->first_name, 'observation' => trim($validated['observation']), 'sender' => $validated['sender']];
        if ($error = Sender::deliver($prospect, $validated['subject'], $data, $request->user()->id)) {
            return $this->back($error, $prospect, 'send_error');
        }

        return $this->back('Писмото до ' . $prospect->displayName() . ' е изпратено.', null);
    }

    /** „Одобри избраните“: готовите чернови на избраните по оценка, най-много до оставащия дневен лимит. */
    public function approveSelected(Request $request)
    {
        $validated = $request->validate(['ids' => 'required|array|max:50', 'ids.*' => 'integer'], ['ids.required' => 'Изберете поне един бизнес.']);

        $room = Outreach::remainingToday();
        $sent = 0;
        $errors = [];
        $left = 0;
        foreach (OutreachProspect::awaitingApproval()->whereIn('id', $validated['ids'])->orderByDesc('score')->get() as $prospect) {
            if ($sent >= $room) {
                $left++;
                continue;
            }
            if (blank($prospect->draft_subject) || blank($prospect->draft_observation)) {
                $errors[] = $prospect->displayName() . ': няма чернова за писмо';
                continue;
            }
            $data = ['first_name' => $prospect->first_name, 'observation' => $prospect->draft_observation, 'sender' => $request->user()->name];
            if ($error = Sender::deliver($prospect, $prospect->draft_subject, $data, $request->user()->id)) {
                $errors[] = $prospect->displayName() . ': ' . $error;
                if (! Outreach::configured() || Outreach::remainingToday() < 1) {
                    break;
                }
                continue;
            }
            $sent++;
        }

        $redirect = redirect()->route('admin.outreach.queue');
        if ($left) {
            $errors[] = "Дневният лимит стигна: {$left} остават за утре";
        }
        if ($errors) {
            $redirect->with('send_error', implode(' · ', array_slice($errors, 0, 5)) . (count($errors) > 5 ? ' …' : ''));
        }

        return $redirect->with('success', "Изпратени: {$sent}.");
    }

    public function skip(OutreachProspect $prospect)
    {
        $prospect->forceFill(['status' => 'skipped', 'draft_at' => null])->save();

        return $this->back($prospect->displayName() . ' е пропуснат.', null);
    }

    /**
     * „→ Обаждане“ или „→ LinkedIn“ за бизнес, на който не се пише по имейл. Обаждането е запис в списъка за обаждане
     * (един на бизнес), със страната и езика; LinkedIn отваря формата за добавяне, попълнена с данните на бизнеса.
     */
    public function reroute(Request $request, OutreachProspect $prospect)
    {
        $to = $request->input('to') === 'linkedin' ? 'linkedin' : 'call';
        $host = $prospect->website ? preg_replace('/^www\./', '', (string) parse_url($prospect->website, PHP_URL_HOST)) : null;

        if ($to === 'call') {
            $finding = $prospect->findings[0] ?? null;
            $finding = $finding ? ($prospect->mailLanguage() === 'en' ? (OutreachProspect::findingTextEn($finding)[0] ?? null) : $finding['title']) : null;
            $call = OutreachCall::firstOrNew(['external_id' => 'prospect:' . $prospect->id]);
            if (! $call->exists) {
                $call->fill([
                    'name' => $prospect->displayName(),
                    'phone' => $prospect->phone,
                    'industry' => $prospect->industry,
                    'city' => $prospect->city,
                    'country' => $prospect->country ?: 'bg',
                    'language' => $prospect->mailLanguage(),
                    'source_url' => $prospect->website && str_starts_with($prospect->website, 'https://') ? $prospect->website : null,
                    'note' => trim(implode("\n", array_filter([
                        'От „За одобрение“, без имейл.',
                        $host ? 'Сайт ' . $host : null,
                        $prospect->company_evidence ? 'На сайта пише ' . $prospect->company_evidence : null,
                        $finding ? 'Finding ' . $finding : null,
                    ]))),
                ])->save();
            }
            $prospect->forceFill(['status' => 'routed', 'draft_at' => null])->save();

            return $this->back($prospect->displayName() . ' е в „Без сайт, за обаждане“' . ($prospect->phone ? '.' : '. Няма телефон, намерете го на сайта.'), null);
        }

        $prospect->forceFill(['status' => 'routed', 'draft_at' => null])->save();

        return redirect()->route('admin.linkedin.index', array_filter([
            'company' => $prospect->displayName(), 'city' => $prospect->city, 'industry' => $prospect->industry,
            'website' => $prospect->website, 'email' => $prospect->email, 'phone' => $prospect->phone,
        ]))->with('success', 'Добавете профила или фирмената страница от LinkedIn. Данните на ' . $prospect->displayName() . ' са попълнени.');
    }

    private function back(string $message, ?OutreachProspect $anchor, string $flash = 'success')
    {
        return redirect()->to(route('admin.outreach.queue') . ($anchor ? '#p' . $anchor->id : ''))->with($flash, $message);
    }
}
