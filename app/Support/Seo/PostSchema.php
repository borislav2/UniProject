<?php

namespace App\Support\Seo;

use App\Models\Post;
use App\Support\Design;
use App\Support\Schema;
use Illuminate\Support\Str;

/**
 * The BlogPosting JSON-LD and the Markdown twin of a post. Same data in both designs. Nothing here changes the content of a post.
 */
class PostSchema
{
    /** Words of the body as a reader sees them (Markdown rendered, tags removed). */
    public static function wordCount(Post $post, ?string $locale = null): int
    {
        $text = trim(html_entity_decode(strip_tags(str_replace('<', ' <', $post->bodyHtml($locale))), ENT_QUOTES | ENT_HTML5, 'UTF-8'));

        return $text === '' ? 0 : count(preg_split('/\s+/u', $text));
    }

    /** „Обновено“ is shown (and meaningful) only when the post was changed more than a day after it was published. */
    public static function wasUpdated(Post $post): bool
    {
        return $post->published_at && $post->updated_at && $post->updated_at->gt($post->published_at->copy()->addDay());
    }

    public static function markdownUrl(Post $post, ?string $locale = null): string
    {
        $locale ??= app()->getLocale();

        return lroute('blog.markdown', ['slug' => $post->tr('slug', $locale)], $locale);
    }

    public static function blogPosting(Post $post): array
    {
        $locale = app()->getLocale();
        $keyword = trim((string) $post->tr('focus_keyword'));
        $category = $post->category?->trOrBg('name');

        return array_filter([
            '@' . 'context' => 'https://schema.org',
            '@type' => 'BlogPosting',
            '@id' => $post->url() . '#article',
            'headline' => $post->tr('title'),
            'description' => $post->excerptOrSummary(),
            'datePublished' => $post->published_at->toAtomString(),
            'dateModified' => ($post->updated_at && $post->updated_at->gt($post->published_at) ? $post->updated_at : $post->published_at)->toAtomString(),
            'inLanguage' => $locale,
            'mainEntityOfPage' => ['@type' => 'WebPage', '@id' => $post->url()],
            'url' => $post->url(),
            'wordCount' => self::wordCount($post),
            'articleSection' => $category,
            'keywords' => $keyword !== '' ? $keyword : null,
            'about' => $keyword !== '' ? ['@type' => 'Thing', 'name' => $keyword] : null,
            'isPartOf' => ['@type' => 'WebSite', '@id' => Schema::websiteId()],
            'image' => $post->cover_image ? asset($post->cover_image) : Schema::shareImage(),
            'author' => Schema::postAuthor($post),
            'publisher' => ['@type' => 'Organization', '@id' => Schema::organizationId(), 'name' => 'Creatium Lab', 'logo' => ['@type' => 'ImageObject', 'url' => Schema::logoUrl()]],
        ], fn ($v) => $v !== null && $v !== '' && $v !== []);
    }

    /** The post as Markdown: title, date, author, summary and body (the body is the Markdown the editors wrote). */
    public static function markdown(Post $post, string $locale): string
    {
        $author = Schema::postAuthor($post)['name'];
        $words = $locale === 'en'
            ? ['published' => 'Published', 'updated' => 'Updated', 'author' => 'Author', 'section' => 'Section', 'url' => 'Address', 'summary' => 'In short']
            : ['published' => 'Публикувана', 'updated' => 'Обновена', 'author' => 'Автор', 'section' => 'Раздел', 'url' => 'Адрес', 'summary' => 'Накратко'];

        $l = ['# ' . $post->tr('title', $locale), ''];
        $l[] = '- ' . $words['published'] . ' ' . $post->published_at->toDateString();
        if (self::wasUpdated($post)) {
            $l[] = '- ' . $words['updated'] . ' ' . $post->updated_at->toDateString();
        }
        $l[] = '- ' . $words['author'] . ' ' . $author;
        if ($post->category) {
            $l[] = '- ' . $words['section'] . ' ' . $post->category->trOrBg('name');
        }
        $l[] = '- ' . $words['url'] . ' ' . $post->url($locale);
        if (filled($post->tr('excerpt', $locale))) {
            $l[] = '';
            $l[] = '> **' . $words['summary'] . '** ' . trim(preg_replace('/\s+/u', ' ', (string) $post->tr('excerpt', $locale)));
        }
        $l[] = '';
        $l[] = trim((string) $post->tr('body', $locale));

        return implode("\n", $l) . "\n";
    }
}
