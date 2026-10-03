<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\PostCategory;
use App\Support\Uploads;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PostController extends Controller
{
    public const LOCALES = ['bg' => 'Български', 'en' => 'English'];

    public function index()
    {
        $posts = Post::with('category')->latest('updated_at')->paginate(20);

        return view('admin.posts.index', compact('posts'));
    }

    public function create()
    {
        return view('admin.posts.form', [
            'post' => new Post(['status' => 'draft']),
            'categories' => PostCategory::all(),
        ]);
    }

    public function store(Request $request)
    {
        $post = new Post(['user_id' => $request->user()->id]);
        $this->save($request, $post);

        return redirect()->route('admin.posts.edit', $post)->with('success', 'Статията е създадена.');
    }

    public function edit(Post $post)
    {
        return view('admin.posts.form', ['post' => $post, 'categories' => PostCategory::all()]);
    }

    public function update(Request $request, Post $post)
    {
        $this->save($request, $post);

        return redirect()->route('admin.posts.edit', $post)->with('success', 'Промените са запазени.');
    }

    public function destroy(Post $post)
    {
        Uploads::delete($post->cover_image);
        Uploads::delete($post->og_image);
        $post->delete();

        return redirect()->route('admin.posts.index')->with('success', 'Статията е изтрита.');
    }

    private function save(Request $request, Post $post): void
    {
        $rules = [
            'post_category_id' => 'nullable|exists:post_categories,id',
            'status' => ['required', Rule::in(array_keys(Post::STATUSES))],
            'published_at' => 'nullable|date',
            'cover_image' => 'nullable|' . Uploads::IMAGE_RULE,
            'og_image' => 'nullable|' . Uploads::IMAGE_RULE,
        ];
        foreach (array_keys(self::LOCALES) as $l) {
            $rules += [
                "title.$l" => 'nullable|string|max:200',
                "slug.$l" => ['nullable', 'string', 'max:200', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
                "excerpt.$l" => 'nullable|string|max:500',
                "body.$l" => 'nullable|string|max:200000',
                "meta_title.$l" => 'nullable|string|max:120',
                "meta_description.$l" => 'nullable|string|max:300',
                "canonical_url.$l" => 'nullable|url:http,https|max:500',
                "og_title.$l" => 'nullable|string|max:200',
                "og_description.$l" => 'nullable|string|max:300',
            ];
        }
        $data = $request->validate($rules, [
            'slug.*.regex' => 'Адресът може да съдържа само малки латински букви, цифри и тирета.',
            'canonical_url.*.url' => 'Canonical трябва да е пълен адрес, започващ с https://',
        ]);

        if (! $request->filled('title.bg') && ! $request->filled('title.en')) {
            throw ValidationException::withMessages(['title.bg' => 'Напишете заглавие поне на единия език.']);
        }

        $translated = [];
        foreach (['title', 'slug', 'excerpt', 'body', 'meta_title', 'meta_description', 'canonical_url', 'og_title', 'og_description'] as $field) {
            $translated[$field] = [];
        }

        foreach (array_keys(self::LOCALES) as $l) {
            $title = trim((string) ($data['title'][$l] ?? ''));
            if ($title === '') {
                continue; // a language without a title does not exist for this post
            }
            $slug = $data['slug'][$l] ?? null;
            if ($slug && $this->slugTaken($slug, $l, $post)) {
                throw ValidationException::withMessages(["slug.$l" => 'Вече има статия с този адрес.']);
            }
            $translated['title'][$l] = $title;
            $translated['slug'][$l] = $slug ?: $this->uniqueSlug($title, $l, $post);
            foreach (['excerpt', 'body', 'meta_title', 'meta_description', 'canonical_url', 'og_title', 'og_description'] as $field) {
                $value = trim((string) ($data[$field][$l] ?? ''));
                if ($value !== '') {
                    $translated[$field][$l] = $value;
                }
            }
        }

        $post->fill($translated + [
            'post_category_id' => $data['post_category_id'] ?? null,
            'status' => $data['status'],
            'published_at' => $data['published_at'] ?? ($data['status'] === 'published' ? ($post->published_at ?? now()) : null),
        ]);

        foreach (['cover_image', 'og_image'] as $field) {
            if ($request->hasFile($field)) {
                Uploads::delete($post->{$field});
                $post->{$field} = Uploads::storeImage($request->file($field), 'blog');
            } elseif ($request->boolean("remove_$field")) {
                Uploads::delete($post->{$field});
                $post->{$field} = null;
            }
        }

        $post->save();
    }

    private function slugTaken(string $slug, string $locale, Post $post): bool
    {
        return Post::where("slug->$locale", $slug)->when($post->exists, fn ($q) => $q->whereKeyNot($post->id))->exists();
    }

    private function uniqueSlug(string $title, string $locale, Post $post): string
    {
        $base = Str::slug($title, '-', $locale) ?: 'statia';
        $slug = $base;
        for ($i = 2; $this->slugTaken($slug, $locale, $post); $i++) {
            $slug = "$base-$i";
        }

        return $slug;
    }
}
