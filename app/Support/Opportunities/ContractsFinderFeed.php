<?php

namespace App\Support\Opportunities;

use Carbon\CarbonInterface;

/**
 * Великобритания: Contracts Finder (OCDS, без ключ). Не филтрира по CPV или ключова дума, затова всичко от периода
 * се чете и Collector избира подходящото. Параметри: stages=tender, limit до 100, publishedFrom (дата), страниране с links.next.
 */
class ContractsFinderFeed extends OcdsFeed
{
    public const URL = 'https://www.contractsfinder.service.gov.uk/Published/Notices/OCDS/Search';

    public function key(): string
    {
        return 'contracts-finder';
    }

    public function label(): string
    {
        return 'Contracts Finder (Великобритания)';
    }

    protected function firstUrl(CarbonInterface $since): string
    {
        return self::URL . '?' . http_build_query(['stages' => 'tender', 'limit' => 100, 'publishedFrom' => $since->format('Y-m-d')]);
    }

    protected function normalize(array $release): ?array
    {
        $tender = $release['tender'] ?? [];
        $title = trim((string) ($tender['title'] ?? ''));
        if (empty($release['ocid']) || $title === '') {
            return null;
        }

        $url = collect($tender['documents'] ?? [])->firstWhere('documentType', 'tenderNotice')['url'] ?? null;
        $cpv = self::cpvCodes($tender);

        return [
            'external_id' => (string) $release['ocid'],
            'title' => $title,
            'buyer' => trim((string) ($release['buyer']['name'] ?? '')) ?: null,
            'country' => 'GB',
            'language' => 'en',
            'url' => $url,
            'text' => (string) ($tender['description'] ?? ''),
            'cpv' => $cpv,
            'value_min' => $tender['minValue']['amount'] ?? null,
            'value_max' => $tender['value']['amount'] ?? null,
            'currency' => $tender['value']['currency'] ?? ($tender['minValue']['currency'] ?? null),
            'published_at' => self::date($tender['datePublished'] ?? $release['date'] ?? null),
            'deadline_at' => self::date($tender['tenderPeriod']['endDate'] ?? null, true),
            'sme' => isset($tender['suitability']['sme']) ? (bool) $tender['suitability']['sme'] : null,
            'framework' => false,
            'buyer_type' => null,
            'procedure' => $tender['procurementMethodDetails'] ?? null,
            // Само това се пази от изданието: без страни, лица за контакт и описания на документи.
            'raw' => array_filter([
                'ocid' => $release['ocid'], 'release' => $release['id'] ?? null, 'tag' => $release['tag'] ?? null,
                'category' => $tender['mainProcurementCategory'] ?? null, 'procedure' => $tender['procurementMethodDetails'] ?? null,
                'sme' => $tender['suitability']['sme'] ?? null, 'cpv' => $cpv,
            ], fn ($v) => $v !== null),
        ];
    }
}
