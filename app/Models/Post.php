<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Post extends Model
{
    use HasTranslations;

    public const STATUSES = ['draft' => 'Чернова', 'published' => 'Публикувана'];

    protected array $translatable = ['title', 'slug', 'excerpt', 'body', 'meta_title', 'meta_description', 'canonical_url', 'og_title', 'og_description'];

    protected $fillable = [
        'post_category_id', 'user_id', 'title', 'slug', 'excerpt', 'body', 'meta_title', 'meta_description',
        'canonical_url', 'og_title', 'og_description', 'cover_image', 'og_image', 'status', 'published_at',
    ];

    protected $casts = ['published_at' => 'datetime'];

    public function category(): BelongsTo
    {
        return $this->belongsTo(PostCategory::class, 'post_category_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** Published, not scheduled for later, and written in the given language. */
    public function scopeVisible(Builder $query, ?string $locale = null): Builder
    {
        $locale ??= app()->getLocale();

        return $query->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->whereNotNull("title->$locale")
            ->whereNotNull("slug->$locale");
    }

    public function isVisible(?string $locale = null): bool
    {
        return $this->status === 'published' && $this->published_at && $this->published_at->lte(now())
            && $this->tr('title', $locale) && $this->tr('slug', $locale);
    }

    /** Languages this post can be read in. */
    public function locales(): array
    {
        return array_values(array_filter(['bg', 'en'], fn ($locale) => $this->isVisible($locale)));
    }

    public function url(?string $locale = null): string
    {
        $locale ??= app()->getLocale();

        return lroute('blog.show', ['slug' => $this->tr('slug', $locale)], $locale);
    }

    /** Body rendered from Markdown. Raw HTML is stripped, so editors cannot inject scripts. */
    public function bodyHtml(?string $locale = null): string
    {
        return Str::markdown((string) $this->tr('body', $locale), [
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);
    }

    public function excerptOrSummary(?string $locale = null, int $limit = 180): string
    {
        return $this->tr('excerpt', $locale)
            ?? Str::limit(trim(preg_replace('/\s+/', ' ', strip_tags($this->bodyHtml($locale)))), $limit);
    }
}
