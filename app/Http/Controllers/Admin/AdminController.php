<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChatConversation;
use App\Models\OutreachCall;
use App\Models\Post;
use App\Models\Project;
use App\Support\Outreach\Outreach;

class AdminController extends Controller
{
    /** Таблото: какво чака днес (запитвания, консултации, обаждания, писма), после общата картина. */
    public function dashboard()
    {
        $leads = Project::where('source', 'website');

        $leadChannels = (clone $leads)
            ->where('created_at', '>=', now()->subDays(30))
            ->selectRaw('COALESCE(lead_channel, ?) as channel, COUNT(*) as total', ['Директно'])
            ->groupBy('channel')
            ->orderByDesc('total')
            ->get();

        return view('admin.dashboard', [
            'unread' => (clone $leads)->whereNull('read_at')->count(),
            'recentLeads' => (clone $leads)->latest()->take(5)->get(),
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
            'nextPost' => Post::where('status', 'published')->where('published_at', '>', now())->orderBy('published_at')->first(),
            'mailNotConfigured' => in_array(config('mail.default'), ['log', 'array'], true),
            'leadChannels' => $leadChannels,
        ]);
    }
}
