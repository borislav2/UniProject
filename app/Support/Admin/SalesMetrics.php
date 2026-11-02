<?php

namespace App\Support\Admin;

use App\Models\ClientEmail;
use App\Models\Project;
use App\Models\ProjectNote;
use Illuminate\Support\Carbon;

/** Цифрите на таблото: запитванията през месеца спрямо миналия, етапите, бързината на отговор и източниците. */
class SalesMetrics
{
    /** Броячи за периода: нови запитвания, свързахме се, оферти, спечелени, загубени (по датата на смяна на етапа). */
    public static function period(Carbon $from, Carbon $to): array
    {
        $leads = fn () => Project::where('source', 'website');
        $reached = fn (array $stages) => $leads()->whereIn('stage', $stages)->whereBetween('stage_changed_at', [$from, $to])->count();

        return [
            'leads' => $leads()->whereBetween('created_at', [$from, $to])->count(),
            'contacted' => $reached(['contacted']),
            'offer' => $reached(['offer']),
            'won' => $reached(['won']),
            'lost' => $reached(['lost']),
        ];
    }

    /** Този месец и миналият (по София), за сравнение „↑/↓ спрямо миналия месец“. */
    public static function months(): array
    {
        $start = now('Europe/Sofia')->startOfMonth();

        return [
            'this' => self::period($start->copy()->utc(), now()),
            // Миналият месец до същия ден, за да е честно сравнението по средата на месеца.
            'last' => self::period($start->copy()->subMonthNoOverflow()->utc(), now('Europe/Sofia')->subMonthNoOverflow()->utc()),
        ];
    }

    /** Отворените запитвания по етап и колко от тях нямат следваща стъпка или човек. */
    public static function pipeline(): array
    {
        $byStage = Project::openLeads()->selectRaw('stage, COUNT(*) as total')->groupBy('stage')->pluck('total', 'stage');

        return [
            'stages' => collect(Project::OPEN_STAGES)->mapWithKeys(fn ($s) => [$s => (int) ($byStage[$s] ?? 0)])->all(),
            'no_next_step' => Project::openLeads()->whereNull('next_action_at')->count(),
            'unassigned' => Project::openLeads()->whereNull('assigned_to')->count(),
        ];
    }

    /**
     * Колко бързо отговаряме: от създаването на запитването до първия запис от човек (разговор, бележка, етап)
     * или първия изпратен имейл. Запитванията от последните 30 дни; без отговор = чакат още.
     */
    public static function responseTime(int $days = 30): array
    {
        $leads = Project::where('source', 'website')->where('created_at', '>=', now()->subDays($days))->get(['id', 'created_at']);
        if ($leads->isEmpty()) {
            return ['average_hours' => null, 'within_hour' => 0, 'answered' => 0, 'waiting' => 0];
        }
        $ids = $leads->pluck('id');
        $firstNote = ProjectNote::whereIn('project_id', $ids)->whereNotNull('user_id')->selectRaw('project_id, MIN(created_at) as at')->groupBy('project_id')->pluck('at', 'project_id');
        $firstEmail = ClientEmail::whereIn('project_id', $ids)->whereNull('error')->whereNotNull('sent_at')->selectRaw('project_id, MIN(sent_at) as at')->groupBy('project_id')->pluck('at', 'project_id');

        $hours = $leads->map(function ($lead) use ($firstNote, $firstEmail) {
            $times = collect([$firstNote[$lead->id] ?? null, $firstEmail[$lead->id] ?? null])->filter()->map(fn ($t) => Carbon::parse($t));

            return $times->isEmpty() ? null : max(0, $lead->created_at->diffInMinutes($times->min())) / 60;
        });
        $answered = $hours->filter(fn ($h) => $h !== null);

        return [
            'average_hours' => $answered->isEmpty() ? null : round($answered->avg(), 1),
            'within_hour' => $answered->filter(fn ($h) => $h <= 1)->count(),
            'answered' => $answered->count(),
            'waiting' => $hours->count() - $answered->count(),
        ];
    }

    /** Запитванията от последните N дни по вид: форма, оферта, проверка на сайт, Крео. */
    public static function kinds(int $days = 30): array
    {
        $counts = array_fill_keys(array_keys(Project::LEAD_KINDS), 0);
        Project::where('source', 'website')->where('created_at', '>=', now()->subDays($days))->get(['id', 'source', 'service'])
            ->each(function ($lead) use (&$counts) {
                $counts[$lead->leadKind()]++;
            });

        return $counts;
    }
}
