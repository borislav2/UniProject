<?php

namespace App\Support;

use App\Models\Post;

/**
 * GEO/SEO audit (25.11.2026): the title of the page and the description of some starter articles came from the article title and
 * the excerpt, and both hold a colon, which the owners' copy rule forbids in visible text and in titles and descriptions.
 * Gives these posts a plain meta title or meta description (no colons, no dashes), only where the field is still empty or holds exactly
 * the earlier value of ours listed here. A text the owners typed in „Блог“ is never changed, and the article title (the h1) is not touched.
 */
class PostPlainMeta2026
{
    /** slug => ['meta_title' => [new, earlier values of ours that may be replaced], 'meta_description' => [new, earlier values]] */
    public const POSTS = [
        'brandat-ne-zapochva-s-logo-pet-uroka-za-2027' => [
            'meta_title' => ['Брандът не започва с лого, пет урока за 2027', []],
        ],
        'posledniyat-klik-lazhe-kak-klientite-vi-namirat-predi-google' => [
            'meta_title' => ['Последният клик лъже, откъде идват клиентите ви', []],
            'meta_description' => ['Поръчката се записва на последния клик, но пътят започва другаде, в TikTok, Meta или разговор с ChatGPT. Как да четете отчетите, без да се заблуждавате.', []],
        ],
        'forma-za-zapitvane-greshki' => [
            'meta_title' => ['Шест грешки във формата за запитване, които губят клиенти', []],
        ],
        'imeyl-za-dobre-doshli' => [
            'meta_title' => ['Имейл за добре дошли, първото писмо към нов абонат', []],
        ],
        'otzivi-v-google' => [
            'meta_title' => ['Отзиви в Google, как да ги получавате и да отговаряте', []],
        ],
        'https-i-katinarcheto' => [
            'meta_title' => ['HTTPS и катинарчето, защо е важно за всеки сайт', []],
        ],
        'profil-v-google-business-kak-da-go-popalnite' => [
            'meta_title' => ['Профил в Google Business, как да го попълните правилно', []],
        ],
        'zashto-80-ot-ai-proektite-ne-stigat-do-vnedryavane' => [
            'meta_description' => ['Около 80% от AI проектите не стигат до внедряване. Тези, които работят, имат ясна спецификация, проверка и човек, който одобрява.', []],
        ],
        'avtentichno-video-bez-byudzhet-humor-istoriya-provokatsiya' => [
            'meta_description' => ['Видеа с над милион гледания без рекламен бюджет. Какво общо имат хуморът, историята, провокацията и коментарите и как да започнете с телефон.', []],
        ],
        'kak-da-razberete-dali-saytat-vi-nosi-klienti' => [
            'meta_description' => ['Без сложни отчети, четири прости начина да разберете дали сайтът работи за бизнеса ви, или просто съществува.', []],
        ],
    ];

    /** @return int how many meta fields were filled or replaced */
    public static function apply(): int
    {
        $changed = 0;

        foreach (self::POSTS as $slug => $fields) {
            $post = Post::where('slug->bg', $slug)->first();
            if (! $post) {
                continue;
            }
            foreach ($fields as $field => [$new, $earlier]) {
                $current = trim((string) $post->tr($field, 'bg'));
                if ($current === '' || in_array($current, $earlier, true)) {
                    $post->{$field} = array_merge((array) $post->{$field}, ['bg' => $new]);
                    $changed++;
                }
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
