<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\Admin\Profiles;
use App\Support\Admin\TeamActivity;
use App\Support\Admin\Today;
use App\Support\Admin\TodayBlocks;
use Illuminate\Http\Request;

/**
 * „Днес“ по човек: по подразбиране моят ден (възложените на мен и неразпределените) и какво съм направил днес.
 * Табовете отгоре показват деня на всеки от екипа или на целия екип (?user=all); общият акаунт „Админ“ (профил all)
 * започва от целия екип. Блоковете са по профила на човека, чийто ден гледаме (Profiles::todaySections).
 */
class TodayController extends Controller
{
    public function __invoke(Request $request)
    {
        $viewer = $request->user();
        $team = User::orderBy('name')->get(['id', 'name', 'profile']);
        $requested = $request->query('user');
        $member = match (true) {
            $requested === 'all' => null,
            $requested !== null && $team->contains('id', (int) $requested) => $team->firstWhere('id', (int) $requested),
            $requested === null && $viewer->isMaster() => null,
            default => $viewer,
        };
        $userId = $member?->id;
        $profile = $member ? $member->profile() : $viewer->profile();

        $from = TeamActivity::startOfToday();
        $sections = Profiles::todaySections($profile);
        $blocks = (new TodayBlocks($member, $profile === 'all' ? 4 : 7))->make(collect($sections)->flatten()->all());

        return view('admin.today', [
            'team' => $team,
            'member' => $member,
            'profile' => $profile,
            'sections' => $sections,
            'blocks' => $blocks,
            'kpis' => TodayBlocks::kpis($profile, $blocks),
            'done' => TeamActivity::entries($userId, $from),
            'doneCounts' => TeamActivity::counts($userId, $from),
            // Брояч на таба: колко има да свърши всеки (непрочетени и стъпки за днес на него или без човек).
            'todoByMember' => $team->mapWithKeys(fn ($u) => [$u->id => Today::urgentCountFor($u->id)]),
        ]);
    }
}
