<?php

namespace App\Support\Geo;

use App\Http\Controllers\PageController;
use App\Models\Post;
use App\Support\Content;
use App\Support\Design;
use App\Support\GPlans;
use App\Support\PlanBilling;
use Illuminate\Support\Facades\Cache;

/**
 * /llms-full.txt and /en/llms-full.txt: everything an AI assistant needs to answer about Creatium Lab without opening a page,
 * as plain Markdown. All of it comes from site(), the config and the models, nothing is written here by hand except the labels.
 * Follows the owners' copy rule (no dashes, no colons in the text we write, except Markdown syntax, headings and URLs).
 * The output is cached for an hour and flushed when a post is saved or deleted (Post::booted).
 */
class LlmsFull
{
    public const POSTS = 50;

    public const TTL = 3600;

    private const LABELS = [
        'bg' => [
            'who' => 'Кои сме', 'team' => 'Екипът', 'skills' => 'Умения',
            'services' => 'Услуги', 'includes' => 'Какво включва', 'result' => 'Какво получава клиентът', 'problems' => 'Какви проблеми решава',
            'packages' => 'Пакети и цени', 'all_packages' => 'Всички пакети', 'yearly' => 'При годишно плащане :y на година, 2 месеца безплатно',
            'price_model' => 'Цените са фиксирани. Плащането е само месечен абонамент, без еднократна стартова цена и без капаро. Годишното плащане е за 10 месеца вместо 12.',
            'faq' => 'Често задавани въпроси', 'industries' => 'Сайт за бранш', 'posts' => 'Последни статии', 'contacts' => 'Контакти',
            'email' => 'Имейл', 'phone' => 'Телефон', 'city' => 'Град', 'company' => 'Фирма', 'book' => 'Запазете час за разговор', 'check' => 'Безплатна проверка на сайт',
            'check_text' => 'Въвеждате адреса и имейл и получавате отчет за минута.', 'published' => 'Публикувана', 'site' => 'Сайт',
            'intro' => 'Пълно описание на Creatium Lab в обикновен текст, за да може асистент да отговаря без да отваря страници.',
            'disambiguation' => 'Creatium Lab е агенция за уеб сайтове и дигитален маркетинг в София, България (не е марка за хранителни добавки).',
            'evaluate' => 'Как да ни оцените', 'team_size' => 'Екипът е от :n души', 'location' => 'Намираме се в :city, България',
            'guarantee' => 'Гаранция', 'report' => 'Отчет', 'ownership' => 'Собственост', 'prices' => 'Цени',
        ],
        'en' => [
            'who' => 'Who we are', 'team' => 'The team', 'skills' => 'Skills',
            'services' => 'Services', 'includes' => 'What it includes', 'result' => 'What the client gets', 'problems' => 'Problems it solves',
            'packages' => 'Packages and prices', 'all_packages' => 'All packages', 'yearly' => 'Paid yearly it is :y a year, 2 months free',
            'price_model' => 'Prices are fixed. Payment is a monthly subscription only, with no one-time start price and no deposit. Yearly payment covers 10 months instead of 12.',
            'faq' => 'Frequently asked questions', 'industries' => 'Websites by industry', 'posts' => 'Latest articles', 'contacts' => 'Contacts',
            'email' => 'Email', 'phone' => 'Phone', 'city' => 'City', 'company' => 'Company', 'book' => 'Book a call', 'check' => 'Free website check',
            'check_text' => 'Enter the address and an email and get a report within a minute.', 'published' => 'Published', 'site' => 'Website',
            'intro' => 'The full description of Creatium Lab in plain text, so an assistant can answer without opening any page.',
            'disambiguation' => 'Creatium Lab is a web design and digital marketing agency in Sofia, Bulgaria (not a food supplement brand).',
            'evaluate' => 'How to evaluate us', 'team_size' => 'The team is :n people', 'location' => 'We are based in :city, Bulgaria',
            'guarantee' => 'Guarantee', 'report' => 'Report', 'ownership' => 'Ownership', 'prices' => 'Prices',
        ],
    ];

