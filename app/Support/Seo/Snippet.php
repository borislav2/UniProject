<?php

namespace App\Support\Seo;

/**
 * Дължини и вид в Google: заглавие до 60 знака и 561 px (Arial 20px, както го мери Screaming Frog), описание до 155 знака
 * и 985 px (Arial 14px). Ширините са на Arial (Liberation Sans има същите), в хилядни от em, и за кирилицата също.
 * Същата таблица отива и в живия преглед на администрацията (_live.blade.php през widthTable()), за да съвпадат.
 */
class Snippet
{
    public const TITLE_MAX = 60;

    public const TITLE_MIN = 30;

    public const DESC_MAX = 155;

    public const DESC_MIN = 70;

    public const TITLE_PX = 561;

    public const DESC_PX = 985;

    public const TITLE_FONT = 20;

    public const DESC_FONT = 14;

    /** Дължина на „ | Creatium Lab“ към заглавието. */
    public const SUFFIX = ' | Creatium Lab';

    /** Ширина на знак в Arial, в хилядни от em. Знак, който го няма, е със средна ширина. */
    private const WIDTHS = [
        ' ' => 278, '!' => 278, '"' => 355, '#' => 556, '$' => 556, '%' => 889, '&' => 667, '\'' => 191,
        '(' => 333, ')' => 333, '*' => 389, '+' => 584, ',' => 278, '-' => 333, '.' => 278, '/' => 278,
        '0' => 556, '1' => 556, '2' => 556, '3' => 556, '4' => 556, '5' => 556, '6' => 556, '7' => 556,
        '8' => 556, '9' => 556, ':' => 278, ';' => 278, '<' => 584, '=' => 584, '>' => 584, '?' => 556,
        '@' => 1015, 'A' => 667, 'B' => 667, 'C' => 722, 'D' => 722, 'E' => 667, 'F' => 611, 'G' => 778,
        'H' => 722, 'I' => 278, 'J' => 500, 'K' => 667, 'L' => 556, 'M' => 833, 'N' => 722, 'O' => 778,
        'P' => 667, 'Q' => 778, 'R' => 722, 'S' => 667, 'T' => 611, 'U' => 722, 'V' => 667, 'W' => 944,
        'X' => 667, 'Y' => 667, 'Z' => 611, '[' => 278, '\\' => 278, ']' => 278, '^' => 469, '_' => 556,
        '`' => 333, 'a' => 556, 'b' => 556, 'c' => 500, 'd' => 556, 'e' => 556, 'f' => 278, 'g' => 556,
        'h' => 556, 'i' => 222, 'j' => 222, 'k' => 500, 'l' => 222, 'm' => 833, 'n' => 556, 'o' => 556,
        'p' => 556, 'q' => 556, 'r' => 333, 's' => 500, 't' => 278, 'u' => 556, 'v' => 500, 'w' => 722,
        'x' => 500, 'y' => 500, 'z' => 500, '{' => 334, '|' => 260, '}' => 334, '~' => 584, 'А' => 667,
        'Б' => 656, 'В' => 667, 'Г' => 542, 'Д' => 677, 'Е' => 667, 'Ж' => 923, 'З' => 604, 'И' => 719,
        'Й' => 719, 'К' => 583, 'Л' => 656, 'М' => 833, 'Н' => 722, 'О' => 778, 'П' => 719, 'Р' => 667,
        'С' => 722, 'Т' => 611, 'У' => 635, 'Ф' => 760, 'Х' => 667, 'Ц' => 740, 'Ч' => 667, 'Ш' => 917,
        'Щ' => 938, 'Ъ' => 792, 'Ы' => 885, 'Ь' => 656, 'Э' => 719, 'Ю' => 1010, 'Я' => 722, 'а' => 556,
        'б' => 573, 'в' => 531, 'г' => 365, 'д' => 583, 'е' => 556, 'ж' => 669, 'з' => 458, 'и' => 559,
        'й' => 559, 'к' => 438, 'л' => 583, 'м' => 688, 'н' => 552, 'о' => 556, 'п' => 542, 'р' => 556,
        'с' => 500, 'т' => 458, 'у' => 500, 'ф' => 823, 'х' => 500, 'ц' => 573, 'ч' => 521, 'ш' => 802,
        'щ' => 823, 'ъ' => 625, 'ы' => 719, 'ь' => 521, 'э' => 510, 'ю' => 750, 'я' => 542, 'Ё' => 667,
        'ё' => 556, 'Є' => 719, 'є' => 510, 'І' => 278, 'і' => 222, 'Ї' => 278, 'ї' => 278, 'Ў' => 635,
        'ў' => 500, '€' => 556, '№' => 1073, '„' => 333, '“' => 333, '”' => 333, '‘' => 222, '’' => 222,
        '–' => 556, '—' => 1000, '…' => 1000, '°' => 400, '²' => 333, '·' => 333, '§' => 556, '&' => 667,
        '©' => 737, '®' => 737, '™' => 1000, '×' => 584, '«' => 556, '»' => 556,
    ];

