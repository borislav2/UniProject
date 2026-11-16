<?php

namespace App\Support\Seo;

use App\Models\OutreachSetting;
use App\Models\Post;
use App\Models\SeoReport;
use App\Support\Content;
use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\Psr7\UriResolver;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * SEO здраве: обхожда НАШИЯ сайт отвътре (InternalSite, без заявки по мрежата), от началната страница и sitemap.xml, и
 * събира за всеки адрес състояние, заглавие, описание, h1, заглавия, canonical, noindex, hreflang, картинки, вътрешни
 * връзки, JSON-LD и думи; после проблемите на целия сайт: сираци, липсващи в sitemap, счупени връзки, вериги от
 * пренасочвания и дълбочина от началната страница. Проверява само публичния дизайн, който е включен в момента.
 *
 * Няма cron: най-много веднъж на interval_hours (24) го задвижват посещенията след отговора (Middleware\RunSeoHealth),
 * под ключалка в кеша. Ръчно: бутон „Провери сега“ и php artisan creatium:seo-health. Грешките се записват и никога не чупят страница.
 */
class SiteHealth
{
    public const STATE_KEY = 'seo_health_state';

    /** Код => [тежест, заглавие, какво да се направи]. Редът е редът на екрана. */
    public const ISSUES = [
        'server_errors' => ['error', 'Страници с грешка на сървъра', 'Страницата връща грешка 5xx. Прегледайте дневника (storage/logs).'],
        'sitemap_bad' => ['error', 'Адреси в sitemap без отговор 200', 'Махнете ги от sitemap или поправете страницата.'],
        'broken_links' => ['error', 'Счупени вътрешни връзки', 'Връзка към страница, която не съществува или дава грешка. Поправете връзката.'],
        'noindex_in_sitemap' => ['error', 'Страница с noindex, но в sitemap', 'Или махнете noindex, или я изключете от sitemap.'],
        'no_title' => ['error', 'Страници без заглавие', 'Всяка страница има нужда от <title>.'],
        'no_description' => ['error', 'Страници без meta описание', 'Напишете описание в „SEO на страниците“.'],
        'no_h1' => ['error', 'Страници без заглавие h1', 'Всяка страница има точно едно главно заглавие.'],
        'json_ld_invalid' => ['error', 'Невалидни структурирани данни (JSON-LD)', 'Блокът не е валиден JSON или няма @context.'],
        'redirect_chains' => ['warning', 'Вериги от пренасочвания', 'Пренасочване към пренасочване. Сочете направо към крайния адрес.'],
        'links_to_redirects' => ['warning', 'Връзки към пренасочени адреси', 'Сменете връзката с крайния адрес.'],
        'hreflang' => ['warning', 'Проблеми с hreflang (БГ и EN)', 'Двата езика трябва да сочат един към друг и всеки към себе си.'],
        'canonical_missing' => ['warning', 'Страници без canonical', 'Добавете canonical към самата страница.'],
        'canonical_other' => ['warning', 'Canonical към друг адрес', 'Страницата казва на Google да брои друг адрес. Уверете се, че е нарочно.'],
        'title_length' => ['warning', 'Заглавия извън 30 до 60 знака', 'Твърде дълго се реже в Google, твърде късо не използва мястото.'],
        'description_length' => ['warning', 'Описания извън 70 до 155 знака', 'Твърде дълго се реже в Google, твърде късо не убеждава.'],
        'title_duplicate' => ['warning', 'Повтарящи се заглавия', 'Две страници с едно заглавие си конкурират. Направете ги различни.'],
        'description_duplicate' => ['warning', 'Повтарящи се описания', 'Всяка страница има свое описание.'],
        'multiple_h1' => ['warning', 'Повече от един h1', 'Оставете едно главно заглавие.'],
        'heading_skip' => ['warning', 'Прескочени нива на заглавията', 'Например h1 право към h3. Подредете ги по ред.'],
        'images_no_alt' => ['warning', 'Картинки без alt', 'Добавете alt (за украса е достатъчно alt="").'],
        'orphans' => ['warning', 'Сираци: в sitemap, но без вътрешна връзка', 'Добавете връзка към страницата отнякъде от сайта.'],
        'missing_from_sitemap' => ['warning', 'Индексируеми страници извън sitemap', 'Добавете ги в sitemap или ги изключете от индекса.'],
        'deep_pages' => ['warning', 'Страници на повече от 3 клика от началната', 'Приближете ги с връзка от по-горна страница.'],
        'images_no_size' => ['notice', 'Картинки без ширина и височина', 'Без размери страницата подскача при зареждане.'],
        'thin_pages' => ['notice', 'Страници с малко текст (под 200 думи)', 'За информационна страница често е малко.'],
        'noindex_pages' => ['notice', 'Страници с noindex', 'Нарочно скрити от Google. Проверете, че са точно тези, които искате.'],
    ];

