<?php

namespace App\Support;

/** Телефонни номера за връзки: tel:, Viber и WhatsApp искат международния формат (359…). */
class Phone
{
    /** „0888 12 34 56“ → „359888123456“; чужди номера с + остават както са, без +. */
    public static function international(?string $phone): ?string
    {
        $raw = trim((string) $phone);
        $digits = preg_replace('/\D/', '', $raw);
        if ($digits === '') {
            return null;
        }
        if (str_starts_with($raw, '+') || str_starts_with($digits, '359')) {
            return $digits;
        }
        if (str_starts_with($digits, '00')) {
            return substr($digits, 2);
        }

        return str_starts_with($digits, '0') ? '359' . substr($digits, 1) : $digits;
    }

    public static function tel(?string $phone): string
    {
        return 'tel:' . preg_replace('/[^\d+]/', '', (string) $phone);
    }

    public static function viber(?string $phone): ?string
    {
        return ($n = self::international($phone)) ? 'viber://chat?number=%2B' . $n : null;
    }

    public static function whatsapp(?string $phone): ?string
    {
        return ($n = self::international($phone)) ? 'https://wa.me/' . $n : null;
    }
}
