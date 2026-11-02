<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChatConversation;
use App\Models\OutreachCall;
use App\Models\Post;
use App\Models\Project;
use App\Models\User;
use App\Support\Admin\SalesMetrics;
use App\Support\Admin\TeamActivity;
use App\Support\Outreach\Outreach;

class AdminController extends Controller
{
    /**
     * Таблото: какво чака днес, после продажбите този месец спрямо миналия, екипът тази седмица (по човек),
     * бързината на отговор, последните запитвания, консултациите и откъде идват клиентите.
     */
    public function dashboard()
    {
        $leads = Project::where('source', 'website');

        $leadChannels = (clone $leads)
            ->where('created_at', '>=', now()->subDays(30))
            ->selectRaw('COALESCE(lead_channel, ?) as channel, COUNT(*) as total', ['Директно'])
            ->groupBy('channel')
            ->orderByDesc('total')
            ->get();

        $weekStart = now('Europe/Sofia')->startOfWeek()->utc();
        $team = User::orderBy('name')->get(['id', 'name'])->map(fn (User $user) => [
            'user' => $user,
            'work' => TeamActivity::workload($user->id),
            'week' => TeamActivity::counts($user->id, $weekStart),
            'today' => TeamActivity::counts($user->id, TeamActivity::startOfToday())['total'],
        ]);

        return view('admin.dashboard', [
            'unread' => Project::formLeads()->whereNull('read_at')->count(),
            'unreadChecks' => Project::siteChecks()->whereNull('read_at')->count(),
            'overdue' => Project::openLeads()->whereNotNull('next_action_at')->where('next_action_at', '<', now())->count(),
            'recentLeads' => (clone $leads)->with('assignee')->latest()->take(6)->get(),
            'consultations' => Project::whereNotNull('consultation_at')
                ->whereBetween('consultation_at', [now()->subHour(), now()->addDays(14)])
                ->orderBy('consultation_at')->take(6)->get(),
            'calls' => OutreachCall::todayStats(),
            'callbacks' => OutreachCall::whereNotNull('callback_on')->whereDate('callback_on', '<=', today())->count(),
            'outreach' => [
                'configured' => Outreach::configured(),
                'sent' => Outreach::sentToday(),
                'limit' => (int) config('creatium.outreach.daily_limit'),
                'clicked' => \App\Models\OutreachProspect::where('last_clicked_at', '>=', now()->subDays(7))->count(),
            ],
            'chats' => ChatConversation::where('updated_at', '>=', now()->subDays(7))->count(),
            'activeProjects' => Project::whereIn('status', ['In Progress', 'On Hold'])->count(),
            'mrr' => Project::subscribers()->sum('monthly_fee'),
            'nextPost' => Post::where('status', 'published')->where('published_at', '>', now())->orderBy('published_at')->first(),
            'mailNotConfigured' => in_array(config('mail.default'), ['log', 'array'], true),
            'leadChannels' => $leadChannels,
            'months' => SalesMetrics::months(),
            'pipeline' => SalesMetrics::pipeline(),
            'response' => SalesMetrics::responseTime(),
            'kinds' => SalesMetrics::kinds(),
            'team' => $team,
        ]);
    }
}
