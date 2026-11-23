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

    /**
     * A description that fits $max characters: whole when it fits, else cut after the last full sentence that leaves at least
     * DESC_MIN characters, else at the last word with "…". Never ends on a dash, colon, comma or semicolon. The layout (post
     * fallback) and the admin preview (PostSeo) both use it, so they stay equal.
     */
    public static function fit(string $text, int $max = self::DESC_MAX): string
    {
        $text = trim((string) preg_replace('/\s+/u', ' ', $text));
        if (mb_strlen($text) <= $max) {
            return $text;
        }

        $head = mb_substr($text, 0, $max + 1);
        // The last sentence end (. ! ?) that is followed by a space or the cut, inside the limit.
        if (preg_match_all('/[.!?…](?=\s|$)/u', $head, $m, PREG_OFFSET_CAPTURE)) {
            foreach (array_reverse($m[0]) as [, $byte]) {
                $len = mb_strlen(substr($head, 0, $byte)) + 1;
                if ($len >= self::DESC_MIN && $len <= $max) {
                    return mb_substr($head, 0, $len);
                }
            }
        }

        $cut = mb_substr($text, 0, $max - 1);
        // Back to the last whole word (the next character was a space when the cut is already on a boundary).
        if (mb_substr($text, $max - 1, 1) !== ' ' && ($pos = mb_strrpos($cut, ' ')) !== false && $pos > 0) {
            $cut = mb_substr($cut, 0, $pos);
        }

        return rtrim($cut, " ,;:.-–—") . '…';
    }

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