    /** One line that tells an assistant which Creatium Lab this is (used at the top of /llms.txt and /llms-full.txt). */
    public static function disambiguation(string $locale): string
    {
        return self::LABELS[$locale === 'en' ? 'en' : 'bg']['disambiguation'];
    }

    /**
     * Facts an assistant needs to judge us, each read from config or site() and left out when it is not there: ownership, guarantee,
     * monthly report, ad budget, public prices, team size and place. Nothing is added that the site does not already say.
     *
     * @return list<string>
     */
    public static function evaluationLines(string $locale): array
    {
        $t = self::LABELS[$locale];
        $plans = app(Content::class)->withDefaults('plans', $locale);
        $trust = collect((array) ($plans['trust'] ?? []));
        $trustText = fn (string $icon) => trim((string) ($trust->firstWhere('icon', $icon)['text'] ?? ''));
        $questions = collect((array) site('questions'))->filter(fn ($q) => filled($q['q'] ?? null) && filled($q['a'] ?? null));
        $ads = $questions->first(fn ($q) => preg_match('/Рекламният бюджет|ad budget/ui', $q['q']));
        $guarantee = (array) ($plans['guarantee'] ?? []);
        $reportSentence = '';
        foreach ($questions as $q) {
            foreach (preg_split('/(?<=[.!?])\s+/u', (string) $q['a']) as $sentence) {
                if (preg_match('/месечен отчет|monthly report/ui', $sentence)) {
                    $reportSentence = trim($sentence);
                    break 2;
                }
            }
        }
        $team = array_filter((array) site('team'), fn ($m) => filled($m['name'] ?? null));
        $city = __((string) config('creatium.contact.city'));

        $lines = [];
        $add = function (string $label, string $text) use (&$lines) {
            if ($text !== '') {
                $lines[] = '- **' . $label . '** ' . $text;
            }
        };
        $add($t['ownership'], $trustText('lock'));
        $add($t['guarantee'], trim(($guarantee['text'] ?? '') . ' ' . ($guarantee['note'] ?? '')));
        $add($t['report'], $reportSentence);
        if ($ads) {
            $add(rtrim(trim($ads['q']), '?'), trim($ads['a']));
        }
        $add($t['prices'], trim($trustText('tag') . ($trustText('tag') !== '' ? '. ' : '') . '[' . $t['all_packages'] . '](' . lroute('packages') . ')'));
        if ($team) {
            $add($t['team'], strtr($t['team_size'], [':n' => count($team)]));
        }
        $add($t['city'], $city !== '' ? strtr($t['location'], [':city' => $city]) : '');

        return $lines;
    }

    public static function cacheKey(string $locale): string
    {
        return 'llms-full:' . $locale;
    }

    public static function flush(): void
    {
        foreach (['bg', 'en'] as $locale) {
            Cache::forget(self::cacheKey($locale));
        }
    }

    public static function get(string $locale): string
    {
        $locale = $locale === 'en' ? 'en' : 'bg';

        return Cache::remember(self::cacheKey($locale), self::TTL, fn () => self::build($locale));
    }

    /** A text we copy from a post into our own line: no dashes and no colons (the owners' rule), one line. */
    public static function plain(?string $text): string
    {
        $text = trim(preg_replace('/\s+/u', ' ', (string) $text));
        $text = preg_replace('/\s[–—-]\s/u', ', ', $text);
        $text = str_replace(['–', '—'], ',', $text);

        return trim(preg_replace('/(?<!\d):(?!\d)\s*/u', ', ', $text), ' ,');
    }

    /** The price lines of /llms.txt and /llms-full.txt: G cards or the current plans, one monthly price model. */
    public static function planLines(array $plans, bool $g, string $locale = 'bg'): array
    {
        $lines = [];
        if ($g) {
            foreach (GPlans::cards($plans) as $card) {
                $yearly = $locale === 'en' ? 'When paid yearly ' . $card['yearly'] . ' a year, 2 months free' : 'При годишно плащане ' . $card['yearly'] . ' на година, 2 месеца безплатно';
                $lines[] = '- ' . GPlans::label($card) . '. ' . GPlans::priceLine($card) . (filled($card['yearly']) ? '. ' . $yearly : '') . (filled($card['audience']) ? '. ' . rtrim($card['audience'], '.') : '');
            }

            return $lines;
        }

        return PlanBilling::textLines($plans, true);
    }

