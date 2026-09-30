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
    public function index()
    {
        $projects = Project::with(['category', 'technologies'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        
        return view('admin.projects.index', compact('projects'));
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
            ->with('success', 'Project created successfully.');
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
        
        return redirect()->route('admin.projects.index')
            ->with('success', 'Project updated successfully.');
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
            ->with('success', 'Project deleted successfully.');
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
            ->paginate(10);
        
        return view('admin.projects.index', compact('projects', 'search'));
    }
}
