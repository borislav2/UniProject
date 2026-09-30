<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\Category;
use App\Models\Technology;
use App\Models\User;

class AdminController extends Controller
{
    public function dashboard()
    {
        $stats = [
            'total_projects' => Project::count(),
            'total_categories' => Category::count(),
            'total_technologies' => Technology::count(),
            'total_users' => User::count(),
            'completed_projects' => Project::where('status', 'Completed')->count(),
            'in_progress_projects' => Project::where('status', 'In Progress')->count(),
        ];
        
        $recentProjects = Project::with(['category', 'technologies'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();
        
        $leadChannels = Project::where('source', 'website')
            ->where('created_at', '>=', now()->subDays(30))
            ->selectRaw('COALESCE(lead_channel, ?) as channel, COUNT(*) as total', ['Директно'])
            ->groupBy('channel')
            ->orderByDesc('total')
            ->get();

        return view('admin.dashboard', compact('stats', 'recentProjects', 'leadChannels'));
    }
}
