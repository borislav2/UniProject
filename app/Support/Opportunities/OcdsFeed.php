<?php

namespace App\Support\Opportunities;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Общото за двата британски източника (Contracts Finder и Find a Tender): OCDS пакети с „releases“, страниране с links.next.
 * Особеност: Find a Tender връща невалиден JSON със стойности Infinity (например maximumLotsBidPerSupplier), затова
 * тялото се чисти преди json_decode. Нищо от контактните данни на купувача (parties.contactPoint) не се копира.
 */
abstract class OcdsFeed implements Feed
{
    abstract protected function firstUrl(CarbonInterface $since): string;

    /** @return array<string, mixed>|null Нормализиран запис или null, ако изданието не е за нас (без идентификатор или заглавие). */
    abstract protected function normalize(array $release): ?array;

    public function fetch(CarbonInterface $since, int $maxPages): array
    {
        $url = $this->firstUrl($since);
        $returned = 0;
        $byId = [];

        for ($page = 1; $page <= max(1, $maxPages) && $url; $page++) {
            $package = $this->get($url);
            $releases = $package['releases'] ?? [];
            $returned += count($releases);
            foreach ($releases as $release) {
                $record = $this->normalize($release);
                if (! $record) {
                    continue;
                }
                // Поправките са отделни издания със същия ocid: остава най-новото.
                $date = (string) ($release['date'] ?? '');
                if (! isset($byId[$record['external_id']]) || $date >= $byId[$record['external_id']][0]) {
                    $byId[$record['external_id']] = [$date, $record];
                }
            }
            $url = $package['links']['next'] ?? null;
            if (! $releases) {
                break;
            }
        }

        return ['returned' => $returned, 'records' => array_values(array_map(fn ($pair) => $pair[1], $byId))];
    }

    /** @return array<string, mixed> */
    protected function get(string $url): array
    {
        $response = Http::withHeaders(['User-Agent' => config('creatium.opportunities.user_agent')])
            ->timeout((int) config('creatium.opportunities.timeout'))->acceptJson()->get($url);
        if (! $response->successful()) {
            throw new RuntimeException($this->label() . ': отговор ' . $response->status());
        }

        $body = preg_replace('/(?<=[:\[,])(\s*)-?(?:Infinity|NaN)(?=\s*[,}\]])/', '$1null', $response->body());
        $data = json_decode($body, true);
        if (! is_array($data)) {
            throw new RuntimeException($this->label() . ': отговорът не е валиден JSON');
        }

        return $data;
    }

    /** Дата от OCDS (ISO 8601 със или без час и зона) като UTC. Дата без час значи до края на деня. */
    protected static function date(mixed $value, bool $endOfDay = false): ?CarbonInterface
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }
        try {
            $dateOnly = (bool) preg_match('/^\d{4}-\d{2}-\d{2}$/', trim($value));
            $date = Carbon::parse($value, $dateOnly ? 'Europe/London' : null);

            return ($dateOnly && $endOfDay ? $date->endOfDay() : $date)->utc();
        } catch (\Throwable) {
            return null;
        }
    }

    /** CPV кодовете на изданието: основният, допълнителните и тези на лотовете. @return list<string> */
    protected static function cpvCodes(array $tender): array
    {
        $codes = [];
        $collect = function (?array $c) use (&$codes) {
            if ($c && strtoupper((string) ($c['scheme'] ?? '')) === 'CPV' && ! empty($c['id'])) {
                $codes[] = (string) $c['id'];
            }
        };
        $collect($tender['classification'] ?? null);
        foreach ($tender['additionalClassifications'] ?? [] as $c) {
            $collect($c);
        }
        foreach ($tender['lots'] ?? [] as $lot) {
            foreach ($lot['additionalClassifications'] ?? [] as $c) {
                $collect($c);
            }
        }

        return array_values(array_unique($codes));
    }

    /** Заглавията и описанията на лотовете, за търсене на ключови думи. */
    protected static function lotText(array $tender): string
    {
        return collect($tender['lots'] ?? [])->map(fn ($lot) => trim(($lot['title'] ?? '') . ' ' . ($lot['description'] ?? '')))->filter()->implode(' ');
    }
}
