<?php

namespace App\Support\Opportunities;

use Carbon\CarbonInterface;

/**
 * Един източник на обществени поръчки. fetch() прави най-много $maxPages заявки и връща известията, нормализирани
 * към един вид запис (виж ключовете в NormalizedNotice). Греши с изключение: Collector го хваща и го записва.
 */
interface Feed
{
    /** Ключът на източника в opportunities.source. */
    public function key(): string;

    public function label(): string;

    /**
     * @return array{returned: int, records: list<array<string, mixed>>}
     *   returned = колко известия е върнал източникът; records = нормализираните записи.
     *   Запис: external_id, title, buyer, country (ISO2), language (ISO2), url, text, cpv (list), value_min, value_max, currency,
     *   published_at, deadline_at (CarbonInterface|null), sme (bool|null), framework (bool), buyer_type (?string), procedure (?string), raw (array)
     */
    public function fetch(CarbonInterface $since, int $maxPages): array;
}