    private const SEVERITY_RULE = ['error' => [4, 30], 'warning' => [1, 10], 'notice' => [0, 0]];

    private const SKIP_PATHS = '#^/(admin|login|logout|chat|o/|otpisvane|nov-dizain|up$|storage|build|images|files|uploads)#';

    private const NON_PAGE = '#\.(jpe?g|png|gif|webp|avif|svg|ico|css|js|json|xml|txt|pdf|zip|woff2?|mp4|webm)$#i';

    private string $root = '';

    private InternalSite $site;

    /** @var array<string, array> нормализиран адрес => запис */
    private array $pages = [];

    public static function enabled(): bool
    {
        return (bool) config('creatium.seo_health.auto');
    }

    public static function state(): array
    {
        return OutreachSetting::get(self::STATE_KEY, []);
    }

    public static function latest(): ?SeoReport
    {
        return SeoReport::latestOne();
    }

    public static function previous(): ?SeoReport
    {
        return SeoReport::orderByDesc('ran_at')->orderByDesc('id')->skip(1)->first();
    }

    /** Дошло ли е времето за автоматична проверка (нова на interval_hours, а след грешка най-рано след час). */
    public static function due(): bool
    {
        $state = self::state();
        if (! empty($state['last_error']) && ! empty($state['last_attempt_at']) && \Illuminate\Support\Carbon::parse($state['last_attempt_at'])->gt(now()->subHour())) {
            return false;
        }
        $last = self::latest();

        return ! $last || $last->ran_at->lte(now()->subHours((int) config('creatium.seo_health.interval_hours')));
    }

    /** Автоматичната стъпка: само ако е време, под ключалка, никога не хвърля. */
    public static function tick(): ?SeoReport
    {
        if (! self::enabled() || ! self::due()) {
            return null;
        }

        return self::runLocked();
    }

    /** Пуска проверката, ако друга не върви. Връща отчета или null (върви друга или грешка). */
    public static function runLocked(): ?SeoReport
    {
        $lock = Cache::lock('seo-health:run', 600);
        if (! $lock->get()) {
            return null;
        }
        try {
            return (new self)->run();
        } catch (Throwable $e) {
            report($e);
            self::remember(['last_attempt_at' => now()->toIso8601String(), 'last_error' => mb_substr($e->getMessage(), 0, 300)]);
            Log::warning('SEO здраве: ' . $e->getMessage());

            return null;
        } finally {
            $lock->release();
        }
    }

    public static function running(): bool
    {
        $lock = Cache::lock('seo-health:run', 600);
        if ($lock->get()) {
            $lock->release();

            return false;
        }

        return true;
    }

    private static function remember(array $changes): void
    {
        OutreachSetting::put(self::STATE_KEY, $changes + self::state());
    }

