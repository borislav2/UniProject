<?php

namespace App\Support\Opportunities;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * ЕС: TED (Tenders Electronic Daily), POST https://api.ted.europa.eu/v3/notices/search, без ключ.
 * Заявката е на езика за търсене на TED: само нашите CPV кодове, само обяви за търг (cn-standard, cn-social, cn-desg,
 * pin-cfc-*), публикувани в периода и със срок за оферти от днес нататък. Резултатите са по полета: повечето са списъци
 * по лотове, имената са по езици. Известията с резултати от търгове (can-*, veat) не се искат изобщо.
 *
 * Особености: limit най-много 250; грешно поле връща 400 със списък на допустимите; срокът е в две полета
 * (deadline с час, deadline-receipt-tender-date-lot само дата), затова се искат и двете; държавите и езиците са ISO3.
 */
class TedFeed implements Feed
{
    public const URL = 'https://api.ted.europa.eu/v3/notices/search';

    public const LIMIT = 250;

    private const NOTICE_TYPES = 'cn-standard cn-social cn-desg pin-cfc-standard pin-cfc-social';

    private const FIELDS = [
        'publication-number', 'notice-type', 'procedure-type', 'title-proc', 'description-proc', 'description-lot', 'classification-cpv',
        'buyer-name', 'buyer-country', 'buyer-legal-type', 'publication-date', 'deadline', 'deadline-receipt-tender-date-lot',
        'estimated-value-proc', 'estimated-value-cur-proc', 'estimated-value-lot', 'estimated-value-cur-lot',
        'framework-agreement-lot', 'submission-language', 'sme-lot',
    ];

    public function key(): string
    {
        return 'ted';
    }

    public function label(): string
    {
        return 'TED (ЕС)';
    }

    public function query(CarbonInterface $since): string
    {
        $cpv = implode(' ', array_keys(config('creatium.opportunities.cpv')));
        $from = $since->format('Ymd');
        $today = now()->format('Ymd');

        return "classification-cpv IN ({$cpv}) AND publication-date>={$from} AND notice-type IN (" . self::NOTICE_TYPES . ") AND (deadline-receipt-tender-date-lot>={$today} OR deadline>={$today})";
    }

    public function fetch(CarbonInterface $since, int $maxPages): array
    {
        $returned = 0;
        $records = [];
        for ($page = 1; $page <= max(1, $maxPages); $page++) {
            $response = Http::withHeaders(['User-Agent' => config('creatium.opportunities.user_agent')])
                ->timeout((int) config('creatium.opportunities.timeout'))->acceptJson()->asJson()
                ->post(self::URL, ['query' => $this->query($since), 'fields' => self::FIELDS, 'limit' => self::LIMIT, 'page' => $page]);
            if (! $response->successful()) {
                throw new RuntimeException('TED: отговор ' . $response->status() . ' ' . mb_substr((string) $response->json('message'), 0, 160));
            }

            $notices = $response->json('notices');
            if (! is_array($notices)) {
                throw new RuntimeException('TED: неочакван отговор');
            }
            $returned += count($notices);
            foreach ($notices as $notice) {
                if ($record = $this->normalize($notice)) {
                    $records[$record['external_id']] = $record;
                }
            }
            if (count($notices) < self::LIMIT) {
                break;
            }
        }

        return ['returned' => $returned, 'records' => array_values($records)];
    }

