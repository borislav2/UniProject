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
        return require database_path('data/' . $file);
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
            if (isset($originals[$slug]) && trim((string) ($body['bg'] ?? '')) === $originals[$slug]) {
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
            Post::create([
                'post_category_id' => $categories[$post['category']]->id,
                'title' => ['bg' => $post['title']],
                'slug' => ['bg' => $post['slug']],
                'excerpt' => ['bg' => $post['excerpt']],
                'body' => ['bg' => trim($post['body'])],
                'status' => $status,
                'published_at' => $status === 'published' ? $dates[$i] : null,
            ]);
            $added++;
        }

        return $added;
    }
}
