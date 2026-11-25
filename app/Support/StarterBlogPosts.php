<?php

namespace App\Support;

use App\Models\Post;
use App\Models\PostCategory;
use Illuminate\Support\Carbon;

/**
 * Първите статии на блога (database/data/blog-posts.php). Добавят се веднъж от миграция:
 * първите три излизат веднага, а всяка следваща е насрочена 2–4 дни след предишната, в 9:00 българско време.
 * Насрочените статии се показват сами, когато дойде датата им (Post::visible()).
 */
class StarterBlogPosts
{
    public const PUBLISH_NOW = 3;

    /** Дни между две насрочени статии: редуват се, за да не излизат в един и същ ден от седмицата. */
    public const GAPS = [3, 2, 4, 3, 4, 2];

    public static function data(string $file = 'blog-posts.php'): array
    {
        $data = require database_path('data/' . $file);

        // The English version of a post sits in database/data/en/{Bulgarian slug}.md (see english()). It is merged here as
        // title_en, slug_en, excerpt_en and body_en, unless the data file already carries those keys itself.
        foreach ($data['posts'] as $i => $post) {
            $english = self::english($post['slug']);
            if ($english) {
                $data['posts'][$i] += ['title_en' => $english['title'], 'slug_en' => $english['slug'], 'excerpt_en' => $english['excerpt'], 'body_en' => $english['body']];
            }
        }

        return $data;
    }

    public const ENGLISH_DIR = 'data/en';

    /**
     * The English text of a post, from database/data/en/{Bulgarian slug}.md: the first three lines are the title, the slug and the
     * excerpt, then a line with three dashes, then the Markdown body. Null when the file does not exist.
     *
     * @return array{title: string, slug: string, excerpt: string, body: string}|null
     */
    public static function english(string $bulgarianSlug): ?array
    {
        $path = database_path(self::ENGLISH_DIR . "/$bulgarianSlug.md");
        if (! is_file($path)) {
            return null;
        }

        [$head, $body] = array_pad(explode("\n---\n", str_replace("\r\n", "\n", file_get_contents($path)), 2), 2, '');
        $lines = array_map('trim', explode("\n", trim($head)));

        return ['title' => $lines[0] ?? '', 'slug' => $lines[1] ?? '', 'excerpt' => $lines[2] ?? '', 'body' => trim($body)];
    }

    /** True when no other post already uses this English address. */
    public static function englishSlugIsFree(string $slug, ?int $exceptId = null): bool
    {
        return ! Post::where('slug->en', $slug)->when($exceptId, fn ($q) => $q->whereKeyNot($exceptId))->exists();
    }

    /** The English address, or the same with -2, -3 ... added when another post already has it. */
    private static function uniqueEnglishSlug(string $slug, ?int $exceptId = null): string
    {
        $candidate = $slug;
        for ($n = 2; ! self::englishSlugIsFree($candidate, $exceptId); $n++) {
            $candidate = "$slug-$n";
        }

        return $candidate;
    }

    /**
     * Gives the posts of $file their English version (title_en, slug_en, excerpt_en, body_en) where the post exists and its English
     * title is still empty. A post with an English title (written by the owners or filled earlier) is never touched, and the
     * Bulgarian fields are never changed. Returns the number of posts filled.
     */
    public static function fillEnglish(string $file): int
    {
        $filled = 0;
        foreach (self::data($file)['posts'] as $data) {
            if (blank($data['title_en'] ?? null) || blank($data['body_en'] ?? null)) {
                continue;
            }
            $post = Post::where('slug->bg', $data['slug'])->first();
            if (! $post || filled($post->tr('title', 'en'))) {
                continue;
            }

            $fields = [
                'title' => $data['title_en'],
                'slug' => filled($post->tr('slug', 'en')) && self::englishSlugIsFree($post->tr('slug', 'en'), $post->id)
                    ? $post->tr('slug', 'en') : self::uniqueEnglishSlug($data['slug_en'], $post->id),
                'excerpt' => filled($post->tr('excerpt', 'en')) ? $post->tr('excerpt', 'en') : $data['excerpt_en'],
                'body' => filled($post->tr('body', 'en')) ? $post->tr('body', 'en') : trim($data['body_en']),
            ];
            foreach ($fields as $field => $value) {
                $values = (array) $post->getAttribute($field);
                $post->setAttribute($field, array_merge($values, ['en' => $value]));
            }
            $post->save();
            $filled++;
        }

        return $filled;
    }

    /** Датите на публикуване по реда на статиите. */
    public static function schedule(int $count, Carbon $start): array
    {
        $dates = [];
        $next = $start->copy()->timezone('Europe/Sofia')->setTime(9, 0);

        for ($i = 0; $i < $count; $i++) {
            if ($i < self::PUBLISH_NOW) {
                // Минути разлика, за да е стабилен редът в блога.
                $dates[] = $start->copy()->subMinutes(self::PUBLISH_NOW - $i)->utc();
                continue;
            }
            $next = $next->copy()->addDays(self::GAPS[($i - self::PUBLISH_NOW) % count(self::GAPS)]);
            $dates[] = $next->copy()->utc();
        }

        return $dates;
    }

    public const EXPANDED_DIR = 'data/blog-expanded';

    public const PREVIOUS_DIR = 'data/blog-previous';

    /** The earlier expanded text of a post that was expanded again (search console pass, 25.11.2026), or null. */
    public static function previousBody(string $slug): ?string
    {
        $file = database_path(self::PREVIOUS_DIR . "/$slug.md");

        return is_file($file) ? trim(file_get_contents($file)) : null;
    }

