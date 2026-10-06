<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\PostCategory;
use App\Models\Project;
use Illuminate\Http\Response;

class PageController extends Controller
{
    public function services()
    {
        return view('services', [
            'services' => site('services'),
        ]);
    }

    public function packages()
    {
        return view('packages', [
            'packages' => site('packages'),
        ]);
    }

    public function portfolio()
    {
        $projects = Project::with(['category', 'technologies'])
            ->where('is_public', true)
            ->where('status', 'Completed')
            ->orderByDesc('end_date')
            ->get();

        return view('portfolio', compact('projects'));
    }

    public function about()
    {
        return view('about', ['about' => site('about'), 'team' => site('team'), 'faq' => site('faq')]);
    }

    public function privacy()
    {
        return view($this->localized('privacy'), [
            'legal' => config('creatium.legal'),
            'contact' => config('creatium.contact'),
        ]);
    }

    public function terms()
    {
        return view($this->localized('terms'), ['contact' => config('creatium.contact')]);
    }

    public function cookies()
    {
        return view($this->localized('cookies'));
    }

    public function sitemap(): Response
    {
        $pages = ['home', 'services', 'packages', 'portfolio', 'blog.index', 'about', 'contact', 'privacy', 'terms', 'cookies'];
        $urls = [];

        foreach ($pages as $name) {
            $alternates = ['bg' => lroute($name, [], 'bg'), 'en' => lroute($name, [], 'en')];
            foreach ($alternates as $url) {
                $urls[] = ['loc' => $url, 'alternates' => $alternates];
            }
        }

        // Blog posts and categories: alternates only between languages that really exist.
        $posts = Post::where('status', 'published')->where('published_at', '<=', now())->latest('published_at')->get();
        foreach ($posts as $post) {
            $alternates = collect($post->locales())->mapWithKeys(fn ($l) => [$l => $post->url($l)])->all();
            foreach ($alternates as $url) {
                $urls[] = ['loc' => $url, 'alternates' => count($alternates) > 1 ? $alternates : [], 'lastmod' => $post->updated_at];
            }
        }

        foreach (PostCategory::whereHas('posts', fn ($q) => $q->where('status', 'published')->where('published_at', '<=', now()))->get() as $category) {
            $alternates = [];
            foreach (['bg', 'en'] as $l) {
                if ($category->tr('slug', $l) && $category->posts()->visible($l)->exists()) {
                    $alternates[$l] = lroute('blog.category', ['slug' => $category->tr('slug', $l)], $l);
                }
            }
            foreach ($alternates as $url) {
                $urls[] = ['loc' => $url, 'alternates' => count($alternates) > 1 ? $alternates : []];
            }
        }

        return response()
            ->view('sitemap', ['urls' => $urls])
            ->header('Content-Type', 'application/xml');
    }

    public function robots(): Response
    {
        $body = "User-agent: *\nDisallow: /admin\nDisallow: /login\n\nSitemap: " . route('sitemap') . "\n";

        return response($body)->header('Content-Type', 'text/plain');
    }

    /** Legal pages are written separately for each language: resources/views/en/* holds the English ones. */
    private function localized(string $view): string
    {
        return app()->getLocale() === 'en' ? "en.$view" : $view;
    }
}
