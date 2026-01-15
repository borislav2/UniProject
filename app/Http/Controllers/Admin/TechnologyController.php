<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Technology;

class TechnologyController extends Controller
{
    public function index()
    {
        $technologies = Technology::withCount('projects')
            ->orderBy('name')
            ->paginate(10);
        
        return view('admin.technologies.index', compact('technologies'));
    }
    
    public function create()
    {
        return view('admin.technologies.create');
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:technologies,name',
            'version' => 'nullable|string|max:50'
        ]);
        
        Technology::create($request->all());
        
        return redirect()->route('admin.technologies.index')
            ->with('success', 'Technology created successfully.');
    }
    
    public function show(Technology $technology)
    {
        $technology->load('projects');
        
        return view('admin.technologies.show', compact('technology'));
    }
    
    public function edit(Technology $technology)
    {
        return view('admin.technologies.edit', compact('technology'));
    }
    
    public function update(Request $request, Technology $technology)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:technologies,name,' . $technology->id,
            'version' => 'nullable|string|max:50'
        ]);
        
        $technology->update($request->all());
        
        return redirect()->route('admin.technologies.index')
            ->with('success', 'Technology updated successfully.');
    }
    
    public function destroy(Technology $technology)
    {
        if ($technology->projects()->count() > 0) {
            return redirect()->route('admin.technologies.index')
                ->with('error', 'Cannot delete technology with associated projects.');
        }
        
        $technology->delete();
        
        return redirect()->route('admin.technologies.index')
            ->with('success', 'Technology deleted successfully.');
    }
}
