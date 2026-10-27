<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\Category;
use App\Models\Technology;
use Illuminate\Support\Str;

class ProjectController extends Controller
{
    /** Филтри над списъка: ключ => [текст, условие]. */
    private const FILTERS = [
        'leads' => 'Отворени запитвания',
        'active' => 'В работа',
        'done' => 'Завършени',
        'mine' => 'Моите',
    ];

    public function index(Request $request)
    {
        $filter = array_key_exists($request->query('filter'), self::FILTERS) ? $request->query('filter') : null;
        $projects = $this->filtered(Project::query(), $filter)
            ->with(['category', 'technologies', 'assignee'])
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        return view('admin.projects.index', ['projects' => $projects, 'filter' => $filter, 'filters' => $this->filterCounts()]);
    }

    private function filtered($query, ?string $filter)
    {
        return match ($filter) {
            'leads' => $query->openLeads()->orderByRaw('next_action_at is null')->orderBy('next_action_at'),
            'active' => $query->whereIn('status', ['In Progress', 'On Hold']),
            'done' => $query->where('status', 'Completed'),
            'mine' => $query->where('assigned_to', auth()->id()),
            default => $query,
        };
    }

    private function filterCounts(): array
    {
        $counts = ['' => ['Всички', Project::count()]];
        foreach (self::FILTERS as $key => $label) {
            $counts[$key] = [$label, $this->filtered(Project::query(), $key)->count()];
        }

        return $counts;
    }
    