    /** Обхожда сайта, записва и връща отчета. */
    public function run(?InternalSite $site = null): SeoReport
    {
        @set_time_limit((int) config('creatium.seo_health.budget_seconds') + 60);
        $started = microtime(true);
        self::remember(['last_attempt_at' => now()->toIso8601String(), 'last_error' => null]);

        $crawl = function (InternalSite $site) use ($started) {
            $this->site = $site;
            $this->root = $site->root();
            $this->pages = [];

            return $this->crawl($started);
        };
        // $site: тестове с подменен сайт; иначе истинският ни сайт отвътре.
        $data = $site ? $crawl($site) : InternalSite::with($crawl);

        $counts = ['error' => 0, 'warning' => 0];
        foreach ($data['issues'] as $code => $items) {
            $sev = self::ISSUES[$code][0] ?? 'notice';
            if (isset($counts[$sev])) {
                $counts[$sev] += $data['counts'][$code] ?? count($items);
            }
        }
        $score = self::score($data['counts']);
        $data['score'] = $score;

        $report = SeoReport::create([
            'ran_at' => now(), 'score' => $score, 'pages' => $data['crawled'],
            'errors' => $counts['error'], 'warnings' => $counts['warning'],
            'seconds' => (int) round(microtime(true) - $started), 'data' => $data,
        ]);

        $keep = (int) config('creatium.seo_health.keep_reports');
        $old = SeoReport::orderByDesc('ran_at')->orderByDesc('id')->skip($keep)->take(1000)->pluck('id');
        if ($old->isNotEmpty()) {
            SeoReport::whereIn('id', $old)->delete();
        }
        self::remember(['last_error' => null, 'last_ok_at' => now()->toIso8601String()]);

        return $report;
    }

    /** 100 минус наказания по групи (всяка група има таван, за да не потъне оценката от един повтарящ се проблем). */
    public static function score(array $counts): int
    {
        $penalty = 0;
        foreach ($counts as $code => $count) {
            [$sev] = self::ISSUES[$code] ?? ['notice'];
            [$weight, $cap] = self::SEVERITY_RULE[$sev];
            $penalty += min($cap, $weight * $count);
        }

        return (int) max(0, round(100 - $penalty));
    }

    // ---------------------------------------------------------------- обхождане

    private function crawl(float $started): array
    {
        $max = (int) config('creatium.seo_health.max_urls');
        $budget = (int) config('creatium.seo_health.budget_seconds');

        $sitemap = array_values(array_unique(array_filter(array_map(fn ($u) => $this->norm($u), PageInspector::sitemap($this->site)))));
        $home = $this->norm($this->root . '/');
        $queue = [$home];
        $seen = [$home => true];
        $skipped = 0;
        $push = function (?string $url) use (&$queue, &$seen) {
            if ($url && ! isset($seen[$url])) {
                $seen[$url] = true;
                $queue[] = $url;
            }
        };

        $i = 0;
        $sitemapQueued = false;
        while (true) {
            if ($i >= count($queue)) {
                if ($sitemapQueued) {
                    break;
                }
                foreach ($sitemap as $u) {
                    $push($u);
                }
                $push($this->norm($this->root . '/en'));
                $sitemapQueued = true;

                continue;
            }
            if (count($this->pages) >= $max || (microtime(true) - $started) > $budget) {
                $skipped = count($queue) - $i;
                break;
            }
            $url = $queue[$i++];
            $record = $this->fetch($url);
            $this->pages[$url] = $record;

            if ($record['status'] >= 300 && $record['status'] < 400 && $record['location']) {
                $push($this->norm($record['location'], $url));
            }
            foreach ($record['out'] as $target) {
                $push($target);
            }
            foreach ($record['hreflang'] as $target) {
                $push($this->norm($target, $url));
            }
        }

        return $this->analyze($sitemap, $skipped);
    }

    private function fetch(string $url): array
    {
        $got = $this->site->get($url);
        $html = $got['status'] === 200 && (str_contains(strtolower($got['type']), 'html') || str_starts_with(ltrim($got['body']), '<!'));
        $meta = $html ? PageMeta::parse($got['body'], $url) : null;

        $out = [];
        foreach ($meta['links'] ?? [] as $href) {
            $target = $this->norm($href, $url);
            if ($target && $target !== $url) {
                $out[$target] = true;
            }
        }

        return [
            'url' => $url, 'status' => $got['status'], 'location' => $got['location'], 'html' => $html,
            'title' => $meta['title'] ?? null, 'description' => $meta['description'] ?? null,
            'noindex' => $html && ($meta['noindex'] || str_contains(strtolower($got['robots'] ?? ''), 'noindex')),
            'canonical' => $meta['canonical'] ?? null,
            'h1' => $meta['h1'] ?? [], 'skips' => $html ? PageMeta::headingSkips($meta['headings']) : 0,
            'hreflang' => $meta['hreflang'] ?? [], 'images' => $meta['images'] ?? null,
            'jsonld' => $meta['jsonld'] ?? null, 'words' => $meta['words'] ?? 0,
            'out' => array_keys($out),
        ];
    }

