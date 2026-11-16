<?php

namespace App\Support\Seo;

/**
 * Дължини и вид в Google: заглавие до около 60 знака (580 px), описание до около 155. Същата таблица на ширините
 * е и в resources/views/admin/seo/_snippet.blade.php (живият преглед), за да съвпадат.
 */
class Snippet
{
    public const TITLE_MAX = 60;

    public const TITLE_MIN = 30;

    public const DESC_MAX = 155;

    public const DESC_MIN = 70;

    public const TITLE_PX = 580;

    public static function length(?string $text): int
    {
        return mb_strlen(trim((string) $text));
    }

    /** missing | long | short | ok */
    public static function titleState(?string $title): string
    {
        return self::state(self::length($title), self::TITLE_MIN, self::TITLE_MAX);
    }

    public static function descriptionState(?string $description): string
    {
        return self::state(self::length($description), self::DESC_MIN, self::DESC_MAX);
    }

    private static function state(int $length, int $min, int $max): string
    {
        return match (true) {
            $length === 0 => 'missing',
            $length > $max => 'long',
            $length < $min => 'short',
            default => 'ok',
        };
    }

    /** Приблизителна ширина на знак в px при 20px шрифт (Arial): тесни, широки, главни, останалите. */
    public static function charWidth(string $char): float
    {
        if (str_contains("il.,;:'|!ij()[]", $char)) {
            return 5.5;
        }
        if ($char === ' ') {
            return 5.6;
        }
        if (str_contains('mwMWЖШЩЮФЪЫЉ@%', $char)) {
            return 17.0;
        }
        if (mb_strtoupper($char) === $char && mb_strtolower($char) !== $char) {
            return 13.6;
        }

        return 10.6;
    }

    public static function width(string $text): float
    {
        $w = 0.0;
        foreach (mb_str_split($text) as $c) {
            $w += self::charWidth($c);
        }

        return $w;
    }

    /** Заглавието, както го реже Google (около 580 px), с „…“. */
    public static function truncateTitle(string $title): string
    {
        $title = trim($title);
        if (self::width($title) <= self::TITLE_PX) {
            return $title;
        }
        $out = '';
        $w = 0.0;
        foreach (mb_str_split($title) as $c) {
            $w += self::charWidth($c);
            if ($w > self::TITLE_PX - 12) {
                break;
            }
            $out .= $c;
        }

        return rtrim($out) . '…';
    }

    public static function truncateDescription(string $text): string
    {
        $text = trim($text);

        return mb_strlen($text) <= self::DESC_MAX ? $text : rtrim(mb_substr($text, 0, self::DESC_MAX - 1)) . '…';
    }

    /** Адресът като „път“ в Google: creatiumlab.com › uslugi › sait. */
    public static function breadcrumb(string $url): string
    {
        $parts = parse_url($url);
        $segments = array_values(array_filter(explode('/', (string) ($parts['path'] ?? ''))));

        return implode(' › ', array_merge([$parts['host'] ?? ''], $segments));
    }

    /** Повторения: [текст (малки букви) => [ключове...]] само за текстове, които се срещат повече от веднъж. */
    public static function duplicates(array $texts): array
    {
        $groups = [];
        foreach ($texts as $key => $text) {
            $normal = mb_strtolower(trim((string) $text));
            if ($normal !== '') {
                $groups[$normal][] = $key;
            }
        }

        return array_filter($groups, fn ($keys) => count($keys) > 1);
    }
}
