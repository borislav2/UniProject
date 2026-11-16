<?php

namespace App\Support\Seo;

use App\Models\SeoKeyword;
use App\Models\SeoKeywordPosition;

/**
 * Внасяне на позиции от Search Console („Заявки“ / „Queries“, експорт в CSV или поставен от таблица): заявка, кликвания,
 * показвания, CTR, позиция. Разделител запетая, точка и запетая или табулация; заглавия на български или английски;
 * десетични числа с точка или запетая. Няма външни заявки: само текстът, който поставите.
 */
class KeywordImport
{
    private const HEADERS = [
        'query' => ['query', 'queries', 'top queries', 'search query', 'search queries', 'заявка', 'заявки', 'топ заявки', 'топ запитвания', 'запитване', 'запитвания', 'ключова дума', 'keyword'],
        'clicks' => ['clicks', 'click', 'кликвания', 'кликове', 'кликване'],
        'impressions' => ['impressions', 'impression', 'показвания', 'импресии'],
        'ctr' => ['ctr', 'честота на кликване', 'честота на кликванията'],
        'position' => ['position', 'average position', 'avg. position', 'avg position', 'позиция', 'средна позиция'],
    ];

    /** @return array{rows: list<array{query: string, clicks: ?int, impressions: ?int, position: ?float}>, skipped: int} */
    public static function parse(string $text): array
    {
        $text = ltrim(str_replace(["\r\n", "\r"], "\n", $text), "\xEF\xBB\xBF");
        $lines = array_values(array_filter(array_map('rtrim', explode("\n", $text)), fn ($l) => trim($l) !== ''));
        if (! $lines) {
            return ['rows' => [], 'skipped' => 0];
        }

        $delimiter = self::delimiter($lines[0]);
        $first = self::cells($lines[0], $delimiter);
        $map = self::mapHeader($first);
        if ($map) {
            array_shift($lines);
        } else {
            // Без познати заглавия: колоните са заявка, кликвания, показвания, CTR, позиция (редът в Search Console).
            $map = ['query' => 0, 'clicks' => 1, 'impressions' => 2, 'ctr' => 3, 'position' => 4];
            if (count($first) === 2 && self::number($first[1], $delimiter) !== null) {
                $map = ['query' => 0, 'position' => 1];
            }
        }

        $rows = [];
        $skipped = 0;
        foreach ($lines as $line) {
            $cells = self::cells($line, $delimiter);
            $query = trim((string) ($cells[$map['query']] ?? ''));
            $position = isset($map['position']) ? self::number($cells[$map['position']] ?? '', $delimiter) : null;
            if ($query === '' || ($position === null && ! isset($map['clicks']) && ! isset($map['impressions']))) {
                $skipped++;

                continue;
            }
            $clicks = isset($map['clicks']) ? self::number($cells[$map['clicks']] ?? '', $delimiter) : null;
            $impressions = isset($map['impressions']) ? self::number($cells[$map['impressions']] ?? '', $delimiter) : null;
            if ($position === null && $clicks === null && $impressions === null) {
                $skipped++;

                continue;
            }
            $rows[] = ['query' => $query, 'clicks' => $clicks === null ? null : (int) round($clicks), 'impressions' => $impressions === null ? null : (int) round($impressions), 'position' => $position];
        }

        return ['rows' => $rows, 'skipped' => $skipped];
    }

    private static function delimiter(string $header): string
    {
        $counts = [';' => substr_count($header, ';'), "\t" => substr_count($header, "\t"), ',' => substr_count($header, ',')];
        arsort($counts);
        $best = array_key_first($counts);

        return $counts[$best] > 0 ? $best : ',';
    }

    private static function cells(string $line, string $delimiter): array
    {
        return array_map(fn ($c) => trim((string) $c), str_getcsv($line, $delimiter, '"', ''));
    }

    private static function mapHeader(array $cells): ?array
    {
        $map = [];
        foreach ($cells as $i => $cell) {
            $name = mb_strtolower(trim($cell, " \t\"'"));
            foreach (self::HEADERS as $field => $names) {
                if (! isset($map[$field]) && in_array($name, $names, true)) {
                    $map[$field] = $i;
                }
            }
        }

        return isset($map['query']) ? $map : null;
    }

    /** „1 234“, „1,234“, „12,5“, „12.5“, „12,5 %“, „—“ => число или null. */
    public static function number(string $value, string $delimiter = ','): ?float
    {
        $v = trim(str_replace(["\u{00A0}", "\u{202F}", ' ', '%'], '', $value));
        if ($v === '' || $v === '-' || $v === '—' || $v === '–') {
            return null;
        }
        if (str_contains($v, ',') && str_contains($v, '.')) {
            // И двете: последният е десетичен знак, другият разделя хилядите.
            $decimal = strrpos($v, ',') > strrpos($v, '.') ? ',' : '.';
            $v = $decimal === ',' ? str_replace('.', '', $v) : str_replace(',', '', $v);
            $v = str_replace(',', '.', $v);
        } elseif (str_contains($v, ',')) {
            $thousands = $delimiter !== ';' && preg_match('/^\d{1,3}(,\d{3})+$/', $v);
            $v = $thousands ? str_replace(',', '', $v) : str_replace(',', '.', $v);
        } elseif (preg_match('/^\d{1,3}(\.\d{3}){2,}$/', $v)) {
            $v = str_replace('.', '', $v);
        }

        return is_numeric($v) ? (float) $v : null;
    }

    /**
     * Записва позициите за $date при думите, които вече следим (по език и държава). Непознатите се пропускат или (по желание) добавят.
     *
     * @return array{updated: int, created: int, unknown: list<string>, skipped: int}
     */
    public static function apply(array $parsed, string $locale, string $country, \DateTimeInterface|string $date, bool $addUnknown = false): array
    {
        $known = SeoKeyword::where('locale', $locale)->where('country', $country)->get()->keyBy(fn ($k) => SeoKeyword::normalize($k->keyword));
        $updated = $created = 0;
        $unknown = [];
        foreach ($parsed['rows'] as $row) {
            $key = SeoKeyword::normalize($row['query']);
            $keyword = $known[$key] ?? null;
            if (! $keyword) {
                if (! $addUnknown || $created >= 50) {
                    $unknown[] = $row['query'];

                    continue;
                }
                $keyword = SeoKeyword::create(['keyword' => mb_substr(trim($row['query']), 0, 200), 'locale' => $locale, 'country' => $country, 'priority' => 'low']);
                $known[$key] = $keyword;
                $created++;
            }
            SeoKeywordPosition::updateOrCreate(
                ['seo_keyword_id' => $keyword->id, 'date' => $date instanceof \DateTimeInterface ? $date->format('Y-m-d') : $date],
                ['position' => $row['position'], 'impressions' => $row['impressions'], 'clicks' => $row['clicks']],
            );
            $updated++;
        }

        return ['updated' => $updated, 'created' => $created, 'unknown' => $unknown, 'skipped' => $parsed['skipped']];
    }
}