    /** Нормализиран вътрешен адрес на страница или null (чужд, файл, служебен път, друга заявка). */
    private function norm(string $href, ?string $base = null): ?string
    {
        $href = trim($href);
        if ($href === '' || preg_match('/^(#|mailto:|tel:|javascript:|sms:|data:)/i', $href)) {
            return null;
        }
        try {
            $uri = new Uri($href);
            if ($base) {
                $uri = UriResolver::resolve(new Uri($base), $uri);
            } elseif ($uri->getHost() === '') {
                $uri = UriResolver::resolve(new Uri($this->root . '/'), $uri);
            }
        } catch (Throwable) {
            return null;
        }
        if (! $this->site->isOurs((string) $uri->withFragment(''))) {
            return null;
        }
        $path = $uri->getPath() === '' ? '/' : $uri->getPath();
        if (preg_match(self::NON_PAGE, $path) || preg_match(self::SKIP_PATHS, $path)) {
            return null;
        }
        if ($path !== '/') {
            $path = rtrim($path, '/');
        }
        parse_str($uri->getQuery(), $query);
        $keep = isset($query['page']) && is_numeric($query['page']) && (int) $query['page'] > 1 ? '?page=' . (int) $query['page'] : '';

        return $this->root . ($path === '/' ? '/' : $path) . $keep;
    }

    private function path(string $url): string
    {
        return substr($url, strlen($this->root)) ?: '/';
    }

    /** Следва записаните пренасочвания: [краен адрес, брой скокове]. */
    private function finalOf(string $url): array
    {
        $hops = 0;
        $guard = 0;
        while (isset($this->pages[$url]) && $this->pages[$url]['status'] >= 300 && $this->pages[$url]['status'] < 400 && $this->pages[$url]['location'] && $guard++ < 8) {
            $next = $this->norm($this->pages[$url]['location'], $url);
            if (! $next || $next === $url) {
                break;
            }
            $url = $next;
            $hops++;
        }

        return [$url, $hops];
    }

    // ---------------------------------------------------------------- анализ

