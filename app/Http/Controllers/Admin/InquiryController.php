<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;

class InquiryController extends Controller
{
    public function index(Request $request)
    {
        $filter = $request->query('filter') === 'unread' ? 'unread' : 'all';

        $inquiries = Project::where('source', 'website')
            ->when($filter === 'unread', fn ($q) => $q->whereNull('read_at'))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.inquiries.index', [
            'inquiries' => $inquiries,
            'filter' => $filter,
            'unreadCount' => Project::where('source', 'website')->whereNull('read_at')->count(),
            'totalCount' => Project::where('source', 'website')->count(),
            'mailNotConfigured' => in_array(config('mail.default'), ['log', 'array'], true),
            'notifyEmail' => config('creatium.notify_email'),
        ]);
    }

    public function read(Project $project)
    {
        $this->website($project)->forceFill(['read_at' => $project->read_at ?? now()])->save();

        return back();
    }

    public function unread(Project $project)
    {
        $this->website($project)->forceFill(['read_at' => null])->save();

        return back();
    }

    public function readAll()
    {
        Project::where('source', 'website')->whereNull('read_at')->update(['read_at' => now()]);

        return back()->with('success', 'Всички запитвания са маркирани като прочетени.');
    }

    private function website(Project $project): Project
    {
        abort_unless($project->source === 'website', 404);

        return $project;
    }
}
