<?php

namespace App\Support\Outreach;

/**
 * Намира на страница на сайта знак, че бизнесът във Великобритания е регистрирана фирма (а не еднолично лице):
 * „Ltd“, „Limited“, „LLP“, „PLC“ след име или „Company number / Registered in England and Wales“ с номер.
 * Връща намерения текст, за да се види в админа на какво се основава решението. Това е наше прочитане на PECR, не правна консултация.
 */
class CompanyEvidence
{
    private const SUFFIX = '/(?<![\p{L}\d])((?:[\p{Lu}\d][\p{L}\d&\'’.\-]*\s+){1,5}(?:Ltd\.?|LTD|Limited|LIMITED|LLP|L\.L\.P\.|PLC|Plc|plc))(?![\p{L}\d])/u';

    private const REGISTERED = '/(?:Company\s+(?:number|no\.?|registration(?:\s+(?:number|no\.?))?)|Registered\s+(?:company\s+)?(?:number|no\.?)|Registered\s+in\s+(?:England(?:\s*(?:and|&)\s*Wales)?|Scotland|Wales|Northern\s+Ireland))[^\d\p{L}]{0,6}(?:(?:company\s+)?(?:number|no\.?|reg\.?)\s*)?[:.#]?\s*(?:SC|NI|OC|SO|NC|R)?\d{6,8}/iu';

    /** Видимият текст на HTML страница: без скриптове и стилове, без тагове. */
    public static function text(string $html): string
    {
        $html = preg_replace('/<(script|style|noscript|template)\b.*?<\/\1>/is', ' ', $html) ?? $html;
        $text = html_entity_decode(strip_tags(str_replace('<', ' <', $html)), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return trim(preg_replace('/\s+/u', ' ', $text) ?? '');
    }

    public static function find(string $html): ?string
    {
        $text = self::text($html);
        if ($text === '') {
            return null;
        }

        foreach ([self::REGISTERED, self::SUFFIX] as $pattern) {
            if (preg_match($pattern, $text, $m)) {
                return mb_substr(trim(preg_replace('/\s+/u', ' ', $m[0])), 0, 200);
            }
        }

        return null;
    }
}
