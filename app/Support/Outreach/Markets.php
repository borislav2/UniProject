<?php

namespace App\Support\Outreach;

use App\Models\OutreachProspect;

/**
 * Пазарите, на които търсим клиенти, и правилата по канали. Единственият източник за градове, езика на търсенето
 * и писмото и за това дали на бизнес може да се пише по имейл.
 *
 * ВАЖНО: правилата за канала са НАШЕТО ПРОЧИТАНЕ на законите, не правна консултация. Преди да започнем да пишем
 * в нов пазар, ги проверете с юрист (за Великобритания: PECR, за Нидерландия: Telecommunicatiewet чл. 11.7,
 * за Ирландия: S.I. 336/2011, за България: Закон за електронната търговия и GDPR). Правилата са в emailRule().
 */
class Markets
{
    public const DEFAULT = 'bg';

    /**
     * label: името в админа; language: на какъв език е писмото; phone_prefix: кодът на държавата;
     * serper: gl и hl за Google Maps; cities: име => [ширина, дължина, радиус в км] (средни градове, не цели агломерации).
     */
    public const MARKETS = [
        'bg' => [
            'label' => 'България',
            'language' => 'bg',
            'phone_prefix' => '+359',
            'serper' => ['gl' => 'bg', 'hl' => 'bg'],
            'cities' => BusinessSearch::CITIES,
            'rule' => 'Имейл е разрешен до публикуваните адреси на бизнеса.',
        ],
        'gb' => [
            'label' => 'Обединено кралство',
            'language' => 'en',
            'phone_prefix' => '+44',
            'serper' => ['gl' => 'gb', 'hl' => 'en'],
            'cities' => [
                'Manchester' => [53.4808, -2.2426, 8],
                'Leeds' => [53.8008, -1.5491, 8],
                'Bristol' => [51.4545, -2.5879, 8],
                'Liverpool' => [53.4084, -2.9916, 7],
                'Sheffield' => [53.3811, -1.4701, 8],
                'Nottingham' => [52.9548, -1.1581, 7],
                'Leicester' => [52.6369, -1.1398, 7],
                'Brighton' => [50.8225, -0.1372, 5],
                'Cardiff' => [51.4816, -3.1791, 8],
                'Edinburgh' => [55.9533, -3.1883, 8],
                'Glasgow' => [55.8642, -4.2518, 8],
                'Belfast' => [54.5973, -5.9301, 7],
            ],
            'rule' => 'Имейл само до фирми (Ltd, LLP, PLC), когато сайтът показва регистрация. Иначе обаждане или LinkedIn.',
        ],
        'ie' => [
            'label' => 'Ирландия',
            'language' => 'en',
            'phone_prefix' => '+353',
            'serper' => ['gl' => 'ie', 'hl' => 'en'],
            'cities' => [
                'Dublin' => [53.3498, -6.2603, 10],
                'Cork' => [51.8985, -8.4756, 6],
                'Galway' => [53.2707, -9.0568, 5],
                'Limerick' => [52.6638, -8.6267, 5],
                'Waterford' => [52.2593, -7.1101, 4],
            ],
            'rule' => 'Имейл е разрешен до бизнес адреси.',
        ],
        'nl' => [
            'label' => 'Нидерландия',
            'language' => 'en',
            'phone_prefix' => '+31',
            'serper' => ['gl' => 'nl', 'hl' => 'nl'],
            'cities' => [
                'Amsterdam' => [52.3676, 4.9041, 8],
                'Rotterdam' => [51.9244, 4.4777, 8],
                'Utrecht' => [52.0907, 5.1214, 6],
                'Den Haag' => [52.0705, 4.3007, 7],
                'Eindhoven' => [51.4416, 5.4697, 6],
                'Groningen' => [53.2194, 6.5665, 5],
            ],
            'rule' => 'Без имейл (нужно е съгласие). Само обаждане или LinkedIn.',
        ],
    ];

