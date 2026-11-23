<?php

namespace App\Support\Seo;

use App\Models\Post;
use App\Support\Schema;
use Illuminate\Support\Str;

/**
 * SEO проверки на статия: ефективното заглавие и описание (както ги слага layouts/public) и контролен списък по ключовата дума.
 * Проверките са прости и честни: те казват какво липсва, не обещават позиция в Google.
 */
class PostSeo
{
    public const MIN_WORDS = 300;

    public const WARN_WORDS = 150;

    /** Заглавието в Google: meta_title или заглавието + „ | Creatium Lab“ (без наставката, когато заглавието стане над 60 знака), като в layouts/public. */
    public static function effectiveTitle(Post $post, string $locale): ?string
    {
        if ($meta = $post->tr('meta_title', $locale)) {
            return $meta;
        }
        $title = $post->tr('title', $locale);

        return $title ? (mb_strlen($title) + 15 > 60 ? $title : $title . ' | Creatium Lab') : null;
    }

    public static function effectiveDescription(Post $post, string $locale): ?string
    {
        // Own description as written; the fallback (excerpt or the start of the text) is cut at a sentence or word to fit Google.
        return $post->tr('meta_description', $locale) ?? ($post->tr('title', $locale) ? self::summary($post, $locale) : null);
    }

    private static function summary(Post $post, string $locale): string
    {
        return Snippet::fit($post->excerptOrSummary($locale, 400));
    }

    /** @return list<array{id: string, label: string, state: string, detail: string}> state: ok | warn | bad */
    public static function checks(Post $post, string $locale): array
    {
        $title = $post->tr('title', $locale);
        $body = (string) $post->tr('body', $locale);
        $keyword = trim((string) $post->tr('focus_keyword', $locale));
        $effTitle = self::effectiveTitle($post, $locale);
        $effDesc = self::effectiveDescription($post, $locale);
        $slug = (string) $post->tr('slug', $locale);

        $checks = [];
        $add = function (string $id, string $label, string $state, string $detail = '') use (&$checks) {
            $checks[] = compact('id', 'label', 'state', 'detail');
        };

        $ts = Snippet::titleState($effTitle);
        $add('title', 'Заглавие в Google (30 до 60 знака)', match ($ts) { 'ok' => 'ok', 'short' => 'warn', default => 'bad' }, $effTitle ? Snippet::length($effTitle) . ' знака' : 'няма заглавие');
        $ds = Snippet::descriptionState($effDesc);
        $own = (bool) $post->tr('meta_description', $locale);
        $add('description', 'Meta описание (70 до 155 знака)', match ($ds) { 'ok' => 'ok', 'short' => 'warn', default => 'bad' },
            $ds === 'missing' ? 'няма описание' : Snippet::length($effDesc) . ' знака' . ($own ? '' : ' (не е писано тук: взема се от краткото описание или от текста)'));

        if ($keyword === '') {
            $add('keyword', 'Ключова дума', 'warn', 'не е зададена');
        } else {
            $add('keyword', 'Ключова дума', 'ok', '„' . $keyword . '“');
        }
        $has = fn (?string $haystack) => $keyword !== '' && $haystack !== null && mb_stripos(self::plain($haystack), $keyword) !== false;
        $kw = function (string $id, string $label, bool $found) use (&$add, $keyword) {
            $add($id, $label, $keyword === '' ? 'warn' : ($found ? 'ok' : 'warn'), $keyword === '' ? 'първо задайте дума' : ($found ? 'намерена' : 'не е намерена'));
        };
        $kw('kw_title', 'Думата е в заглавието', $has($title));
        $kw('kw_intro', 'Думата е в първия абзац', $has(self::firstParagraph($body)));
        $kw('kw_heading', 'Думата е в подзаглавие', collect(self::headings($body))->contains(fn ($h) => $has($h)));
        $slugKey = $keyword === '' ? '' : Str::slug($keyword, '-', 'bg');
        $kw('kw_slug', 'Думата е в адреса на статията', $slugKey !== '' && str_contains($slug, $slugKey));
        $kw('kw_description', 'Думата е в meta описанието', $has($effDesc));

        $words = self::wordCount($body);
        $add('length', 'Дължина на текста (поне ' . self::MIN_WORDS . ' думи)', $words >= self::MIN_WORDS ? 'ok' : ($words >= self::WARN_WORDS ? 'warn' : 'bad'), $words . ' думи');

        $links = self::internalLinks($body);
        $add('links', 'Поне 2 връзки към други страници от сайта', $links >= 2 ? 'ok' : ($links === 1 ? 'warn' : 'bad'), $links . ' връзки');

        [$total, $noAlt] = self::images($body);
        $add('images', 'Картинките в текста имат alt', $noAlt === 0 ? 'ok' : 'bad', $total === 0 ? 'няма картинки в текста' : ($noAlt === 0 ? "всички $total с alt" : "$noAlt от $total без alt"));

        return $checks;
    }

