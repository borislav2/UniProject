<?php

namespace App\Support\Admin;

use App\Models\ClientEmail;
use App\Models\LinkedinProspect;
use App\Models\OutreachCall;
use App\Models\Project;
use App\Models\ProjectNote;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Какво е направил човек от екипа за даден период: бележки и разговори по запитванията, смени на етапа,
 * имейли до клиенти, писма до бизнеси, обаждания от списъка и покани и съобщения в LinkedIn. „Днес“ показва деня, таблото седмицата по хора.
 */
class TeamActivity
{
    /** Начало на деня по София (в UTC), за да е „днес“ и за хората, които работят късно вечер. */
    public static function startOfToday(): Carbon
    {
        return now('Europe/Sofia')->startOfDay()->utc();
    }

    /**
     * Списък със записи [at, type, label, text, url], най-новите първи. $userId null = целият екип.
     *
     * @return Collection<int, array{at: Carbon, type: string, label: string, text: string, url: ?string, user: ?string}>
     */
    public static function entries(?int $userId, Carbon $from, ?Carbon $to = null): Collection
    {
        $to ??= now();

        $notes = ProjectNote::with(['project:id,name', 'user:id,name'])
            ->when($userId, fn ($q) => $q->where('user_id', $userId))
            ->whereNotNull('user_id')
            ->whereBetween('created_at', [$from, $to])->get()
            ->map(fn (ProjectNote $note) => [
                'at' => $note->created_at,
                'type' => $note->kind === 'call' ? 'call' : ($note->kind === 'stage' ? 'stage' : 'note'),
                'label' => ProjectNote::KINDS[$note->kind] ?? 'Бележка',
                'text' => ($note->project?->name ? $note->project->name . ': ' : '') . \Illuminate\Support\Str::limit($note->body, 140),
                'url' => $note->project_id ? route('admin.projects.show', $note->project_id) : null,
                'user' => $note->user?->name,
            ]);

        $emails = ClientEmail::with(['project:id,name', 'outreachProspect:id,company,email', 'user:id,name'])
            ->when($userId, fn ($q) => $q->where('user_id', $userId))
            ->whereNotNull('user_id')
            ->whereNull('error')
            ->whereBetween('sent_at', [$from, $to])->get()
            ->map(fn (ClientEmail $email) => [
                'at' => $email->sent_at,
                'type' => $email->outreach_prospect_id ? 'outreach' : 'email',
                'label' => $email->outreach_prospect_id ? 'Писмо до бизнес' : 'Имейл до клиент',
                'text' => ($email->project?->name ?? $email->outreachProspect?->company ?? $email->to_email) . ': ' . $email->subject,
                'url' => $email->project_id ? route('admin.projects.show', $email->project_id) : null,
                'user' => $email->user?->name,
            ]);

        $calls = OutreachCall::with('caller:id,name')
            ->when($userId, fn ($q) => $q->where('called_by', $userId))
            ->whereNotNull('called_by')
            ->whereBetween('called_at', [$from, $to])->get()
            ->map(fn (OutreachCall $call) => [
                'at' => $call->called_at,
                'type' => 'outreach-call',
                'label' => 'Обаждане до бизнес',
                'text' => $call->name . ': ' . (OutreachCall::STATUSES[$call->status] ?? $call->status),
                'url' => $call->project_id ? route('admin.projects.show', $call->project_id) : null,
                'user' => $call->caller?->name,
            ]);

        // LinkedIn: кой е пуснал покана и кой е писал (колоните invited_by / messaged_by се пишат при смяна на статуса).
        $linkedin = collect(['invited' => ['linkedin-invite', 'Покана в LinkedIn'], 'messaged' => ['linkedin-message', 'Съобщение в LinkedIn']])
            ->flatMap(fn ($meta, $step) => LinkedinProspect::with($step === 'invited' ? 'inviter:id,name' : 'messenger:id,name')
                ->when($userId, fn ($q) => $q->where($step . '_by', $userId))
                ->whereNotNull($step . '_by')
                ->whereBetween($step . '_at', [$from, $to])->get()
                ->map(fn (LinkedinProspect $p) => [
                    'at' => $p->{$step . '_at'},
                    'type' => $meta[0],
                    'label' => $meta[1],
                    'text' => $p->name . ($p->company ? ' (' . $p->company . ')' : ''),
                    'url' => $p->project_id ? route('admin.projects.show', $p->project_id) : route('admin.linkedin.index', ['q' => $p->name]),
                    'user' => ($step === 'invited' ? $p->inviter : $p->messenger)?->name,
                ]));

        return $notes->concat($emails)->concat($calls)->concat($linkedin)->sortByDesc('at')->values();
    }

    /** Броячи за периода: обаждания (до бизнеси и разговори по запитванията), имейли, писма до бизнеси, смени на етапа, спечелени. */
    public static function counts(?int $userId, Carbon $from, ?Carbon $to = null): array
    {
        $entries = self::entries($userId, $from, $to);

        return [
            'calls' => $entries->whereIn('type', ['call', 'outreach-call'])->count(),
            'emails' => $entries->where('type', 'email')->count(),
            'outreach' => $entries->where('type', 'outreach')->count(),
            'linkedin_invites' => $entries->where('type', 'linkedin-invite')->count(),
            'linkedin_messages' => $entries->where('type', 'linkedin-message')->count(),
            'stages' => $entries->where('type', 'stage')->count(),
            'notes' => $entries->where('type', 'note')->count(),
            'total' => $entries->count(),
        ];
    }

    /** Отворените запитвания на човека: колко води, колко са нови и колко имат просрочена стъпка. */
    public static function workload(int $userId): array
    {
        $open = Project::openLeads()->where('assigned_to', $userId);

        return [
            'open' => (clone $open)->count(),
            'new' => (clone $open)->where('stage', 'new')->count(),
            'overdue' => (clone $open)->whereNotNull('next_action_at')->where('next_action_at', '<', now())->count(),
            'won_month' => Project::where('assigned_to', $userId)->where('stage', 'won')->where('stage_changed_at', '>=', now('Europe/Sofia')->startOfMonth()->utc())->count(),
        ];
    }
}
