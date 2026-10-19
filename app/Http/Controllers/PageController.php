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
            'page' => site('services_page'),
        ]);
    }

    /** Страница на една услуга (/uslugi/{slug}, /en/services/{slug}). */
    public function service(string $slug)
    {
        $locale = app()->getLocale();
        $pages = site('service_pages');
        $key = collect($pages)->search(fn ($page) => $page['slug'] === $slug);
        abort_if($key === false, 404);

        $alternates = [];
        foreach (['bg', 'en'] as $lang) {
            $alternates[$lang] = self::serviceUrl($key, $lang);
        }

        return view('service', [
            'key' => $key,
            'page' => $pages[$key],
            'others' => collect($pages)->except($key),
            'plan' => site('plans')['items'][$pages[$key]['package'] ?? -1] ?? null,
            'pageAlternates' => $alternates,
            'hreflangAlternates' => $alternates,
            'seo' => ['meta_title' => $pages[$key]['seo_title'], 'meta_description' => $pages[$key]['seo_description']],
        ]);
    }

    /** Адресът на услуга на даден език (slug-ът е различен за всеки език). */
    public static function serviceUrl(string $key, ?string $locale = null): string
    {
        $locale ??= app()->getLocale();
        $slug = $locale === 'en' ? config("creatium_en.service_pages.$key.slug") : config("creatium.service_pages.$key.slug");

        return lroute('services.show', ['slug' => $slug], $locale);
    }

    public function packages()
    {
        return view('packages', [
            'plans' => site('plans'),
        ]);
    }

    public function portfolio()
    {
        $projects = Project::with(['category', 'technologies'])
            ->where('is_public', true)
            ->where('status', 'Completed')
            ->orderByDesc('end_date')
            ->get();

        return view('portfolio', ['projects' => $projects, 'demos' => site('demos') ?? []]);
    }

    public function about()
    {
        return view('about', ['about' => site('about'), 'team' => site('team'), 'faq' => site('questions')]);
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
        $blog = config('creatium.blog_enabled');
        $pages = array_values(array_filter(['home', 'services', 'site-check', 'packages', 'portfolio', 'blog.index', 'about', 'contact', 'privacy', 'terms', 'cookies'], fn ($name) => $blog || $name !== 'blog.index'));
        $urls = [];

        foreach ($pages as $name) {
            $alternates = ['bg' => lroute($name, [], 'bg'), 'en' => lroute($name, [], 'en')];
            foreach ($alternates as $url) {
                $urls[] = ['loc' => $url, 'alternates' => $alternates];
            }
        }

        foreach (array_keys(config('creatium.service_pages')) as $key) {
            $alternates = ['bg' => self::serviceUrl($key, 'bg'), 'en' => self::serviceUrl($key, 'en')];
            foreach ($alternates as $url) {
                $urls[] = ['loc' => $url, 'alternates' => $alternates];
            }
        }

        // Blog posts and categories: alternates only between languages that really exist.
        $posts = ! $blog ? collect() : Post::where('status', 'published')->where('published_at', '<=', now())->latest('published_at')->get();
        foreach ($posts as $post) {
            $alternates = collect($post->locales())->mapWithKeys(fn ($l) => [$l => $post->url($l)])->all();
            foreach ($alternates as $url) {
                $urls[] = ['loc' => $url, 'alternates' => count($alternates) > 1 ? $alternates : [], 'lastmod' => $post->updated_at];
            }
        }

        foreach (! $blog ? [] : PostCategory::whereHas('posts', fn ($q) => $q->where('status', 'published')->where('published_at', '<=', now()))->get() as $category) {
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
