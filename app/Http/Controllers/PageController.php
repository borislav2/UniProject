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
        return gview('services', [
            'page' => site('services_page'),
            'trust' => app(\App\Support\Content::class)->withDefaults('home_trust'),
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

        // A g_only service is not part of the current site yet: it renders (preview, direct link) but is kept out of search, the sitemap
        // and hreflang until design G is the public design, when it becomes a normal indexable page by itself.
        $notYetPublic = ! empty($pages[$key]['g_only']) && ! \App\Support\Design::is('g');

        return gview('service', [
            'noindex' => $notYetPublic,
            'key' => $key,
            'page' => $pages[$key],
            // A service marked g_only (software development) is part of design G; the current design lists the same services as before.
            'others' => collect($pages)->except($key)->reject(fn ($other) => ! empty($other['g_only']) && ! \App\Support\Design::is('g')),
            'plan' => site('plans')['items'][$pages[$key]['package'] ?? -1] ?? null,
            'pageAlternates' => $alternates,
            'hreflangAlternates' => $notYetPublic ? [] : $alternates,
            'seo' => ['meta_title' => $pages[$key]['seo_title'], 'meta_description' => $pages[$key]['seo_description']],
        ]);
    }

    /** „Изработка на сайт за …“: страница за бранш (само на български), с концепцията, проблемите, пакета и въпросите ѝ. */
    public function industry(string $slug)
    {
        $page = site('industry_pages')[$slug] ?? null;
        // A page names a concept site in `demo` and borrows its problems, solutions, package and FAQ; a page without `demo`
        // (for example „малък бизнес“) carries those four keys itself and has no concept site to show.
        $demo = $page && isset($page['demo']) ? collect(site('demos'))->firstWhere('slug', $page['demo']) : null;
        abort_unless($page && ($demo || ! isset($page['demo'])), 404);
        $content = $demo ?? $page;

        $alternates = ['bg' => route('industry', $slug), 'en' => self::serviceUrl('website', 'en')];

        return gview('industry', [
            'slug' => $slug,
            'page' => $page,
            'demo' => $demo,
            'content' => $content,
            'plan' => $content['package']['name'] ? \App\Support\Plans::find(site('plans')['items'], $content['package']['name']) : null,
            'others' => collect(site('industry_pages'))->except($slug),
            'pageAlternates' => $alternates,
            'hreflangAlternates' => [],
            'seo' => ['meta_title' => $page['seo_title'], 'meta_description' => $page['seo_description']],
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
        // New top-level keys (trust, compare, steps...) show even after an older admin save of the block.
        $plans = app(\App\Support\Content::class)->withDefaults('plans');
        $selected = request()->integer('paket', -1);
        $faq = \App\Support\Plans::faq($plans, (array) site('questions'));

        // Картите показват само годишния абонамент (собствениците, ноември 2026), без превключвател и без ?plashtane.
        $billing = \App\Support\PlanBilling::config($plans);

        return gview('packages', [
            'plans' => $plans,
            'billing' => $billing,
            'faq' => $faq,
            // „Получете оферта“ под всеки пакет води към формата с избрания пакет (?paket=N#oferta).
            'selectedPlan' => array_key_exists($selected, $plans['items'] ?? []) ? $selected : null,
        ]);
    }

    public function portfolio()
    {
        $projects = Project::with(['category', 'technologies'])
            ->where('is_public', true)
            ->where('status', 'Completed')
            ->orderByDesc('end_date')
            ->get();

        return gview('portfolio', ['projects' => $projects, 'demos' => site('demos') ?? []]);
    }

    public function about()
    {
        // Ключове, добавени след последната редакция в админа (напр. story), идват от config.
        $about = app(\App\Support\Content::class)->withDefaults('about');

        return gview('about', ['about' => $about, 'team' => site('team'), 'trust' => site('home_trust')]);
    }

    /** Често задавани въпроси: отделна страница, по теми („group“), с FAQPage данни за Google. */
    public function faq()
    {
        $questions = collect(site('questions') ?? [])->filter(fn ($item) => filled($item['q'] ?? null) && filled($item['a'] ?? null));

        return gview('faq', ['groups' => $questions->groupBy(fn ($item) => $item['group'] ?? '')]);
    }

    /**
     * Презентацията на Creatium Lab: слайдове 16:9 от съдържанието на сайта. Служи и като източник на PDF-а
     * за изтегляне (tools/presentation/render.mjs го записва в public/files). Не се индексира.
     */
    public function presentation()
    {
        return view('presentation', [
            'hero' => site('home_hero'),
            'problem' => site('home_problem'),
            'engine' => site('home_engine'),
            'engineCards' => site('services_page.cards') ?? [],
            'trust' => site('home_trust'),
            'process' => site('home_process'),
            'plans' => site('plans'),
            'demos' => site('demos') ?? [],
            'team' => site('team') ?? [],
            'contact' => config('creatium.contact'),
        ]);
    }

    public function privacy()
    {
        return $this->legal('privacy', [
            'legal' => config('creatium.legal'),
            'contact' => config('creatium.contact'),
        ]);
    }

    public function terms()
    {
        return $this->legal('terms', ['contact' => config('creatium.contact')]);
    }

    public function cookies()
    {
        return $this->legal('cookies', []);
    }

    public function sitemap(): Response
    {
        $blog = config('creatium.blog_enabled');
        $pages = array_values(array_filter(['home', 'services', 'site-check', 'packages', 'portfolio', 'blog.index', 'about', 'faq', 'contact', 'privacy', 'terms', 'cookies'], fn ($name) => $blog || $name !== 'blog.index'));
        $urls = [];
        $posts = ! $blog ? collect() : Post::where('status', 'published')->where('published_at', '<=', now())->latest('published_at')->get();
        // The date a post last changed (never before it was published); the blog list changes whenever a post does.
        $postDate = fn (Post $post) => $post->updated_at && $post->updated_at->gt($post->published_at) ? $post->updated_at : $post->published_at;
        $blogLastmod = $posts->map($postDate)->max();

        $englishBlog = $blog && Post::visible('en')->exists();
        foreach ($pages as $name) {
            if ($name === 'blog.index' && ! $englishBlog) {
                $urls[] = ['loc' => lroute($name, [], 'bg'), 'alternates' => [], 'lastmod' => $blogLastmod];
                continue;
            }
            $alternates = ['bg' => lroute($name, [], 'bg'), 'en' => lroute($name, [], 'en')];
            foreach ($alternates as $url) {
                $urls[] = ['loc' => $url, 'alternates' => $alternates] + ($name === 'blog.index' ? ['lastmod' => $blogLastmod] : []);
            }
        }

        foreach (config('creatium.service_pages') as $key => $servicePage) {
            // Software development belongs to design G: it is listed only when G is the public design (before that the page is noindex).
            if (! empty($servicePage['g_only']) && \App\Support\Design::configured() !== 'g') {
                continue;
            }
            $alternates = ['bg' => self::serviceUrl($key, 'bg'), 'en' => self::serviceUrl($key, 'en')];
            foreach ($alternates as $url) {
                $urls[] = ['loc' => $url, 'alternates' => $alternates];
            }
        }

        // Страниците за браншове са само на български.
        foreach (array_keys(config('creatium.industry_pages')) as $slug) {
            $urls[] = ['loc' => route('industry', $slug), 'alternates' => []];
        }

        // Blog posts and categories: alternates only between languages that really exist.
        foreach ($posts as $post) {
            $alternates = collect($post->locales())->mapWithKeys(fn ($l) => [$l => $post->url($l)])->all();
            foreach ($alternates as $url) {
                $urls[] = ['loc' => $url, 'alternates' => count($alternates) > 1 ? $alternates : [], 'lastmod' => $postDate($post)];
            }
        }

        foreach (! $blog ? [] : PostCategory::whereHas('posts', fn ($q) => $q->where('status', 'published')->where('published_at', '<=', now()))->get() as $category) {
            $alternates = [];
            foreach (['bg', 'en'] as $l) {
                if ($category->tr('slug', $l) && $category->posts()->visible($l)->exists()) {
                    $alternates[$l] = lroute('blog.category', ['slug' => $category->tr('slug', $l)], $l);
                }
            }
            $categoryLastmod = $category->posts()->where('status', 'published')->where('published_at', '<=', now())->get()->map($postDate)->max();
            foreach ($alternates as $url) {
                $urls[] = ['loc' => $url, 'alternates' => count($alternates) > 1 ? $alternates : [], 'lastmod' => $categoryLastmod];
            }
        }

        return response()
            ->view('sitemap', ['urls' => $urls])
            ->header('Content-Type', 'application/xml');
    }

    /**
     * /llms.txt: кратко описание на бизнеса в Markdown за AI асистентите (llmstxt.org):
     * какво правим, услугите, пакетите с цените и как да се свържат с нас. Генерира се от съдържанието на сайта.
     */
    public function llms(): Response
    {
        app()->setLocale('bg');
        $contact = config('creatium.contact');
        // What is public decides what is listed: software development, the G package names and the single G phone only once G is the public design.
        $g = \App\Support\Design::configured() === 'g';
        $lines = [
            '# Creatium Lab',
            '',
            '> ' . __('Правим сайтове за бизнеси и се грижим хората да ги намират в Google.') . ' ' . site('services_page')['intro'],
            '',
            \App\Support\Geo\LlmsFull::disambiguation('bg'),
            '',
            'Екип в ' . __($contact['city']) . ', България, който работи с бизнеси. Сайтът на български е на ' . lroute('home', [], 'bg') . ', на английски на ' . lroute('home', [], 'en') . '.',
            '',
            '## Услуги',
            '',
        ];
        foreach (site('service_pages') as $key => $service) {
            if (! empty($service['g_only']) && ! $g) {
                continue;
            }
            $lines[] = '- [' . $service['title'] . '](' . self::serviceUrl($key, 'bg') . '). ' . $service['lead'];
        }
        $lines[] = '- [Безплатна проверка на сайт](' . lroute('site-check', [], 'bg') . '). Въвеждате адреса и имейл и получавате отчет за минута.';
        $lines[] = '';
        $lines[] = '## Сайт за бранш';
        $lines[] = '';
        foreach (site('industry_pages') as $slug => $industry) {
            $lines[] = '- [' . $industry['title'] . '](' . route('industry', $slug) . '). ' . $industry['lead'];
        }
        $lines[] = '';
        $lines[] = '## Пакети и цени';
        $lines[] = '';
        $plansBg = app(\App\Support\Content::class)->withDefaults('plans', 'bg');
        array_push($lines, ...\App\Support\Geo\LlmsFull::planLines($plansBg, $g));
        $lines[] = '- Всички пакети на ' . lroute('packages', [], 'bg');
        $lines[] = '';
        $lines[] = '## Екипът';
        $lines[] = '';
        foreach ((array) site('team') as $member) {
            if (! filled($member['name'] ?? null)) {
                continue;
            }
            $role = $g && filled($member['g_role'] ?? null) ? $member['g_role'] : ($member['role'] ?? '');
            $lines[] = '- ' . $member['name'] . ($role ? ', ' . $role : '') . (filled($member['linkedin'] ?? null) ? ' (' . $member['linkedin'] . ')' : '');
        }
        $lines[] = '- Повече за екипа на ' . lroute('about', [], 'bg') . ' и често задавани въпроси на ' . lroute('faq', [], 'bg');
        if (config('creatium.blog_enabled')) {
            $posts = Post::visible('bg')->latest('published_at')->take(8)->get();
            if ($posts->isNotEmpty()) {
                $lines[] = '';
                $lines[] = '## Статии';
                $lines[] = '';
                foreach ($posts as $post) {
                    $lines[] = '- [' . $post->tr('title') . '](' . $post->url('bg') . ')';
                }
            }
        }
        $lines[] = '';
        $lines[] = '## Контакти';
        $lines[] = '';
        $lines[] = '- Имейл ' . $contact['email'];
        foreach ($g ? g_phones() : $contact['phones'] as $phone) {
            $lines[] = '- Телефон ' . $phone;
        }
        $lines[] = '- Запазете час за разговор на ' . lroute('contact', [], 'bg') . '#konsultatsiya';
        $lines[] = '- Проекти и сайтове по браншове на ' . lroute('portfolio', [], 'bg');
        $lines[] = '';
        $lines[] = '## Пълно описание';
        $lines[] = '';
        $lines[] = '- [Всичко на едно място в обикновен текст](' . route('llms-full') . '). Екипът, услугите, цените, въпросите и последните статии.';
        $lines[] = '- [Full description in English](' . route('en.llms-full') . ')';

        return response(implode("\n", $lines) . "\n")->header('Content-Type', 'text/markdown; charset=UTF-8');
    }

    /** /llms-full.txt and /en/llms-full.txt: the whole site in plain Markdown for AI assistants (cached for an hour, see Geo\\LlmsFull). */
    public function llmsFull(): Response
    {
        $locale = request()->segment(1) === 'en' ? 'en' : 'bg';

        return response(\App\Support\Geo\LlmsFull::get($locale))->header('Content-Type', 'text/markdown; charset=UTF-8');
    }

    /** IndexNow key file: the key and nothing else (public by design); any other 32 hex name is a 404. */
    public function indexNowKey(string $key): Response
    {
        abort_unless(\App\Support\Seo\IndexNow::key() !== '' && hash_equals(\App\Support\Seo\IndexNow::key(), $key), 404);

        return response(\App\Support\Seo\IndexNow::key())->header('Content-Type', 'text/plain; charset=UTF-8');
    }

    /**
     * /robots.txt: the admin and the login stay out, and so do the technical endpoints that are not pages (chat, signed outreach links,
     * the design preview link). Everything else is open, CSS and JS included, because Google needs them to render the pages.
     * The search and answer bots of the AI assistants are named on purpose (they follow their own group, so the rules are repeated),
     * so that a later blanket rule cannot shut them out by accident. Bots that only collect training data are not named: they follow „*“
     * (allowed today), and blocking them is the owners' decision.
     */
    public function robots(): Response
    {
        $rules = "Disallow: /admin\nDisallow: /login\nDisallow: /chat\nDisallow: /o/\nDisallow: /otpisvane/\nDisallow: /nov-dizain\n";
        $body = "User-agent: *\n" . $rules . "\n";
        foreach (self::AI_SEARCH_BOTS as $bot) {
            $body .= "User-agent: $bot\nAllow: /\n" . $rules . "\n";
        }
        $body .= "Sitemap: " . route('sitemap') . "\n";

        return response($body)->header('Content-Type', 'text/plain');
    }

    /** Search and user-triggered fetchers of the AI assistants (they cite and link to pages, so they are welcome). */
    public const AI_SEARCH_BOTS = ['OAI-SearchBot', 'ChatGPT-User', 'PerplexityBot', 'Perplexity-User', 'Claude-SearchBot', 'Claude-User', 'Applebot', 'DuckAssistBot'];

    /**
     * A legal page: the long text stays in one place (resources/views/privacy|terms|cookies and en/*). Design G renders that view's
     * sections and puts the text into its own page (g.legal) instead of the old page header and Tailwind wrapper.
     */
    private function legal(string $name, array $data)
    {
        $view = $this->localized($name);
        if (! \App\Support\Design::is('g')) {
            return view($view, $data);
        }

        $sections = view($view, $data)->renderSections();

        return view('g.legal', ['sections' => $sections]);
    }

    /** Legal pages are written separately for each language: resources/views/en/* holds the English ones. */
    private function localized(string $view): string
    {
        return app()->getLocale() === 'en' ? "en.$view" : $view;
    }
}
