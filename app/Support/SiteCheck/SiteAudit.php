<?php

namespace App\Support\SiteCheck;

/**
 * Пълната безплатна проверка: началната страница (SiteChecker), обхождане на сайта (SiteCrawler)
 * и по желание скорост на телефон (PageSpeed). Връща един отчет, който се праща по имейл и се пази в запитването.
 */
class SiteAudit
{
    public function __construct(private SiteChecker $checker, private SiteCrawler $crawler, private PageSpeed $pageSpeed)
    {
    }

    public function run(string $domain): array
    {
        $report = $this->checker->check($domain);
        if (! $report['reachable']) {
            return $report;
        }
        $report['home_score'] = $report['score'];

        // Обхождане: каквото и да се счупи, началните проверки остават.
        try {
            $crawl = $this->crawler->crawl($report['url']);
        } catch (\Throwable $e) {
            $crawl = ['crawl' => ['pages' => 0, 'analyzed' => 0, 'limit' => (int) config('creatium.site_check.crawl_limit'), 'limited' => false, 'limited_by' => null, 'seconds' => 0, 'error' => $e->getMessage()], 'issues' => []];
        }
        $report['crawl'] = $crawl['crawl'];
        $report['issues'] = $crawl['issues'];

        // „Готовност за Google Shopping“: само за онлайн магазини, от вече обходените страници; не влияе на оценката.
        try {
            $report['shopping'] = ShoppingReadiness::evaluate($crawl['shop'] ?? [], str_starts_with($report['url'], 'https://'));
        } catch (\Throwable) {
            $report['shopping'] = null;
        }

        $report['pagespeed'] = null;
        try {
            if ($speed = $this->pageSpeed->run($report['url'])) {
                $report['pagespeed'] = $speed;
                $report['checks'][] = self::speedCheck($speed);
                $points = collect($report['checks'])->sum(fn ($c) => ['ok' => 1, 'warn' => 0.5, 'fail' => 0][$c['status']]);
                $report['home_score'] = (int) round(100 * $points / count($report['checks']));
            }
        } catch (\Throwable) {
        }

        $report['score'] = self::score($report['home_score'], $report['issues'], (int) ($report['crawl']['analyzed'] ?? 0));

        return $report;
    }

    /** Оценката на началната страница минус наказание за проблемите по сайта: errors тежат повече от warnings. */
    public static function score(int $homeScore, array $issues, int $pages): int
    {
        $penalty = 0;
        foreach ($issues as $issue) {
            $share = min(1, $issue['count'] / max(1, $pages));
            $penalty += $issue['severity'] === 'error' ? 3 + 5 * $share : 1 + 2 * $share;
        }

        return max(0, (int) round($homeScore - min(60, $penalty)));
    }

    private static function speedCheck(array $speed): array
    {
        $parts = array_filter([
            $speed['lcp'] !== null ? 'LCP ' . number_format($speed['lcp'], 1, ',', '') . ' s' : null,
            $speed['cls'] !== null ? 'CLS ' . number_format($speed['cls'], 2, ',', '') : null,
            $speed['inp'] !== null ? 'INP ' . $speed['inp'] . ' ms' : null,
        ]);
        $status = $speed['score'] >= 90 ? 'ok' : ($speed['score'] >= 50 ? 'warn' : 'fail');

        return [
            'status' => $status,
            'title' => __('Скорост на телефон: :score от 100', ['score' => $speed['score']]),
            'detail' => implode(' · ', $parts) . ($parts ? '. ' : '') . ($status === 'ok'
                ? __('Страницата се зарежда бързо и на телефон.')
                : __('Бавната страница на телефон губи посетители. Най-често помагат по-леки снимки, по-малко добавки и по-добър хостинг.')),
        ];
    }

    /**
     * Всичко за оправяне в един списък: провалени и предупредителни проверки на началната страница плюс проблемите по сайта.
     *
     * @return list<array{severity: string, title: string, detail: string, examples: array, count: ?int}>
     */
    public static function items(array $report): array
    {
        $items = [];
        foreach ($report['checks'] ?? [] as $check) {
            if (($check['status'] ?? 'ok') !== 'ok') {
                $items[] = ['severity' => $check['status'] === 'fail' ? 'error' : 'warning', 'title' => $check['title'], 'detail' => $check['detail'] ?? '', 'examples' => [], 'count' => null];
            }
        }
        foreach ($report['issues'] ?? [] as $issue) {
            $d = SiteCrawler::describe($issue['key'], $issue['count']);
            $items[] = ['severity' => $issue['severity'], 'title' => $d['title'], 'detail' => $d['why'], 'examples' => $issue['examples'] ?? [], 'count' => $issue['count']];
        }
        usort($items, fn ($a, $b) => ($a['severity'] === 'error' ? 0 : 1) <=> ($b['severity'] === 'error' ? 0 : 1));

        return $items;
    }
}