    /** Брой изпълнени проверки от всички. */
    public static function score(array $checks): array
    {
        return [collect($checks)->where('state', 'ok')->count(), count($checks)];
    }

    /** Обобщение за списъка със статии: езикът (bg, иначе първият наличен), оценка и дали има червен проблем. */
    public static function overview(Post $post): array
    {
        $locale = $post->tr('title', 'bg') ? 'bg' : 'en';
        $checks = self::checks($post, $locale);
        [$ok, $total] = self::score($checks);
        $effTitle = self::effectiveTitle($post, $locale);
        $red = in_array(Snippet::titleState($effTitle), ['missing', 'long'], true) || in_array(Snippet::descriptionState(self::effectiveDescription($post, $locale)), ['missing', 'long'], true);

        return ['locale' => $locale, 'ok' => $ok, 'total' => $total, 'red' => $red, 'tone' => $red ? 'bad' : ($ok >= 9 ? 'ok' : 'warn'), 'checks' => $checks];
    }

    public static function plain(string $markdown): string
    {
        $text = preg_replace('/!\[[^\]]*\]\([^)]*\)/u', ' ', $markdown);
        $text = preg_replace('/\[([^\]]*)\]\([^)]*\)/u', '$1', $text);
        $text = preg_replace('/[*_`>#]+/u', ' ', $text);

        return trim(preg_replace('/\s+/u', ' ', $text));
    }

    public static function wordCount(string $markdown): int
    {
        $plain = self::plain($markdown);

        return $plain === '' ? 0 : count(preg_split('/\s+/u', $plain, -1, PREG_SPLIT_NO_EMPTY));
    }

    public static function firstParagraph(string $markdown): string
    {
        foreach (preg_split('/\R{2,}/u', trim($markdown)) as $block) {
            $block = trim($block);
            if ($block !== '' && ! preg_match('/^(#|!\[|[-*+]\s|\d+\.\s|>|```|---)/u', $block)) {
                return $block;
            }
        }

        return '';
    }

    /** @return list<string> */
    public static function headings(string $markdown): array
    {
        preg_match_all('/^#{2,6}\s+(.+?)\s*#*\s*$/mu', $markdown, $m);

        return $m[1] ?? [];
    }

    public static function internalLinks(string $markdown): int
    {
        preg_match_all('/(?<!!)\[[^\]]*\]\(\s*<?([^)\s>]+)/u', $markdown, $m);
        $host = strtolower((string) parse_url((string) config('app.url'), PHP_URL_HOST));
        $count = 0;
        foreach ($m[1] ?? [] as $href) {
            $linkHost = strtolower((string) parse_url($href, PHP_URL_HOST));
            if ((str_starts_with($href, '/') && ! str_starts_with($href, '//')) || ($linkHost !== '' && $host !== '' && ($linkHost === $host || $linkHost === 'www.' . $host))) {
                $count++;
            }
        }

        return $count;
    }

    /** @return array{0: int, 1: int} картинки и колко от тях са без alt */
    public static function images(string $markdown): array
    {
        preg_match_all('/!\[([^\]]*)\]\(/u', $markdown, $m);

        return [count($m[1]), count(array_filter($m[1], fn ($alt) => trim($alt) === ''))];
    }

    /** Кой е авторът в структурираните данни: член на екипа или фирмата (като Schema::postAuthor). */
    public static function authorLabel(Post $post): string
    {
        $author = Schema::postAuthor($post);

        return ($author['@type'] ?? '') === 'Person' ? $author['name'] . ' (от екипа)' : 'Creatium Lab (фирмата)';
    }
}
