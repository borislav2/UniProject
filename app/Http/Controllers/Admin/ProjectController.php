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
        'leads' => 'Нови запитвания',
        'active' => 'В работа',
        'done' => 'Завършени',
    ];

    public function index(Request $request)
    {
        $filter = array_key_exists($request->query('filter'), self::FILTERS) ? $request->query('filter') : null;
        $projects = $this->filtered(Project::query(), $filter)
            ->with(['category', 'technologies'])
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        return view('admin.projects.index', ['projects' => $projects, 'filter' => $filter, 'filters' => $this->filterCounts()]);
    }

    private function filtered($query, ?string $filter)
    {
        return match ($filter) {
            'leads' => $query->where('status', 'Planning')->where('source', 'website'),
            'active' => $query->whereIn('status', ['In Progress', 'On Hold']),
            'done' => $query->where('status', 'Completed'),
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
        $project->load(['category', 'technologies']);
        
        return view('admin.projects.show', compact('project'));
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
