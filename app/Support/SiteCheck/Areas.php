<?php

namespace App\Support\SiteCheck;

/**
 * Петте области на отчета (Техническо, Съдържание и заглавия, Видимост в AI, Локално и доверие, Проследяване):
 * малка оценка за всяка от проблемите, намерени от нашите проверки. Смята се в момента на показване от записания отчет,
 * затова работи и за стари отчети (без област и без ключове на проверките). Оценката не е позиция в Google.
 */
class Areas
{
    /** Проверките на началната страница с ключ, които нямат двойник в обхождането (иначе щяха да се броят два пъти). */
    public const CHECK_AREAS = [
        'https' => 'technical', 'http_redirect' => 'technical', 'speed' => 'technical', 'viewport' => 'technical',
        'indexing' => 'technical', 'sitemap' => 'technical', 'speedpsi' => 'technical', 'ai' => 'ai', 'tel' => 'local',
    ];

    /** Област на проверка от началната страница (за списъка в отчета, включително тези с двойник в обхождането). */
    public static function checkArea(?string $key): string
    {
        return self::CHECK_AREAS[$key ?? ''] ?? match ($key) {
            'title', 'description', 'h1', 'og', 'alt' => 'content',
            'schema' => 'ai',
            default => 'technical',
        };
    }

    public static function weight(string $severity, float $share): float
    {
        return match ($severity) {
            'error' => 14 + 16 * $share,
            'notice' => 2,
            default => 5 + 7 * $share,
        };
    }

    /**
     * @return array<string, array{area: string, label: string, hint: string, score: int, errors: int, warnings: int, notices: int}>
     */
    public static function scores(array $report): array
    {
        $pages = max(1, (int) ($report['crawl']['analyzed'] ?? 1));
        $out = [];
        foreach (IssueCatalog::AREAS as $area) {
            $out[$area] = ['area' => $area, 'label' => IssueCatalog::areaLabel($area), 'hint' => IssueCatalog::areaHint($area), 'penalty' => 0.0, 'errors' => 0, 'warnings' => 0, 'notices' => 0];
        }
        foreach ($report['issues'] ?? [] as $issue) {
            $area = IssueCatalog::area($issue['key']);
            $sev = $issue['severity'] ?? IssueCatalog::severity($issue['key']);
            $out[$area]['penalty'] += self::weight($sev, min(1, ($issue['count'] ?? 1) / $pages));
            $out[$area][$sev === 'error' ? 'errors' : ($sev === 'notice' ? 'notices' : 'warnings')]++;
        }
        foreach ($report['checks'] ?? [] as $check) {
            $area = self::CHECK_AREAS[$check['key'] ?? ''] ?? null;
            if ($area && ($check['status'] ?? 'ok') !== 'ok') {
                $fail = $check['status'] === 'fail';
                $out[$area]['penalty'] += self::weight($fail ? 'error' : 'warning', 1);
                $out[$area][$fail ? 'errors' : 'warnings']++;
            }
        }
        foreach ($out as &$row) {
            $row['score'] = max(0, (int) round(100 - $row['penalty']));
            unset($row['penalty']);
        }

        return $out;
    }

    /** Тон за оценка: green, amber, red. */
    public static function tone(?int $score): string
    {
        return $score === null ? 'gray' : ($score >= 80 ? 'green' : ($score >= 50 ? 'amber' : 'red'));
    }

    /**
     * Най-важните неща за оправяне във всички области (само errors и warnings), най-тежките първи.
     *
     * @param  list<array>  $items  от SiteAudit::items()
     */
    public static function top(array $items, int $n = 3): array
    {
        return array_slice(array_values(array_filter($items, fn ($i) => $i['severity'] !== 'notice')), 0, $n);
    }
}
