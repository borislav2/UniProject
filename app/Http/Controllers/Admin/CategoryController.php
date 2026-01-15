<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::withCount('projects')
            ->orderBy('name')
            ->paginate(10);
        
        return view('admin.categories.index', compact('categories'));
    }
    
    public function create()
    {
        return view('admin.categories.create');
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
            'description' => 'nullable|string'
        ]);
        
        Category::create($request->all());
        
        return redirect()->route('admin.categories.index')
            ->with('success', 'Category created successfully.');
    }
    
    public function show(Category $category)
    {
        $category->load('projects');
        
        return view('admin.categories.show', compact('category'));
    }
    
    public function edit(Category $category)
    {
        return view('admin.categories.edit', compact('category'));
    }
    
    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,' . $category->id,
            'description' => 'nullable|string'
        ]);
        
        $category->update($request->all());
        
        return redirect()->route('admin.categories.index')
            ->with('success', 'Category updated successfully.');
    }
    
    public function destroy(Category $category)
    {
        if ($category->projects()->count() > 0) {
            return redirect()->route('admin.categories.index')
                ->with('error', 'Cannot delete category with associated projects.');
        }
        
        $category->delete();
        
        return redirect()->route('admin.categories.index')
            ->with('success', 'Category deleted successfully.');
    }
}
