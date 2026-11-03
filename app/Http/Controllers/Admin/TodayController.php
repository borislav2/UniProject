<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OutreachCall;
use App\Models\User;
use App\Support\Admin\TeamActivity;
use App\Support\Admin\Today;
use Illuminate\Http\Request;

/**
 * „Днес“ по човек: по подразбиране моят ден (възложените на мен и неразпределените) и какво съм направил днес.
 * Табовете отгоре показват деня на всеки от екипа или на целия екип (?user=all).
 */
class TodayController extends Controller
{
    public function __invoke(Request $request)
    {
        $team = User::orderBy('name')->get(['id', 'name']);
        $requested = $request->query('user');
        $member = match (true) {
            $requested === 'all' => null,
            $requested !== null && $team->contains('id', (int) $requested) => $team->firstWhere('id', (int) $requested),
            default => $request->user(),
        };
        $userId = $member?->id;
        $only = fn ($items) => $userId ? $items->filter(fn ($p) => ! $p->assigned_to || $p->assigned_to === $userId)->values() : $items;

        $from = TeamActivity::startOfToday();

        return view('admin.today', [
            'team' => $team,
            'member' => $member,
            'due' => $only(Today::due()),
            'unread' => $only(Today::unread()),
            'withoutNextStep' => $only(Today::withoutNextStep()),
            'consultations' => $only(Today::consultations()),
            'callbacks' => Today::callbacks(),
            'linkedinDue' => Today::linkedinDue($userId),
            'calls' => OutreachCall::todayStats(),
            'hot' => Today::hotProspects(),
            'clientIssues' => Today::clientIssues(),
            'followups' => \App\Models\OutreachProspect::dueForFollowUp()->count(),
            'done' => TeamActivity::entries($userId, $from),
            'doneCounts' => TeamActivity::counts($userId, $from),
            // Брояч на таба: колко има да свърши всеки (непрочетени и стъпки за днес на него или без човек).
            'todoByMember' => $team->mapWithKeys(fn ($u) => [$u->id => Today::urgentCountFor($u->id)]),
        ]);
    }
}