    public function create()
    {
        $categories = Category::all();
        $technologies = Technology::all();
        
        return view('admin.projects.create', compact('categories', 'technologies'));
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'required|string|in:Planning,In Progress,Completed,On Hold,Cancelled',
            'manager' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'is_public' => 'nullable|boolean',
            'website_url' => 'nullable|url:http,https|max:255',
            'client_phone' => 'nullable|string|max:50',
            'client_email' => 'nullable|email|max:255',
            'package' => 'nullable|string|max:80',
            'monthly_fee' => 'nullable|numeric|min:0|max:100000',
            'billing_day' => 'nullable|integer|min:1|max:31',
            'technologies' => 'nullable|array',
            'technologies.*' => 'exists:technologies,id',
            'file' => 'nullable|file|mimes:pdf,doc,docx,txt,jpg,jpeg,png,gif|max:10240'
        ]);
        
        $data = $request->except('technologies', 'file');
        $data['is_public'] = $request->boolean('is_public');
        
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $fileName = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/projects'), $fileName);
            $data['file_path'] = 'uploads/projects/' . $fileName;
        }
        
        $project = Project::create($data);
        
        if ($request->has('technologies')) {
            $project->technologies()->attach($request->technologies);
        }
        
        return redirect()->route('admin.projects.index')
            ->with('success', 'Проектът е създаден.');
    }
    
    public function show(Project $project)
    {
        $project->load(['category', 'technologies', 'notes.user']);

        return view('admin.projects.show', ['project' => $project, 'timeline' => $this->timeline($project)]);
    }
    
    public function edit(Project $project)
    {
        $categories = Category::all();
        $technologies = Technology::all();
        
        return view('admin.projects.edit', compact('project', 'categories', 'technologies'));
    }
    
    public function update(Request $request, Project $project)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'required|string|in:Planning,In Progress,Completed,On Hold,Cancelled',
            'manager' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'is_public' => 'nullable|boolean',
            'website_url' => 'nullable|url:http,https|max:255',
            'client_phone' => 'nullable|string|max:50',
            'client_email' => 'nullable|email|max:255',
            'package' => 'nullable|string|max:80',
            'monthly_fee' => 'nullable|numeric|min:0|max:100000',
            'billing_day' => 'nullable|integer|min:1|max:31',
            'technologies' => 'nullable|array',
            'technologies.*' => 'exists:technologies,id',
            'file' => 'nullable|file|mimes:pdf,doc,docx,txt,jpg,jpeg,png,gif|max:10240'
        ]);
        
        $data = $request->except('technologies', 'file');
        $data['is_public'] = $request->boolean('is_public');
        
        if ($request->hasFile('file')) {
            // Delete old file if exists
            if ($project->file_path && file_exists(public_path($project->file_path))) {
                unlink(public_path($project->file_path));
            }
            
            $file = $request->file('file');
            $fileName = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/projects'), $fileName);
            $data['file_path'] = 'uploads/projects/' . $fileName;
        }
        
        $project->update($data);
        
        $project->technologies()->sync($request->technologies ?? []);
        
        return redirect()->route('admin.projects.show', $project)
            ->with('success', 'Промените са запазени.');
    }
    
    public function destroy(Project $project)
    {
        // Delete file if exists
        if ($project->file_path && file_exists(public_path($project->file_path))) {
            unlink(public_path($project->file_path));
        }
        
        $project->technologies()->detach();
        $project->delete();
        
        return redirect()->route('admin.projects.index')
            ->with('success', 'Проектът е изтрит.');
    }
    
    /** Етап на продажбата и следваща стъпка (от „Запитвания“, „Днес“ или страницата на проекта). */
    public function pipeline(Request $request, Project $project)
    {
        $data = $request->validate([
            'stage' => 'required|in:' . implode(',', array_keys(Project::STAGES)),
            'next_action' => 'nullable|string|max:255',
            'next_action_at' => 'nullable|date',
            'lost_reason' => 'nullable|string|max:255',
            'note' => 'nullable|string|max:2000',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        $closed = in_array($data['stage'], ['won', 'lost'], true);
        $values = [
            'stage' => $data['stage'],
            'next_action' => $closed ? null : ($data['next_action'] ?? null),
            'next_action_at' => $closed || empty($data['next_action_at']) ? null : \Illuminate\Support\Carbon::parse($data['next_action_at'], 'Europe/Sofia')->utc(),
            'lost_reason' => $data['stage'] === 'lost' ? ($data['lost_reason'] ?? null) : null,
            'read_at' => $project->read_at ?? now(),
        ];
        if ($request->has('assigned_to')) {
            $values['assigned_to'] = $data['assigned_to'] ?? null;
        }
        if ($project->stage !== $data['stage']) {
            $values['stage_changed_at'] = now();
        }
        if ($values['next_action_at']) {
            $values['next_action_at'] = $values['next_action_at']->copy()->second(0);
        }
        // Спечелен започва работа, загубен се отказва: така и филтрите по статус остават верни.
        if ($data['stage'] === 'won' && $project->status === 'Planning') {
            $values['status'] = 'In Progress';
        }
        if ($data['stage'] === 'lost') {
            $values['status'] = 'Cancelled';
        }
        $stageChanged = $project->stage !== $data['stage'];
        $nextChanged = $project->next_action !== $values['next_action'] || $project->next_action_at?->toDateTimeString() !== $values['next_action_at']?->toDateTimeString();
        $project->forceFill($values)->save();

        // История: бележката от разговора и смяната на етапа/следващата стъпка.
        if (filled($data['note'] ?? null)) {
            $project->notes()->create(['user_id' => $request->user()->id, 'kind' => 'call', 'body' => trim($data['note'])]);
        }
        if ($stageChanged || $nextChanged) {
            $text = 'Етап: ' . $project->stageLabel() . ($project->lost_reason ? ' (' . $project->lost_reason . ')' : '');
            if ($project->next_action_at) {
                $text .= '. Следваща стъпка: ' . ($project->next_action ?: 'свържете се') . ' на ' . $project->next_action_at->timezone('Europe/Sofia')->format('d.m.Y H:i');
            }
            $project->notes()->create(['user_id' => $request->user()->id, 'kind' => 'stage', 'body' => $text]);
        }

        return back()->with('success', 'Запазено: ' . $project->stageLabel() . ($project->next_action_at ? ', следваща стъпка ' . $project->next_action_at->timezone('Europe/Sofia')->format('d.m H:i') : '') . '.');
    }

    /** Ръчна бележка или запис за разговор в историята на проекта. */
    public function note(Request $request, Project $project)
    {
        $data = $request->validate([
            'kind' => 'required|in:note,call',
            'body' => 'required|string|max:2000',
        ], ['body.required' => 'Напишете бележката.']);
        $project->notes()->create(['user_id' => $request->user()->id] + $data);

        return back()->with('success', 'Бележката е добавена.');
    }

    /** Историята на клиента на едно място: бележки и етапи, изпратени имейли, разговорът с Крео, писмото до бизнеса и кликовете. */
    private function timeline(Project $project): \Illuminate\Support\Collection
    {
        $items = collect([['at' => $project->created_at, 'kind' => 'Създаден', 'text' => $project->source === 'website' ? 'Запитване от сайта' . ($project->lead_channel ? ' (' . $project->lead_channel . ')' : '') : 'Проектът е създаден', 'user' => null, 'url' => null]]);

        foreach ($project->notes as $note) {
            $items->push(['at' => $note->created_at, 'kind' => \App\Models\ProjectNote::KINDS[$note->kind] ?? $note->kind, 'text' => $note->body, 'user' => $note->user?->name, 'url' => null]);
        }
        foreach (\App\Models\ClientEmail::where('project_id', $project->id)->get() as $email) {
            $items->push(['at' => $email->sent_at ?? $email->created_at, 'kind' => 'Имейл', 'text' => ($email->error ? 'Неизпратен: ' : 'Изпратен: ') . $email->subject . ' → ' . $email->to_email, 'user' => null, 'url' => null]);
        }
        foreach (\App\Models\ChatConversation::where('lead_id', $project->id)->get() as $chat) {
            $items->push(['at' => $chat->created_at, 'kind' => 'Крео', 'text' => 'Разговор в чата на сайта', 'user' => null, 'url' => route('admin.chats.show', $chat)]);
        }
        foreach (\App\Models\OutreachProspect::where('project_id', $project->id)->get() as $prospect) {
            if ($prospect->sent_at) {
                $items->push(['at' => $prospect->sent_at, 'kind' => 'Писмо', 'text' => 'Първо писмо до ' . $prospect->email, 'user' => null, 'url' => null]);
            }
            if ($prospect->clicked_at) {
                $items->push(['at' => $prospect->last_clicked_at, 'kind' => 'Клик', 'text' => 'Кликна: ' . (\App\Models\OutreachProspect::CLICK_LINKS[$prospect->last_click_link] ?? $prospect->last_click_link) . ($prospect->click_count > 1 ? " ({$prospect->click_count}×)" : ''), 'user' => null, 'url' => null]);
            }
        }

        return $items->filter(fn ($item) => $item['at'])->sortByDesc('at')->values();
    }

    public function search(Request $request)
    {
        $search = $request->get('search');
        
        if (empty($search)) {
            return redirect()->route('admin.projects.index');
        }
        
        $projects = Project::search($search)
            ->with(['category', 'technologies'])
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        return view('admin.projects.index', ['projects' => $projects, 'search' => $search, 'filter' => null, 'filters' => $this->filterCounts()]);
    }
}
