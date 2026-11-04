<?php

namespace App\Support\SiteCheck;

use Illuminate\Support\Facades\Http;

/**
 * Скорост на телефон от Google PageSpeed Insights API v5 (по желание: само с PAGESPEED_API_KEY, само за началната страница).
 * Това е заявка към Google, затова минава през обикновения Http клиент, а не през SiteChecker::fetch().
 */
class PageSpeed
{
    public const ENDPOINT = 'https://www.googleapis.com/pagespeedonline/v5/runPagespeed';

    public static function configured(): bool
    {
        return filled(config('services.pagespeed.key'));
    }

    /** @return ?array{score: int, lcp: ?float, cls: ?float, inp: ?float, source: string} lcp и inp в секунди/милисекунди: lcp в s, inp в ms */
    public function run(string $url): ?array
    {
        if (! self::configured()) {
            return null;
        }

        try {
            $response = Http::timeout(40)->get(self::ENDPOINT, [
                'url' => $url,
                'strategy' => 'mobile',
                'category' => 'performance',
                'key' => config('services.pagespeed.key'),
            ]);
        } catch (\Throwable) {
            return null;
        }
        if (! $response->successful() || ! is_numeric($response->json('lighthouseResult.categories.performance.score'))) {
            return null;
        }

        $field = $response->json('loadingExperience.metrics') ?? [];
        $audits = $response->json('lighthouseResult.audits') ?? [];

        $lcp = $field['LARGEST_CONTENTFUL_PAINT_MS']['percentile'] ?? ($audits['largest-contentful-paint']['numericValue'] ?? null);
        $cls = isset($field['CUMULATIVE_LAYOUT_SHIFT_SCORE']['percentile']) ? $field['CUMULATIVE_LAYOUT_SHIFT_SCORE']['percentile'] / 100 : ($audits['cumulative-layout-shift']['numericValue'] ?? null);
        $inp = $field['INTERACTION_TO_NEXT_PAINT']['percentile'] ?? ($audits['interaction-to-next-paint']['numericValue'] ?? null);

        return [
            'score' => (int) round($response->json('lighthouseResult.categories.performance.score') * 100),
            'lcp' => is_numeric($lcp) ? round($lcp / 1000, 1) : null,
            'cls' => is_numeric($cls) ? round($cls, 2) : null,
            'inp' => is_numeric($inp) ? (int) round($inp) : null,
            'source' => isset($field['LARGEST_CONTENTFUL_PAINT_MS']) ? 'field' : 'lab',
        ];
    }
}
