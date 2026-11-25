<?php

namespace App\Support;

use App\Models\Post;

/**
 * Search Console pass (25.11.2026): search matching meta titles and descriptions for two posts.
 * A field is filled only when it is empty or still holds exactly the earlier default of ours (PostMetaTitles2026),
 * so whatever the owners typed in „Блог“ is never changed. No dashes and no colons.
 */
class SearchConsoleWins2026
{
    /** slug => [meta_title, meta_description, earlier default titles that may be replaced] */
    public const POSTS = [
        'cheren-petak-2026-danni-za-balgarskiya-kupuvach' => [
            'Black Friday 2026 в България, данни за Черен петък',
            '72% от пазаруващите онлайн планират покупка на Черен петък, а бюджетът е 528 евро на човек. Вижте данните и какво да направи магазинът ви.',
            ['Черен петък 2026 и какво показват данните за купувача'],
        ],
        'kak-da-izberete-domeyn-za-biznesa-si' => [
            'Избор на домейн за бизнеса, домейн .bg или .com',
            'Избор на домейн за бизнеса. Домейн .bg или .com, на чие име да е регистриран и как да не го загубите. Кратки правила на български.',
            [],
        ],
    ];

    /** @return int how many meta fields were filled */
    public static function apply(): int
    {
        $changed = 0;

        foreach (self::POSTS as $slug => [$title, $description, $oldTitles]) {
            $post = Post::where('slug->bg', $slug)->first();
            if (! $post) {
                continue;
            }

            $currentTitle = trim((string) $post->tr('meta_title', 'bg'));
            if ($currentTitle === '' || in_array($currentTitle, $oldTitles, true)) {
                $values = (array) $post->meta_title;
                $values['bg'] = $title;
                $post->meta_title = $values;
                $changed++;
            }

            if (trim((string) $post->tr('meta_description', 'bg')) === '') {
                $values = (array) $post->meta_description;
                $values['bg'] = $description;
                $post->meta_description = $values;
                $changed++;
            }

            if ($post->isDirty()) {
                // updated_at is the article's dateModified: a meta change is not a change of the article.
                $post->timestamps = false;
                $post->save();
            }
        }

        return $changed;
    }
}