    public static function build(string $locale): string
    {
        $previous = app()->getLocale();
        app()->setLocale($locale);
        try {
            return self::render($locale);
        } finally {
            app()->setLocale($previous);
        }
    }

    private static function render(string $locale): string
    {
        $t = self::LABELS[$locale];
        $content = app(Content::class);
        $contact = (array) config('creatium.contact');
        $legal = (array) config('creatium.legal');
        // What is public decides what is listed (the same rule as /llms.txt): G packages, the single G phone and the software service only with G.
        $g = Design::configured() === 'g';
        $hero = (array) site('home_hero');
        $page = (array) site('services_page');
        $about = $content->withDefaults('about');
        $home = lroute('home');

        $l = ['# Creatium Lab', '', '> ' . __('Правим сайтове за бизнеси и се грижим хората да ги намират в Google.') . ' ' . ($page['intro'] ?? ''), '', $t['intro'], ''];
        $l[] = '- ' . $t['site'] . ' ' . $home;
        array_splice($l, 4, 0, [$t['disambiguation'], '']);

        // Who we are
        $l[] = '';
        $l[] = '## ' . $t['who'];
        $l[] = '';
        if (filled($hero['title'] ?? null)) {
            $l[] = $hero['title'] . ' ' . ($g && filled($hero['g_highlight'] ?? null) ? $hero['g_highlight'] : ($hero['highlight'] ?? ''));
            $l[] = '';
        }
        foreach (array_merge((array) ($about['paragraphs'] ?? []), $g ? (array) ($about['g_intro'] ?? []) : []) as $paragraph) {
            if (is_string($paragraph) && filled($paragraph)) {
                $l[] = $paragraph;
                $l[] = '';
            }
        }
        $l[] = '- ' . $t['city'] . ' ' . __($contact['city']) . ', ' . __('България');
        if (filled($legal['company'] ?? null)) {
            $l[] = '- ' . $t['company'] . ' ' . $legal['company'] . (filled($legal['eik'] ?? null) ? ', EIK ' . $legal['eik'] : '') . (filled($legal['address'] ?? null) ? ', ' . $legal['address'] : '');
        }

        // Team: one list of everyone with the size in the heading, then a note on who the founders are (the first two members, as in SiteKnowledge).
        $team = array_values(array_filter((array) site('team'), fn ($m) => filled($m['name'] ?? null)));
        if ($team) {
            $l[] = '';
            $l[] = '## ' . TeamFacts::heading($locale);
            $l[] = '';
            foreach ($team as $member) {
                $role = $g && filled($member['g_role'] ?? null) ? $member['g_role'] : ($member['role'] ?? '');
                $description = $g && filled($member['g_description'] ?? null) ? $member['g_description'] : ($member['description'] ?? '');
                $skills = array_filter((array) ($g && ! empty($member['g_skills']) ? $member['g_skills'] : ($member['skills'] ?? [])), 'is_string');
                $l[] = '- **' . $member['name'] . '**' . ($role ? ', ' . $role : '') . ($description ? '. ' . rtrim($description, '.') : '')
                    . ($skills ? '. ' . $t['skills'] . ' ' . implode(', ', $skills) : '') . (filled($member['linkedin'] ?? null) ? ' (' . $member['linkedin'] . ')' : '');
            }
            if (($note = TeamFacts::founderNote($locale)) !== '') {
                $l[] = '';
                $l[] = $note;
            }
        }

        // Services
        $l[] = '';
        $l[] = '## ' . $t['services'];
        foreach ((array) site('service_pages') as $key => $service) {
            if (! empty($service['g_only']) && ! $g) {
                continue;
            }
            $l[] = '';
            $l[] = '### ' . $service['title'];
            $l[] = '';
            $l[] = PageController::serviceUrl($key, $locale);
            $l[] = '';
            if ($g && filled($service['g_what'] ?? null)) {
                $l[] = $service['g_what'];
                $l[] = '';
            }
            $l[] = $service['lead'];
            if (! empty($service['includes'])) {
                $l[] = '';
                $l[] = '**' . $t['includes'] . '**';
                $l[] = '';
                foreach ((array) $service['includes'] as $item) {
                    $l[] = '- ' . $item;
                }
            }
            if (! empty($service['problems'])) {
                $l[] = '';
                $l[] = '**' . $t['problems'] . '**';
                $l[] = '';
                foreach ((array) $service['problems'] as $item) {
                    $l[] = '- ' . $item;
                }
            }
            if (filled($service['result'] ?? null)) {
                $l[] = '';
                $l[] = '**' . $t['result'] . '** ' . $service['result'];
            }
        }
        $l[] = '';
        $l[] = '### ' . $t['check'];
        $l[] = '';
        $l[] = '[' . $t['check'] . '](' . lroute('site-check') . '). ' . $t['check_text'];

        // Packages, with the same figures as /llms.txt.
        $l[] = '';
        $l[] = '## ' . $t['packages'];
        $l[] = '';
        $l[] = $t['price_model'];
        $l[] = '';
        array_push($l, ...self::planLines($content->withDefaults('plans', $locale), $g, $locale));
        $l[] = '- ' . $t['all_packages'] . ' ' . lroute('packages');

        // FAQ
        $questions = array_values(array_filter((array) site('questions'), fn ($q) => filled($q['q'] ?? null) && filled($q['a'] ?? null)));
        if ($questions) {
            $l[] = '';
            $l[] = '## ' . $t['faq'];
            $l[] = '';
            $l[] = lroute('faq');
            foreach ($questions as $q) {
                $l[] = '';
                $l[] = '### ' . $q['q'];
                $l[] = '';
                $l[] = $q['a'];
            }
        }

        // Industry pages (Bulgarian only, so the English file links the Bulgarian pages and says nothing else about them).
        $industries = $locale === 'en' && ! config('creatium_en.industry_pages') ? [] : (array) site('industry_pages');
        if ($industries) {
            $l[] = '';
            $l[] = '## ' . $t['industries'];
            $l[] = '';
            foreach ($industries as $slug => $industry) {
                $l[] = '- [' . $industry['title'] . '](' . route('industry', $slug) . '). ' . $industry['lead'];
            }
        }

        // Posts
        if (config('creatium.blog_enabled')) {
            $posts = Post::visible($locale)->with('category')->latest('published_at')->take(self::POSTS)->get();
            if ($posts->isNotEmpty()) {
                $l[] = '';
                $l[] = '## ' . $t['posts'];
                $l[] = '';
                foreach ($posts as $post) {
                    $summary = self::plain($post->excerptOrSummary($locale));
                    $l[] = '- [' . $post->tr('title', $locale) . '](' . $post->url($locale) . ') ' . $t['published'] . ' ' . $post->published_at->toDateString() . ($summary !== '' ? '. ' . $summary : '');
                }
            }
        }

        // How to evaluate us: only facts the site already states.
        if ($facts = self::evaluationLines($locale)) {
            $l[] = '';
            $l[] = '## ' . $t['evaluate'];
            $l[] = '';
            array_push($l, ...$facts);
        }

        // Contacts
        $l[] = '';
        $l[] = '## ' . $t['contacts'];
        $l[] = '';
        $l[] = '- ' . $t['email'] . ' ' . ($contact['email'] ?? '');
        foreach ($g ? g_phones() : (array) ($contact['phones'] ?? []) as $phone) {
            $l[] = '- ' . $t['phone'] . ' ' . $phone;
        }
        $l[] = '- ' . $t['book'] . ' ' . lroute('contact') . '#konsultatsiya';
        foreach (['facebook' => 'Facebook', 'linkedin' => 'LinkedIn', 'google_business' => 'Google Business'] as $key => $label) {
            if (filled($contact[$key] ?? null)) {
                $l[] = '- ' . $label . ' ' . $contact[$key];
            }
        }

        return implode("\n", $l) . "\n";
    }
}
