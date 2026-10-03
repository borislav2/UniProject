<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PostCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PostCategoryController extends Controller
{
    public function index()
    {
        $categories = PostCategory::withCount('posts')->get()->sortBy(fn ($c) => $c->tr('name', 'bg'));

        return view('admin.post-categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        PostCategory::create($this->validated($request));

        return back()->with('success', 'Категорията е добавена.');
    }

    public function edit(PostCategory $postCategory)
    {
        return view('admin.post-categories.edit', ['category' => $postCategory]);
    }

    public function update(Request $request, PostCategory $postCategory)
    {
        $postCategory->update($this->validated($request, $postCategory));

        return redirect()->route('admin.post-categories.index')->with('success', 'Категорията е запазена.');
    }

    public function destroy(PostCategory $postCategory)
    {
        $postCategory->delete(); // posts stay, just without a category

        return back()->with('success', 'Категорията е изтрита. Статиите в нея остават без категория.');
    }

    private function validated(Request $request, ?PostCategory $category = null): array
    {
        $data = $request->validate([
            'name.bg' => 'required|string|max:100',
            'name.en' => 'nullable|string|max:100',
        ], ['name.bg.required' => 'Напишете името на български.']);

        $name = $slug = [];
        foreach (['bg', 'en'] as $l) {
            $value = trim((string) ($data['name'][$l] ?? ''));
            if ($value === '') {
                continue;
            }
            $name[$l] = $value;
            $slug[$l] = $this->uniqueSlug($value, $l, $category);
        }

        return ['name' => $name, 'slug' => $slug];
    }

    private function uniqueSlug(string $name, string $locale, ?PostCategory $category): string
    {
        $base = Str::slug($name, '-', $locale) ?: 'kategoriya';
        $slug = $base;
        for ($i = 2; PostCategory::where("slug->$locale", $slug)->when($category, fn ($q) => $q->whereKeyNot($category->id))->exists(); $i++) {
            $slug = "$base-$i";
        }

        return $slug;
    }
}
