<?php

namespace App\Support\Opportunities;

/**
 * Обявите за обществени поръчки са данни за организации. Лични данни (имена и имейли на лица за контакт, телефони)
 * не се пазят: изчистват се от текстовете, а контактните части на обявите не се копират изобщо.
 */
class Privacy
{
    public static function scrub(?string $text, int $limit = 700): ?string
    {
        if ($text === null || trim($text) === '') {
            return null;
        }
        $text = preg_replace('/[A-Z0-9._%+\-]+@[A-Z0-9.\-]+\.[A-Z]{2,}/iu', '[имейл скрит]', $text);
        // Телефони: с международен код (+44, 0044) или с водеща нула и разделители (0141 287 6410). Датите и CPV кодовете не се пипат.
        $text = preg_replace('/(?<![\w.])(?:(?:\+|00)\d[\d\s().\-]{7,}\d|\(?0\d{1,4}\)?[\s.\-]\d{2,4}[\s.\-]\d{2,4}(?:[\s.\-]\d{2,4})?)(?![\w])/u', '[номер скрит]', $text);
        $text = trim(preg_replace('/\s+/u', ' ', $text));

        return mb_strlen($text) > $limit ? rtrim(mb_substr($text, 0, $limit - 1)) . '…' : $text;
    }
}
