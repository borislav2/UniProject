<?php

namespace App\Support\Opportunities;

use Carbon\CarbonInterface;

/**
 * Великобритания: Find a Tender (OCDS, без ключ), по-големите поръчки (и шотландските от PCS). Параметри: stages=tender,
 * limit до 100, updatedFrom (дата и час без зона), страниране с links.next. Отговорът е по-тежък (около 15 KB на известие).
 */
class FindATenderFeed extends OcdsFeed
{
    public const URL = 'https://www.find-tender.service.gov.uk/api/1.0/ocdsReleasePackages';

    public function key(): string
    {
        return 'find-a-tender';
    }

    public function label(): string
    {
        return 'Find a Tender (Великобритания)';
    }

    protected function firstUrl(CarbonInterface $since): string
    {
        return self::URL . '?' . http_build_query(['stages' => 'tender', 'limit' => 100, 'updatedFrom' => $since->copy()->utc()->format('Y-m-d\TH:i:s')]);
    }

    protected function normalize(array $release): ?array
    {
        $tender = $release['tender'] ?? [];
        $title = trim((string) ($tender['title'] ?? ''));
        if (empty($release['ocid']) || $title === '') {
            return null;
        }

        $noticeId = $release['id'] ?? null;
        $cpv = self::cpvCodes($tender);
        $buyerParty = collect($release['parties'] ?? [])->first(fn ($p) => in_array('buyer', $p['roles'] ?? [], true));
        $buyerType = collect($buyerParty['details']['classifications'] ?? [])->firstWhere('scheme', 'TED_CA_TYPE')['id'] ?? null;
        $country = Countries::iso2FromName($buyerParty['address']['countryName'] ?? null) ?? 'GB';
        $text = trim(($tender['description'] ?? '') . ' ' . self::lotText($tender));

        return [
            'external_id' => (string) $release['ocid'],
            'title' => $title,
            'buyer' => trim((string) ($release['buyer']['name'] ?? '')) ?: null,
            'country' => $country,
            'language' => 'en',
            'url' => $noticeId ? 'https://www.find-tender.service.gov.uk/Notice/' . $noticeId : null,
            'text' => $text,
            'cpv' => $cpv,
            'value_min' => $tender['minValue']['amount'] ?? null,
            'value_max' => $tender['value']['amount'] ?? null,
            'currency' => $tender['value']['currency'] ?? ($tender['minValue']['currency'] ?? null),
            'published_at' => self::date($release['date'] ?? null),
            'deadline_at' => self::date($tender['tenderPeriod']['endDate'] ?? null, true),
            'sme' => null,
            'framework' => (bool) ($tender['techniques']['hasFrameworkAgreement'] ?? false),
            'buyer_type' => $buyerType,
            'procedure' => $tender['procurementMethodDetails'] ?? null,
            'raw' => array_filter([
                'ocid' => $release['ocid'], 'notice' => $noticeId, 'tag' => $release['tag'] ?? null, 'category' => $tender['mainProcurementCategory'] ?? null,
                'procedure' => $tender['procurementMethodDetails'] ?? null, 'framework' => $tender['techniques']['hasFrameworkAgreement'] ?? null,
                'buyer_type' => $buyerType, 'lots' => count($tender['lots'] ?? []) ?: null, 'cpv' => $cpv,
            ], fn ($v) => $v !== null),
        ];
    }
}