    public const COVERS_DIR = 'images/blog';

    /**
     * По-дългите версии на първите статии (database/data/blog-expanded/{slug}.md) и кориците им (public/images/blog/{slug}.webp).
     * Текстът се сменя само ако статията е непроменена от админа (още е точно оригиналът от blog-posts.php / summit-posts.php),
     * а корицата се слага само ако статията няма снимка. Каквото собствениците са редактирали, не се пипа.
     *
     * @return array{bodies: int, covers: int}
     */
    public static function expand(): array
    {
        $originals = [];
        foreach (['blog-posts.php', 'summit-posts.php'] as $file) {
            foreach (self::data($file)['posts'] as $post) {
                $originals[$post['slug']] = trim($post['body']);
            }
        }

        $done = ['bodies' => 0, 'covers' => 0];
        foreach (glob(database_path(self::EXPANDED_DIR . '/*.md')) as $path) {
            $slug = basename($path, '.md');
            $post = Post::where('slug->bg', $slug)->first();
            if (! $post) {
                continue;
            }

            $body = $post->body;
            // Replaced when the text is still the original or, for a post that got a newer expanded version, exactly the previous
            // expanded version (kept in database/data/blog-previous/{slug}.md). Anything the owners edited stays.
            $previous = self::previousBody($slug);
            $current = trim((string) ($body['bg'] ?? ''));
            if ($current !== trim(file_get_contents($path)) && ((isset($originals[$slug]) && $current === $originals[$slug]) || ($previous !== null && $current === $previous))) {
                $post->body = ['bg' => trim(file_get_contents($path))] + (array) $body;
                $done['bodies']++;
            }

            $cover = self::COVERS_DIR . "/$slug.webp";
            if (blank($post->cover_image) && is_file(public_path($cover))) {
                $post->cover_image = $cover;
                $done['covers']++;
            }

            $post->save();
        }

        return $done;
    }

    /**
     * Насрочва черновите от $file за $at: стават публикувани с тази дата и се показват сами, когато тя дойде.
     * Пипа само статии, които още са чернови без дата (ако собствениците вече са ги публикували или насрочили, остават така).
     * Първата статия от файла получава най-късната минута, за да е най-отгоре в блога.
     */
    public static function publishDraftsAt(Carbon $at, string $file): int
    {
        $posts = self::data($file)['posts'];
        $count = 0;
        foreach ($posts as $i => $data) {
            $count += Post::where('slug->bg', $data['slug'])->where('status', 'draft')->whereNull('published_at')
                ->update(['status' => 'published', 'published_at' => $at->copy()->addMinutes(count($posts) - 1 - $i)->utc()]);
        }

        return $count;
    }

    /**
     * Слага корици на статии, които вече са в базата (import() пропуска съществуващите): за всяка статия от $file с 'cover',
     * чийто файл съществува, и само ако cover_image още е празен (по български адрес). Каквото е качено или сменено, не се пипа.
     * Връща броя на пълнените.
     */
    public static function fillCovers(string $file): int
    {
        $filled = 0;
        foreach (self::data($file)['posts'] as $data) {
            if (empty($data['cover']) || ! is_file(public_path($data['cover']))) {
                continue;
            }
            $post = Post::where('slug->bg', $data['slug'])->first();
            if ($post && blank($post->cover_image)) {
                $post->cover_image = $data['cover'];
                $post->save();
                $filled++;
            }
        }

        return $filled;
    }

    /**
     * Добавя статиите, които още ги няма (по адрес на български). Връща броя на добавените.
     * С $status = 'draft' влизат като чернови без дата: собствениците ги преглеждат и публикуват от админа.
     */
    public static function import(?Carbon $start = null, string $file = 'blog-posts.php', string $status = 'published'): int
    {
        $data = self::data($file);
        $categories = [];
        foreach ($data['categories'] as $key => $names) {
            $categories[$key] = PostCategory::findBySlug($names['bg'][1], 'bg') ?? PostCategory::create([
                'name' => ['bg' => $names['bg'][0], 'en' => $names['en'][0]],
                'slug' => ['bg' => $names['bg'][1], 'en' => $names['en'][1]],
            ]);
        }

        $dates = self::schedule(count($data['posts']), $start ?? now());
        $added = 0;
        foreach ($data['posts'] as $i => $post) {
            if (Post::where('slug->bg', $post['slug'])->exists()) {
                continue;
            }
            $english = filled($post['title_en'] ?? null) && filled($post['body_en'] ?? null);
            Post::create([
                'post_category_id' => $categories[$post['category']]->id,
                'title' => ['bg' => $post['title']] + ($english ? ['en' => $post['title_en']] : []),
                'slug' => ['bg' => $post['slug']] + ($english ? ['en' => self::uniqueEnglishSlug($post['slug_en'])] : []),
                'excerpt' => ['bg' => $post['excerpt']] + ($english ? ['en' => $post['excerpt_en']] : []),
                'body' => ['bg' => trim($post['body'])] + ($english ? ['en' => trim($post['body_en'])] : []),
                // Корица по избор ('cover' = път под public/): само ако файлът съществува.
                'cover_image' => isset($post['cover']) && is_file(public_path($post['cover'])) ? $post['cover'] : null,
                'status' => $status,
                // A post can carry its own 'publish_at' (Sofia time, 'Y-m-d H:i'), e.g. the daily posts: three a day at fixed hours.
                'published_at' => $status !== 'published' ? null
                    : (isset($post['publish_at']) ? Carbon::parse($post['publish_at'], 'Europe/Sofia')->utc() : $dates[$i]),
            ]);
            $added++;
        }

        return $added;
    }
}
