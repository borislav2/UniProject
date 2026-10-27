<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClientEmail;
use App\Models\OutreachCall;
use App\Models\OutreachProspect;
use App\Models\Project;
use Illuminate\Http\Request;

/** Фунията по канали за избран период: писма, обаждания и запитвания до спечелени клиенти. */
class StatsController extends Controller
{
    public const PERIODS = [7 => '7 дни', 30 => '30 дни', 90 => '90 дни', 365 => 'година'];

    public function __invoke(Request $request)
    {
        $days = array_key_exists((int) $request->query('days'), self::PERIODS) ? (int) $request->query('days') : 30;
        $from = now()->subDays($days);

        // Писма: от бизнесите, на които първото писмо е тръгнало в периода.
        $sent = OutreachProspect::whereNotNull('sent_at')->where('sent_at', '>=', $from);
        $emails = [
            ['Изпратени първи писма', (clone $sent)->count()],
            ['Кликнали', (clone $sent)->whereNotNull('clicked_at')->count()],
            ['Отговорили или запитване', (clone $sent)->where(fn ($q) => $q->where('status', 'replied')->orWhereNotNull('project_id'))->count()],
            ['Спечелени', (clone $sent)->whereHas('project', fn ($q) => $q->where('stage', 'won'))->count()],
        ];
        $followups = ClientEmail::where('template', 'outreach-followup')->whereNotNull('sent_at')->where('sent_at', '>=', $from)->count();

        // Обаждания: последният резултат на бизнесите, обадени в периода.
        $called = OutreachCall::whereNotNull('called_at')->where('called_at', '>=', $from);
        $calls = [
            ['Обадени бизнеси', (clone $called)->count()],
            ['Вдигнаха', (clone $called)->where('status', '!=', 'no_answer')->count()],
            ['Интерес', (clone $called)->whereIn('status', ['interested', 'client'])->count()],
            ['Спечелени', (clone $called)->whereHas('project', fn ($q) => $q->where('stage', 'won'))->count()],
        ];

        // Запитвания (сайт, Крео, проверка на сайт и превърнатите потенциални клиенти) по канал.
        $leads = Project::whereNotNull('stage')->where('created_at', '>=', $from)->get(['id', 'stage', 'lead_channel', 'lost_reason']);
        $channels = $leads->groupBy(fn ($lead) => $lead->lead_channel ?: 'Директно')->map(fn ($group, $channel) => [
            'channel' => $channel,
            'total' => $group->count(),
            'offer' => $group->whereIn('stage', ['offer', 'won'])->count(),
            'won' => $group->where('stage', 'won')->count(),
            'lost' => $group->where('stage', 'lost')->count(),
        ])->sortByDesc('total')->values();
        $pipeline = collect(Project::STAGES)->map(fn ($stage, $key) => [$stage[0], $leads->where('stage', $key)->count()])->values();
        $lostReasons = $leads->where('stage', 'lost')->map(fn ($lead) => trim((string) $lead->lost_reason) ?: 'Без причина')->countBy()->sortDesc();

        return view('admin.stats', compact('days', 'emails', 'followups', 'calls', 'channels', 'pipeline', 'lostReasons'));
    }
}
