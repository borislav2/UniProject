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

    public static function data(): array
    {
        return require database_path('data/blog-posts.php');
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

    /** Добавя статиите, които още ги няма (по адрес на български). Връща броя на добавените. */
    public static function import(?Carbon $start = null): int
    {
        $data = self::data();
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
                'status' => 'published',
                'published_at' => $dates[$i],
            ]);
            $added++;
        }

        return $added;
    }
}
