<?php

namespace App\Support\Opportunities;

use App\Models\OutreachSetting;

/**
 * Подготвените търсения в платформи за свободна практика и краткият списък с програми и грантове: само връзки,
 * които се отварят в нов раздел (Upwork, Malt, Fiverr, freelancermap и подобни забраняват автоматично събиране, затова не се събира нищо).
 * Списъкът се пази в outreach_settings, а creatium.opportunities са стойностите по подразбиране.
 */
class OpportunityLinks
{
    private const KEYS = ['searches' => 'opportunity_searches', 'grants' => 'opportunity_grants'];

    /** @return list<array{label: string, url: string}> */
    public static function get(string $which): array
    {
        $default = config('creatium.opportunities.' . ($which === 'grants' ? 'grant_links' : 'saved_searches'));

        return self::clean(OutreachSetting::get(self::KEYS[$which], $default));
    }

    /** @param list<array{label?: mixed, url?: mixed}> $links */
    public static function save(string $which, array $links): void
    {
        OutreachSetting::put(self::KEYS[$which], self::clean($links));
    }

    /** Текст „Име | адрес“ по един на ред. @return list<array{label: string, url: string}> */
    public static function parse(string $lines): array
    {
        $links = [];
        foreach (preg_split('/\R/u', $lines) as $line) {
            [$label, $url] = array_pad(array_map('trim', explode('|', $line, 2)), 2, '');
            if ($url === '' && preg_match('#^https?://#i', $label)) {
                [$label, $url] = [parse_url($label, PHP_URL_HOST) ?: $label, $label];
            }
            $links[] = ['label' => $label, 'url' => $url];
        }

        return self::clean($links);
    }

    public static function toLines(array $links): string
    {
        return collect($links)->map(fn ($l) => $l['label'] . ' | ' . $l['url'])->implode("\n");
    }

    /** Само http и https адреси, с име, без повторения. @return list<array{label: string, url: string}> */
    private static function clean(mixed $links): array
    {
        $seen = [];
        $out = [];
        foreach ((array) $links as $link) {
            $label = trim((string) ($link['label'] ?? ''));
            $url = trim((string) ($link['url'] ?? ''));
            if ($label === '' || ! preg_match('#^https?://[^\s]+$#i', $url) || isset($seen[$url])) {
                continue;
            }
            $seen[$url] = true;
            $out[] = ['label' => mb_substr($label, 0, 80), 'url' => mb_substr($url, 0, 500)];
        }

        return $out;
    }
}
