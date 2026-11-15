<?php

namespace App\Support;

use App\Support\Outreach\Markets;

/** Телефонни номера за връзки: tel:, Viber и WhatsApp искат международния формат (359…). */
class Phone
{
    /**
     * „0888 12 34 56“ → „359888123456“; чужди номера с + остават както са, без +.
     * За чужд пазар ($market различен от bg) номер без код („0161 496 0000“) получава кода на пазара, не българския.
     */
    public static function international(?string $phone, ?string $market = null): ?string
    {
        $raw = trim((string) $phone);
        $digits = preg_replace('/\D/', '', $raw);
        if ($digits === '') {
            return null;
        }
        if (str_starts_with($raw, '+')) {
            return $digits;
        }
        if (str_starts_with($digits, '00')) {
            return substr($digits, 2);
        }
        $market = Markets::normalize($market);
        if ($market !== Markets::DEFAULT) {
            $code = ltrim(Markets::phonePrefix($market), '+');

            // Националният номер започва с 0 („01614960000“); без нула и без код го оставяме като код + номер.
            return str_starts_with($digits, $code) && strlen($digits) > strlen($code) + 7 ? $digits : $code . ltrim($digits, '0');
        }
        if (str_starts_with($digits, '359')) {
            return $digits;
        }

        return str_starts_with($digits, '0') ? '359' . substr($digits, 1) : $digits;
    }

    /** tel: с международен номер за чужд пазар (екипът е в България), за български: както е написан. */
    public static function tel(?string $phone, ?string $market = null): string
    {
        if (Markets::normalize($market) !== Markets::DEFAULT && ($international = self::international($phone, $market))) {
            return 'tel:+' . $international;
        }

        return 'tel:' . preg_replace('/[^\d+]/', '', (string) $phone);
    }

    public static function viber(?string $phone, ?string $market = null): ?string
    {
        return ($n = self::international($phone, $market)) ? 'viber://chat?number=%2B' . $n : null;
    }

    public static function whatsapp(?string $phone, ?string $market = null): ?string
    {
        return ($n = self::international($phone, $market)) ? 'https://wa.me/' . $n : null;
    }
}