    /** @return array<string, mixed>|null */
    public function normalize(array $n): ?array
    {
        $number = $n['publication-number'] ?? null;
        [$titleLang, $title] = self::pick($n['title-proc'] ?? null);
        if (! $number || ! $title) {
            return null;
        }
        [, $description] = self::pick($n['description-proc'] ?? null);
        if (! $description) {
            [, $description] = self::pick($n['description-lot'] ?? null);
        }

        $country = Countries::iso2(self::first($n['buyer-country'] ?? null));
        $language = Countries::language(self::first($n['submission-language'] ?? null)) ?? Countries::language($titleLang);
        [, $buyer] = self::pick($n['buyer-name'] ?? null);
        $cpv = array_values(array_unique(array_map('strval', (array) ($n['classification-cpv'] ?? []))));

        // Стойност: за цялата процедура, иначе сборът по лотове (всеки лот е на отделен ред).
        $value = self::number($n['estimated-value-proc'] ?? null);
        if ($value === null && ! empty($n['estimated-value-lot'])) {
            $lots = array_filter(array_map([self::class, 'number'], (array) $n['estimated-value-lot']), fn ($v) => $v !== null);
            $value = $lots ? array_sum($lots) : null;
        }
        $currency = ($n['estimated-value-cur-proc'] ?? null) ?: self::first($n['estimated-value-cur-lot'] ?? null);

        $sme = array_filter((array) ($n['sme-lot'] ?? []), fn ($v) => is_bool($v));
        $framework = collect((array) ($n['framework-agreement-lot'] ?? []))->contains(fn ($v) => $v !== 'none');
        $buyerType = self::first($n['buyer-legal-type'] ?? null);

        return [
            'external_id' => (string) $number,
            'title' => $title,
            'buyer' => $buyer,
            'country' => $country,
            'language' => $language,
            'url' => 'https://ted.europa.eu/en/notice/-/detail/' . $number,
            'text' => (string) $description,
            'cpv' => $cpv,
            'value_min' => null,
            'value_max' => $value,
            'currency' => $currency ?: null,
            'published_at' => self::date($n['publication-date'] ?? null),
            'deadline_at' => self::deadline($n),
            'sme' => $sme ? in_array(true, $sme, true) : null,
            'framework' => $framework,
            'buyer_type' => $buyerType,
            'procedure' => $n['procedure-type'] ?? null,
            'raw' => array_filter([
                'publication_number' => $number, 'notice_type' => $n['notice-type'] ?? null, 'procedure' => $n['procedure-type'] ?? null,
                'buyer_type' => $buyerType, 'framework' => $framework, 'lots' => count((array) ($n['deadline-receipt-tender-date-lot'] ?? [])) ?: null,
                'title_language' => $titleLang, 'cpv' => $cpv,
            ], fn ($v) => $v !== null && $v !== false),
        ];
    }

    /**
     * Срокът за оферти. Идва в две полета (час и/или дата), по един запис на лот. Взима се най-ранният срок, който още не е минал;
     * ако всички са минали, най-късният (известието ще се отхвърли като изтекло).
     */
    private static function deadline(array $n): ?CarbonInterface
    {
        $dates = [];
        foreach (['deadline', 'deadline-receipt-tender-date-lot'] as $field) {
            foreach ((array) ($n[$field] ?? []) as $value) {
                if ($date = self::date($value, $field !== 'deadline')) {
                    $dates[] = $date;
                }
            }
        }
        if (! $dates) {
            return null;
        }
        usort($dates, fn ($a, $b) => $a <=> $b);
        $future = array_values(array_filter($dates, fn ($d) => $d->greaterThan(now())));

        return $future[0] ?? end($dates);
    }

    /** Дата като 2026-11-23+01:00, 2026-12-07Z или пълен час. Дата без час значи до края на деня в нейната зона. */
    private static function date(mixed $value, bool $endOfDay = false): ?CarbonInterface
    {
        if (! is_string($value) || $value === '') {
            return null;
        }
        try {
            $dateOnly = (bool) preg_match('/^\d{4}-\d{2}-\d{2}(?:Z|[+-]\d{2}:\d{2})?$/', $value);
            $date = Carbon::parse($value);

            return ($dateOnly && $endOfDay ? $date->endOfDay() : $date)->utc();
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * От поле по езици ({"eng": "...", "fra": ["..."]}) взима текста: английски, после езика на купувача, после първия.
     *
     * @return array{0: ?string, 1: ?string} [ISO3 на езика, текст]
     */
    private static function pick(mixed $byLanguage): array
    {
        if (! is_array($byLanguage) || ! $byLanguage) {
            return [null, null];
        }
        foreach (['eng', 'bul', 'deu', 'nld'] as $preferred) {
            if (isset($byLanguage[$preferred])) {
                $text = self::text($byLanguage[$preferred]);
                if ($text !== null) {
                    return [$preferred, $text];
                }
            }
        }
        foreach ($byLanguage as $lang => $value) {
            if (($text = self::text($value)) !== null) {
                return [(string) $lang, $text];
            }
        }

        return [null, null];
    }

    private static function text(mixed $value): ?string
    {
        $value = is_array($value) ? implode(' ', array_map('strval', array_unique($value))) : (string) $value;
        $value = trim(preg_replace('/\s+/u', ' ', $value));

        return $value === '' ? null : $value;
    }

    private static function first(mixed $value): ?string
    {
        $value = is_array($value) ? ($value[0] ?? null) : $value;

        return $value === null || $value === '' ? null : (string) $value;
    }

    private static function number(mixed $value): ?float
    {
        return is_numeric($value) && (float) $value > 0 ? (float) $value : null;
    }
}
