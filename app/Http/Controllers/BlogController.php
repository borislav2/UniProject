<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\PostCategory;

class BlogController extends Controller
{
    public function index()
    {
        $this->ensureEnabled();

        return $this->listing(null);
    }

    public function category(string $slug)
    {
        $this->ensureEnabled();
        $category = PostCategory::findBySlug($slug);
        abort_unless($category, 404);

        return $this->listing($category);
    }

    public function show(string $slug)
    {
        $this->ensureEnabled();
        $locale = app()->getLocale();
        $post = Post::with('category')->where("slug->$locale", $slug)->first();
        abort_unless($post && $post->isVisible(), 404);

        $translations = collect($post->locales())->mapWithKeys(fn ($l) => [$l => $post->url($l)])->all();

        $related = Post::visible()->with('category')
            ->whereKeyNot($post->id)
            ->when($post->post_category_id, fn ($q) => $q->orderByRaw('post_category_id = ? desc', [$post->post_category_id]))
            ->latest('published_at')
            ->take(3)
            ->get();

        return gview('blog.show', [
            'post' => $post,
            'related' => $related,
            'hreflangAlternates' => count($translations) > 1 ? $translations : [],
            'pageAlternates' => $translations + $this->indexUrls(),
            'ogType' => 'article',
            'seo' => [
                'meta_title' => $post->tr('meta_title'),
                'meta_description' => $post->tr('meta_description') ?? $post->excerptOrSummary(),
                'canonical_url' => $post->tr('canonical_url'),
                'og_title' => $post->tr('og_title'),
                'og_description' => $post->tr('og_description'),
                // Design G shares the G drawing of a starter cover, not the old-design file.
                'og_image' => $post->og_image ?? (\App\Support\Design::is('g') ? g_cover($post) : $post->cover_image),
            ],
        ]);
    }

    private function listing(?PostCategory $category)
    {
        $posts = Post::visible()->with('category')
            ->when($category, fn ($q) => $q->where('post_category_id', $category->id))
            ->latest('published_at')
            ->paginate(9);

        // A page after the last one (?page=99) is not a page: 404 instead of an empty list under a self-canonical address.
        abort_if($posts->currentPage() > 1 && $posts->isEmpty(), 404);

        $categories = PostCategory::whereHas('posts', fn ($q) => $q->visible())->get()
            ->filter(fn ($c) => $c->tr('slug'))
            ->sortBy(fn ($c) => $c->trOrBg('name'));

        $alternates = $this->indexUrls();
        // Докато няма статии на английски, /en/blog е празна: без hreflang към нея и с noindex.
        $hasEnglish = Post::visible('en')->exists();
        $hreflang = $hasEnglish ? $alternates : [];
        if ($category) {
            $hreflang = [];
            foreach (['bg', 'en'] as $l) {
                if ($category->tr('slug', $l)) {
                    $alternates[$l] = $hreflang[$l] = lroute('blog.category', ['slug' => $category->tr('slug', $l)], $l);
                }
            }
            // A language counts only when the category really has posts in it (the sitemap uses the same rule), otherwise the English
            // page is an empty noindex page and hreflang must not point to it.
            foreach (array_keys($hreflang) as $l) {
                if (! $category->posts()->visible($l)->exists()) {
                    unset($hreflang[$l]);
                }
            }
            $hreflang = count($hreflang) > 1 ? $hreflang : [];
        }

        return gview('blog.index', [
            'posts' => $posts,
            'categories' => $categories,
            'category' => $category,
            'pageNumber' => $posts->currentPage(),
            'pageAlternates' => $alternates,
            'hreflangAlternates' => $posts->currentPage() > 1 ? [] : $hreflang,
            // Page 2 and later are indexable pages of their own: the canonical points to themselves, on the public host (the paginator
            // builds its links from the request, which behind a proxy may not be the public address).
            'canonical' => $posts->currentPage() > 1 ? url()->current() . '?page=' . $posts->currentPage() : null,
            'noindex' => app()->getLocale() === 'en' && ($category ? ! $category->posts()->visible('en')->exists() : ! $hasEnglish),
        ]);
    }

    private function indexUrls(): array
    {
        return ['bg' => lroute('blog.index', [], 'bg'), 'en' => lroute('blog.index', [], 'en')];
    }

    /** The public blog is hidden (404) until creatium.blog_enabled is turned on. */
    private function ensureEnabled(): void
    {
        abort_unless(config('creatium.blog_enabled'), 404);
    }
}
