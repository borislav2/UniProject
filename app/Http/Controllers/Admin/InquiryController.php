<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * „Запитвания“ (формата за контакт и „Получете оферта“) и „Проверки на сайт“ (безплатната проверка):
 * една и съща страница с табове по етап (Нови, Свързахме се, …), непрочетени и по човек от екипа.
 */
class InquiryController extends Controller
{
    public const TABS = [
        'all' => 'Всички',
        'new' => 'Нови',
        'contacted' => 'Свързахме се',
        'offer' => 'Оферта',
        'won' => 'Спечелени',
        'lost' => 'Загубени',
    ];

    public function index(Request $request)
    {
        return $this->list($request, 'form');
    }

    public function siteChecks(Request $request)
    {
        return $this->list($request, 'site-check');
    }

    private function list(Request $request, string $kind)
    {
        $base = fn (): Builder => $kind === 'site-check' ? Project::siteChecks() : Project::formLeads();
        $tab = array_key_exists($request->query('tab'), self::TABS) ? $request->query('tab') : 'all';
        $unreadOnly = $request->query('filter') === 'unread';
        $who = in_array($request->query('who'), ['me', 'none'], true) ? $request->query('who') : 'all';
        $me = $request->user()->id;

        $scoped = fn (): Builder => $base()
            ->when($who === 'me', fn ($q) => $q->where('assigned_to', $me))
            ->when($who === 'none', fn ($q) => $q->whereNull('assigned_to'));

        $inquiries = $scoped()
            ->with('assignee')
            ->when($tab !== 'all', fn ($q) => $q->where('stage', $tab))
            ->when($unreadOnly, fn ($q) => $q->whereNull('read_at'))
            // Непрочетените най-отгоре, после най-новите.
            ->orderByRaw('CASE WHEN read_at IS NULL THEN 0 ELSE 1 END')
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $stageCounts = $scoped()->selectRaw('stage, COUNT(*) as total')->groupBy('stage')->pluck('total', 'stage');

        return view('admin.inquiries.index', [
            'kind' => $kind,
            'route' => $kind === 'site-check' ? 'admin.site-checks.index' : 'admin.inquiries.index',
            'inquiries' => $inquiries,
            'tab' => $tab,
            'who' => $who,
            'unreadOnly' => $unreadOnly,
            'tabCounts' => ['all' => (int) $stageCounts->sum()] + collect(array_keys(Project::STAGES))->mapWithKeys(fn ($s) => [$s => (int) ($stageCounts[$s] ?? 0)])->all(),
            'unreadCount' => $scoped()->whereNull('read_at')->count(),
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

    /** „Поемам го“: запитването става мое и се отбелязва като прочетено. */
    public function take(Request $request, Project $project)
    {
        $this->website($project)->forceFill(['assigned_to' => $request->user()->id, 'read_at' => $project->read_at ?? now()])->save();
        $project->notes()->create(['user_id' => $request->user()->id, 'kind' => 'stage', 'body' => 'Пое го ' . $request->user()->name . '.']);

        return back()->with('success', 'Запитването е ваше: ' . $project->name . '.');
    }

    public function readAll(Request $request)
    {
        ($request->input('kind') === 'site-check' ? Project::siteChecks() : Project::formLeads())
            ->whereNull('read_at')->update(['read_at' => now()]);

        return back()->with('success', 'Всички са маркирани като прочетени.');
    }

    private function website(Project $project): Project
    {
        abort_unless($project->source === 'website', 404);

        return $project;
    }
}
