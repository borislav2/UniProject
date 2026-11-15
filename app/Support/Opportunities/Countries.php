<?php

namespace App\Support\Opportunities;

/** Държави и езици: кодовете от източниците (ISO 3166 alpha 3 в TED, alpha 2 другаде) и имената на български. */
class Countries
{
    /** ISO3 => [ISO2, име на български] */
    private const LIST = [
        'BGR' => ['BG', 'България'], 'GBR' => ['GB', 'Обединено кралство'], 'IRL' => ['IE', 'Ирландия'], 'NLD' => ['NL', 'Нидерландия'],
        'DEU' => ['DE', 'Германия'], 'AUT' => ['AT', 'Австрия'], 'CHE' => ['CH', 'Швейцария'], 'BEL' => ['BE', 'Белгия'],
        'LUX' => ['LU', 'Люксембург'], 'FRA' => ['FR', 'Франция'], 'ESP' => ['ES', 'Испания'], 'ITA' => ['IT', 'Италия'],
        'PRT' => ['PT', 'Португалия'], 'GRC' => ['GR', 'Гърция'], 'CYP' => ['CY', 'Кипър'], 'MLT' => ['MT', 'Малта'],
        'DNK' => ['DK', 'Дания'], 'SWE' => ['SE', 'Швеция'], 'FIN' => ['FI', 'Финландия'], 'EST' => ['EE', 'Естония'],
        'LVA' => ['LV', 'Латвия'], 'LTU' => ['LT', 'Литва'], 'POL' => ['PL', 'Полша'], 'CZE' => ['CZ', 'Чехия'],
        'SVK' => ['SK', 'Словакия'], 'SVN' => ['SI', 'Словения'], 'HRV' => ['HR', 'Хърватия'], 'HUN' => ['HU', 'Унгария'],
        'ROU' => ['RO', 'Румъния'], 'NOR' => ['NO', 'Норвегия'], 'ISL' => ['IS', 'Исландия'], 'LIE' => ['LI', 'Лихтенщайн'],
        'MKD' => ['MK', 'Северна Македония'], 'SRB' => ['RS', 'Сърбия'], 'ALB' => ['AL', 'Албания'], 'MNE' => ['ME', 'Черна гора'],
        'BIH' => ['BA', 'Босна и Херцеговина'], 'TUR' => ['TR', 'Турция'], 'UKR' => ['UA', 'Украйна'], 'MDA' => ['MD', 'Молдова'],
        'USA' => ['US', 'САЩ'], 'CAN' => ['CA', 'Канада'], 'AUS' => ['AU', 'Австралия'], 'ARE' => ['AE', 'ОАЕ'],
    ];

    /** ISO3 на език => ISO2 */
    private const LANGUAGES = [
        'eng' => 'en', 'bul' => 'bg', 'deu' => 'de', 'nld' => 'nl', 'fra' => 'fr', 'spa' => 'es', 'ita' => 'it', 'por' => 'pt',
        'pol' => 'pl', 'swe' => 'sv', 'fin' => 'fi', 'dan' => 'da', 'ces' => 'cs', 'slk' => 'sk', 'slv' => 'sl', 'hrv' => 'hr',
        'hun' => 'hu', 'ron' => 'ro', 'ell' => 'el', 'est' => 'et', 'lav' => 'lv', 'lit' => 'lt', 'mlt' => 'mt', 'gle' => 'ga',
        'nor' => 'no', 'nob' => 'no', 'isl' => 'is', 'mkd' => 'mk', 'srp' => 'sr', 'tur' => 'tr', 'ukr' => 'uk',
    ];

    private const LANGUAGE_NAMES = [
        'en' => 'английски', 'bg' => 'български', 'de' => 'немски', 'nl' => 'нидерландски', 'fr' => 'френски', 'es' => 'испански',
        'it' => 'италиански', 'pt' => 'португалски', 'pl' => 'полски', 'sv' => 'шведски', 'fi' => 'фински', 'da' => 'датски',
        'cs' => 'чешки', 'sk' => 'словашки', 'sl' => 'словенски', 'hr' => 'хърватски', 'hu' => 'унгарски', 'ro' => 'румънски',
        'el' => 'гръцки', 'et' => 'естонски', 'lv' => 'латвийски', 'lt' => 'литовски', 'mt' => 'малтийски', 'ga' => 'ирландски',
        'no' => 'норвежки', 'is' => 'исландски', 'mk' => 'македонски', 'sr' => 'сръбски', 'tr' => 'турски', 'uk' => 'украински',
    ];

    /** Двубуквен код от ISO3 или ISO2 (непознат => null). */
    public static function iso2(?string $code): ?string
    {
        $code = strtoupper(trim((string) $code));
        if (strlen($code) === 3) {
            return self::LIST[$code][0] ?? null;
        }

        return strlen($code) === 2 ? $code : null;
    }

    public static function iso2FromName(?string $name): ?string
    {
        $name = mb_strtolower(trim((string) $name));
        if ($name === '') {
            return null;
        }
        if (in_array($name, ['united kingdom', 'uk', 'england', 'scotland', 'wales', 'northern ireland', 'great britain'], true)) {
            return 'GB';
        }
        foreach (self::LIST as [$iso2, $bg]) {
            if (mb_strtolower($bg) === $name) {
                return $iso2;
            }
        }

        return null;
    }

    public static function language(?string $code): ?string
    {
        $code = strtolower(trim((string) $code));

        return strlen($code) === 3 ? (self::LANGUAGES[$code] ?? null) : (strlen($code) >= 2 ? substr($code, 0, 2) : null);
    }

    public static function name(?string $iso2): string
    {
        $iso2 = strtoupper((string) $iso2);
        foreach (self::LIST as [$code, $bg]) {
            if ($code === $iso2) {
                return $bg;
            }
        }

        return $iso2 !== '' ? $iso2 : 'без държава';
    }

    public static function languageName(?string $code): ?string
    {
        return self::LANGUAGE_NAMES[strtolower((string) $code)] ?? ($code ?: null);
    }

    /** Държавите за филтъра и формата: код => име. */
    public static function options(): array
    {
        $options = [];
        foreach (self::LIST as [$iso2, $bg]) {
            $options[$iso2] = $bg;
        }

        return $options;
    }
}