    private function analyze(array $sitemap, int $skipped): array
    {
        $issues = array_fill_keys(array_keys(self::ISSUES), []);
        $add = function (string $code, string $url, string $detail = '') use (&$issues) {
            $issues[$code][] = ['url' => $this->path($url), 'detail' => $detail];
        };
        $inSitemap = array_flip($sitemap);

        // Входящи връзки (след пренасочвания) и най-кратък път от началната страница.
        $inlinks = [];
        $graph = [];
        foreach ($this->pages as $url => $p) {
            foreach ($p['out'] as $target) {
                [$final] = $this->finalOf($target);
                $graph[$url][] = $final;
                if ($final !== $url) {
                    $inlinks[$final][$url] = true;
                }
            }
        }
        $home = $this->norm($this->root . '/');
        $depth = [$home => 0];
        $bfs = [$home];
        for ($i = 0; $i < count($bfs); $i++) {
            foreach ($graph[$bfs[$i]] ?? [] as $next) {
                if (! isset($depth[$next])) {
                    $depth[$next] = $depth[$bfs[$i]] + 1;
                    $bfs[] = $next;
                }
            }
        }

        $titles = $descriptions = [];
        $rows = [];
        foreach ($this->pages as $url => $p) {
            $isPage = $p['html'] && $p['status'] === 200;
            $indexable = $isPage && ! $p['noindex'];

            if ($p['status'] >= 500 || $p['status'] === 0) {
                $add('server_errors', $url, 'Отговор ' . $p['status']);
            }
            if (isset($inSitemap[$url]) && $p['status'] !== 200) {
                $add('sitemap_bad', $url, 'Отговор ' . $p['status']);
            }
            if ($p['status'] >= 300 && $p['status'] < 400) {
                [$final, $hops] = $this->finalOf($url);
                if ($hops > 1) {
                    $add('redirect_chains', $url, $hops . ' скока до ' . $this->path($final));
                }
            }

            foreach ($p['out'] as $target) {
                $t = $this->pages[$target] ?? null;
                if (! $t) {
                    continue;
                }
                if ($t['status'] >= 400 || $t['status'] === 0) {
                    $issues['broken_links'][] = ['url' => $this->path($target), 'detail' => 'отговор ' . $t['status'] . ', връзка от ' . $this->path($url)];
                } elseif ($t['status'] >= 300 && $t['status'] < 400 && $isPage) {
                    [$final] = $this->finalOf($target);
                    $issues['links_to_redirects'][] = ['url' => $this->path($url), 'detail' => $this->path($target) . ' → ' . $this->path($final)];
                }
            }

            if ($isPage) {
                if ($p['jsonld'] && $p['jsonld']['invalid'] > 0) {
                    $add('json_ld_invalid', $url, implode(', ', array_unique($p['jsonld']['errors'])));
                }
                if ($p['noindex']) {
                    $add('noindex_pages', $url);
                    if (isset($inSitemap[$url])) {
                        $add('noindex_in_sitemap', $url);
                    }
                }
            }

            if ($indexable) {
                $titleState = Snippet::titleState($p['title']);
                $descState = Snippet::descriptionState($p['description']);
                $titleState === 'missing' ? $add('no_title', $url) : ($titleState !== 'ok' ? $add('title_length', $url, Snippet::length($p['title']) . ' знака') : null);
                $descState === 'missing' ? $add('no_description', $url) : ($descState !== 'ok' ? $add('description_length', $url, Snippet::length($p['description']) . ' знака') : null);
                $titles[$url] = $p['title'];
                $descriptions[$url] = $p['description'];

                if (count($p['h1']) === 0) {
                    $add('no_h1', $url);
                } elseif (count($p['h1']) > 1) {
                    $add('multiple_h1', $url, count($p['h1']) . ' заглавия h1');
                }
                if ($p['skips'] > 0) {
                    $add('heading_skip', $url, $p['skips'] . ' прескачане');
                }
                if (! $p['canonical']) {
                    $add('canonical_missing', $url);
                } elseif (rtrim($p['canonical'], '/') !== rtrim($url, '/')) {
                    $add('canonical_other', $url, 'сочи към ' . $p['canonical']);
                }
                if ($p['images'] && $p['images']['no_alt'] > 0) {
                    $add('images_no_alt', $url, $p['images']['no_alt'] . ' от ' . $p['images']['total'] . (count($p['images']['examples']) ? ' (' . implode(', ', $p['images']['examples']) . ')' : ''));
                }
                if ($p['images'] && $p['images']['no_size'] > 0) {
                    $add('images_no_size', $url, $p['images']['no_size'] . ' от ' . $p['images']['total']);
                }
                if ($p['words'] < 200) {
                    $add('thin_pages', $url, $p['words'] . ' думи');
                }

                $selfCanonical = $p['canonical'] && rtrim($p['canonical'], '/') === rtrim($url, '/');
                if (! isset($inSitemap[$url]) && $selfCanonical && ! str_contains($url, '?page=')) {
                    $add('missing_from_sitemap', $url);
                }
                if (isset($inSitemap[$url]) && $url !== $home && empty($inlinks[$url])) {
                    $add('orphans', $url);
                }
                if (isset($depth[$url]) && $depth[$url] > 3) {
                    $add('deep_pages', $url, $depth[$url] . ' клика');
                }
            }

            if ($isPage && $p['hreflang']) {
                $self = false;
                foreach ($p['hreflang'] as $lang => $href) {
                    $target = $this->norm($href, $url);
                    if ($target === $url) {
                        $self = true;

                        continue;
                    }
                    $t = $target ? ($this->pages[$target] ?? null) : null;
                    if (! $t) {
                        continue;
                    }
                    if ($t['status'] !== 200) {
                        $add('hreflang', $url, "$lang: " . $this->path($target) . ' връща ' . $t['status']);
                    } else {
                        $back = array_map(fn ($h) => $this->norm($h, $target), $t['hreflang']);
                        if (! in_array($url, $back, true)) {
                            $add('hreflang', $url, "$lang: " . $this->path($target) . ' не сочи обратно');
                        }
                    }
                }
                if (! $self) {
                    $add('hreflang', $url, 'няма връзка към самата страница');
                }
            }

            $rows[] = [
                'p' => $this->path($url), 's' => $p['status'], 'd' => $depth[$url] ?? null,
                't' => Snippet::length($p['title']), 'm' => Snippet::length($p['description']), 'h' => count($p['h1']), 'w' => $p['words'],
                'i' => count($inlinks[$url] ?? []), 'o' => count($p['out']), 'n' => $p['noindex'] ? 1 : 0, 'sm' => isset($inSitemap[$url]) ? 1 : 0,
            ];
        }

        foreach (['title' => 'title_duplicate', 'description' => 'description_duplicate'] as $field => $code) {
            foreach (Snippet::duplicates($field === 'title' ? $titles : $descriptions) as $urls) {
                foreach ($urls as $url) {
                    $others = array_map(fn ($u) => $this->path($u), array_diff($urls, [$url]));
                    $add($code, $url, 'същото като ' . implode(', ', array_slice($others, 0, 3)));
                }
            }
        }

        $counts = [];
        foreach ($issues as $code => $items) {
            $counts[$code] = count($items);
            $issues[$code] = array_slice($items, 0, 100);
        }
        $issues = array_filter($issues);
        $counts = array_filter($counts);

        $links = [];
        foreach ($this->pages as $url => $p) {
            $links[self::keyOf($this->path($url))] = count($inlinks[$url] ?? []);
        }

        return [
            'root' => $this->root,
            'design' => \App\Support\Design::configured(),
            'crawled' => count($this->pages),
            'sitemap' => count($sitemap),
            'skipped' => $skipped,
            'max_depth' => $depth ? max($depth) : 0,
            'counts' => $counts,
            'issues' => $issues,
            'inlinks' => $links,
            'rows' => $rows,
        ];
    }

