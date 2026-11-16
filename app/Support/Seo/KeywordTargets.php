<?php

namespace App\Support\Seo;

use App\Models\Post;
use App\Support\Content;

/** Целевата страница на ключова дума: страница от SEO настройките, услуга или статия. Стойността се пази като текст. */
class KeywordTargets
{
    /** @return array<string, array<string, string>> група => [стойност => етикет] */
    public static function options(): array
    {
        $services = [];
        foreach ((array) config('creatium.service_pages') as $key => $page) {
            $services['service:' . $key] = (string) ($page['title'] ?? $key);
        }
        $posts = [];
        foreach (Post::orderByDesc('published_at')->orderByDesc('id')->limit(300)->get() as $post) {
            $posts['post:' . $post->id] = (string) ($post->tr('title', 'bg') ?? $post->tr('title', 'en'));
        }

        return array_filter([
            'Страници' => Content::SEO_PAGES,
            'Услуги' => $services,
            'Статии' => $posts,
        ]);
    }

    public static function label(?string $target): ?string
    {
        if (! $target) {
            return null;
        }
        foreach (self::options() as $group) {
            if (isset($group[$target])) {
                return $group[$target];
            }
        }

        return Content::SEO_PAGES[$target] ?? $target;
    }

    public static function url(?string $target, string $locale = 'bg'): ?string
    {
        if (! $target) {
            return null;
        }
        if (isset(Content::SEO_PAGES[$target])) {
            return lroute($target, [], $locale);
        }
        if (str_starts_with($target, 'service:')) {
            return \App\Http\Controllers\PageController::serviceUrl(substr($target, 8), $locale);
        }
        if (str_starts_with($target, 'post:')) {
            $post = Post::find((int) substr($target, 5));

            return $post?->tr('slug', $locale) ? $post->url($locale) : ($post?->tr('slug', 'bg') ? $post->url('bg') : null);
        }

        return null;
    }
}