    private const AVERAGE = 560;

    /**
     * A description that fits $max characters: whole when it fits, else cut after the last full sentence that leaves at least
     * DESC_MIN characters, else at the last word with "…". Never ends on a dash, colon, comma or semicolon. The layout (post
     * fallback) and the admin preview (PostSeo) both use it, so they stay equal.
     */
    public static function fit(string $text, int $max = self::DESC_MAX, float $maxPx = self::DESC_PX): string
    {
        $text = trim((string) preg_replace('/\s+/u', ' ', $text));
        // Cyrillic is wide: 155 characters can be past 985 px, so the character limit shrinks until the pixel limit holds too.
        for ($limit = $max; $limit > self::DESC_MIN; $limit -= 3) {
            $out = self::fitChars($text, $limit);
            if (self::width($out, self::DESC_FONT) <= $maxPx) {
                return $out;
            }
        }

        return self::fitChars($text, self::DESC_MIN);
    }

    private static function fitChars(string $text, int $max): string
    {
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
        return self::state(self::length($title), self::TITLE_MIN, self::TITLE_MAX, self::width(trim((string) $title)) > self::TITLE_PX);
    }

    public static function descriptionState(?string $description): string
    {
        return self::state(self::length($description), self::DESC_MIN, self::DESC_MAX, self::descriptionWidth($description) > self::DESC_PX);
    }

    private static function state(int $length, int $min, int $max, bool $tooWide = false): string
    {
        return match (true) {
            $length === 0 => 'missing',
            $length > $max || $tooWide => 'long',
            $length < $min => 'short',
            default => 'ok',
        };
    }

    /** Ширина на знак в px при 20px шрифт (Arial). */
    public static function charWidth(string $char, int $font = self::TITLE_FONT): float
    {
        return (self::WIDTHS[$char] ?? self::AVERAGE) * $font / 1000;
    }

    /** Ширина на текста в px (заглавие: 20px, описание: 14px). */
    public static function width(string $text, int $font = self::TITLE_FONT): float
    {
        $w = 0.0;
        foreach (mb_str_split($text) as $c) {
            $w += self::charWidth($c, $font);
        }

        return $w;
    }

    public static function descriptionWidth(?string $text): float
    {
        return self::width(trim((string) $text), self::DESC_FONT);
    }

    /** Таблицата за живия преглед в JavaScript (един източник). */
    public static function widthTable(): array
    {
        return self::WIDTHS + ['_avg' => self::AVERAGE];
    }

    /**
     * Заглавието със „ | Creatium Lab“, но само когато остава до 60 знака и до 561 px; иначе без него. Заглавие, което е дълго и без
     * наставката (статия без собствено мета заглавие), се реже при последната цяла дума, за да не го реже Google по средата.
     * Мета заглавие, написано в администрацията, не минава оттук: то се показва, както е написано.
     */
    public static function withSuffix(string $title): string
    {
        $full = $title . self::SUFFIX;
        if (mb_strlen($full) <= self::TITLE_MAX && self::width($full) <= self::TITLE_PX) {
            return $full;
        }

        return self::fitTitle($title);
    }

    /** Заглавие до 60 знака и 561 px, отрязано при последната цяла дума, без запетая, точка или съюз накрая. */
    public static function fitTitle(string $title): string
    {
        $title = trim($title);
        if (mb_strlen($title) <= self::TITLE_MAX && self::width($title) <= self::TITLE_PX) {
            return $title;
        }

        $cut = '';
        foreach (mb_str_split($title) as $c) {
            if (mb_strlen($cut) >= self::TITLE_MAX || self::width($cut . $c) > self::TITLE_PX) {
                break;
            }
            $cut .= $c;
        }
        if (($pos = mb_strrpos($cut, ' ')) !== false && $pos > self::TITLE_MIN) {
            $cut = mb_substr($cut, 0, $pos);
        }

        // No hanging one or two letter word (и, в, на, за, to, of) at the end.
        while (preg_match('/^(.*\S)\s+\p{L}{1,2}$/u', $cut, $m) && mb_strlen($m[1]) >= self::TITLE_MIN) {
            $cut = $m[1];
        }

        return rtrim($cut, " ,;:.-–—");
    }

    /** Заглавието, както го реже Google (около 561 px), с „…“. */
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
            if ($w > self::TITLE_PX - self::charWidth('…')) {
                break;
            }
            $out .= $c;
        }

        return rtrim($out) . '…';
    }

    public static function truncateDescription(string $text): string
    {
        $text = trim($text);

        if (mb_strlen($text) <= self::DESC_MAX && self::width($text, self::DESC_FONT) <= self::DESC_PX) {
            return $text;
        }
        $out = '';
        $w = 0.0;
        foreach (mb_str_split($text) as $c) {
            $w += self::charWidth($c, self::DESC_FONT);
            if ($w > self::DESC_PX - self::charWidth('…', self::DESC_FONT) || mb_strlen($out) >= self::DESC_MAX - 1) {
                break;
            }
            $out .= $c;
        }

        return rtrim($out) . '…';
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