    public static function keyOf(string $path): string
    {
        $query = '';
        if (str_contains($path, '?')) {
            [$path, $query] = explode('?', $path, 2);
            $query = '?' . $query;
        }

        return '/' . trim($path, '/') . $query;
    }

    // ---------------------------------------------------------------- за екраните

    /** Къде се поправя страницата: „SEO на страниците“ или статията, иначе null. */
    public static function editUrl(string $path): ?string
    {
        $key = self::keyOf($path);
        foreach (array_keys(Content::SEO_PAGES) as $page) {
            foreach (['bg', 'en'] as $locale) {
                if (self::keyOf((string) parse_url(lroute($page, [], $locale), PHP_URL_PATH)) === $key) {
                    return route('admin.seo.edit', $page);
                }
            }
        }
        if (preg_match('#^/(?:en/)?blog/([^/]+)$#', $key, $m)) {
            $post = Post::where('slug->bg', $m[1])->orWhere('slug->en', $m[1])->first();

            return $post ? route('admin.posts.edit', $post) : null;
        }

        return null;
    }

    /** Промяна спрямо предишната проверка: ['score' => +n, 'errors' => ±n, 'warnings' => ±n, 'new' => n, 'fixed' => n] или null. */
    public static function trend(?SeoReport $now = null, ?SeoReport $before = null): ?array
    {
        $now ??= self::latest();
        $before ??= self::previous();
        if (! $now || ! $before) {
            return null;
        }
        $keys = fn (SeoReport $r) => collect($r->data['issues'] ?? [])->flatMap(fn ($items, $code) => collect($items)->map(fn ($i) => $code . '|' . $i['url'] . '|' . $i['detail']))->all();
        $a = $keys($now);
        $b = $keys($before);

        return [
            'score' => $now->score - $before->score,
            'errors' => $now->errors - $before->errors,
            'warnings' => $now->warnings - $before->warnings,
            'new' => count(array_diff($a, $b)),
            'fixed' => count(array_diff($b, $a)),
        ];
    }
}