    /** Думата за търсене в Google Maps по вид бизнес на езика на пазара (за България е в BusinessSearch::CATEGORIES). */
    private const QUERIES = [
        'en' => [
            'restaurant' => 'restaurant', 'cafe' => 'cafe', 'hotel' => 'hotel', 'beauty' => 'beauty salon', 'hairdresser' => 'hairdresser',
            'dentist' => 'dentist', 'physiotherapy' => 'physiotherapist', 'veterinary' => 'vet clinic', 'car_repair' => 'car garage',
            'fitness' => 'gym', 'builder' => 'builder', 'lawyer' => 'solicitor', 'accountant' => 'accountant',
        ],
        'nl' => [
            'restaurant' => 'restaurant', 'cafe' => 'café', 'hotel' => 'hotel', 'beauty' => 'schoonheidssalon', 'hairdresser' => 'kapper',
            'dentist' => 'tandarts', 'physiotherapy' => 'fysiotherapeut', 'veterinary' => 'dierenarts', 'car_repair' => 'autogarage',
            'fitness' => 'sportschool', 'builder' => 'aannemer', 'lawyer' => 'advocaat', 'accountant' => 'boekhouder',
        ],
    ];

    /** @return list<string> */
    public static function codes(): array
    {
        return array_keys(self::MARKETS);
    }

    public static function exists(?string $code): bool
    {
        return $code !== null && isset(self::MARKETS[$code]);
    }

    /** Непознат или празен код е България (така са всички записи отпреди пазарите). */
    public static function normalize(?string $code): string
    {
        return self::exists($code) ? $code : self::DEFAULT;
    }

    public static function label(?string $code): string
    {
        return self::MARKETS[self::normalize($code)]['label'];
    }

    /** @return array<string, array{0: float, 1: float, 2: int}> */
    public static function cities(?string $code): array
    {
        return self::MARKETS[self::normalize($code)]['cities'];
    }

    /** @return array{gl: string, hl: string} */
    public static function serper(?string $code): array
    {
        return self::MARKETS[self::normalize($code)]['serper'];
    }

    /** Езикът на писмото и на търсенето: bg или en. */
    public static function language(?string $code): string
    {
        return self::MARKETS[self::normalize($code)]['language'];
    }

    public static function phonePrefix(?string $code): string
    {
        return self::MARKETS[self::normalize($code)]['phone_prefix'];
    }

    public static function priority(?string $code): int
    {
        return (int) (config('creatium.outreach.scoring.market')[self::normalize($code)] ?? 0);
    }

    /** Думата за търсене в Google Maps за този пазар и вид бизнес (null = непознат вид). */
    public static function query(?string $code, string $category): ?string
    {
        $code = self::normalize($code);
        if ($code === 'bg') {
            return BusinessSearch::CATEGORIES[$category]['query'] ?? null;
        }

        return self::QUERIES[$code === 'nl' ? 'nl' : 'en'][$category] ?? null;
    }

    public static function rule(?string $code): string
    {
        return self::MARKETS[self::normalize($code)]['rule'];
    }

    /**
     * Може ли на този бизнес да се пише по имейл. Връща [да или не, причина на български, когато е не].
     * Тук е нашето прочитане на правилата по пазари (не е правна консултация):
     * - bg: да, както досега.
     * - ie: да, до бизнес адреси.
     * - gb: студено писмо е позволено само до корпоративни абонати (PECR), не до еднолични търговци и партньорства.
     *   Затова само ако сайтът показва, че бизнесът е фирма (Ltd, Limited, LLP, PLC или регистрационен номер).
     * - nl: писмо до бизнес без съгласие не е позволено (Telecommunicatiewet чл. 11.7), никога по имейл.
     *
     * @return array{0: bool, 1: ?string}
     */
    public static function emailRule(OutreachProspect $prospect): array
    {
        $code = $prospect->country ?: self::DEFAULT;

        return match ($code) {
            'bg', 'ie' => [true, null],
            'gb' => filled($prospect->company_evidence)
                ? [true, null]
                : [false, 'Във Великобритания пишем по имейл само на фирми (Ltd, LLP, PLC). На сайта им не виждаме данни, че бизнесът е регистрирана фирма, затова нищо не се праща по имейл. Обадете се или пишете в LinkedIn.'],
            'nl' => [false, 'В Нидерландия писмо до бизнес без предварително съгласие не е разрешено (Telecommunicatiewet, чл. 11.7), затова нищо не се праща по имейл. Обадете се или пишете в LinkedIn.'],
            default => [false, 'Непознат пазар (' . $code . '): по имейл не се пише.'],
        };
    }

    public static function emailAllowed(OutreachProspect $prospect): bool
    {
        return self::emailRule($prospect)[0];
    }

    /** Защо не може да се пише по имейл (български текст), или null, ако може. */
    public static function emailReason(OutreachProspect $prospect): ?string
    {
        return self::emailRule($prospect)[1];
    }
}
