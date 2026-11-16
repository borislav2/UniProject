<?php

namespace App\Support;

use App\Models\Post;

/**
 * SEO одит (16.11.2026): заглавията на някои от първите статии са над 60 знака и Google ги реже в резултатите.
 * Слага кратко мета заглавие (само за раздела <title> и споделянето, заглавието на статията не се пипа) на статиите,
 * които още нямат собствено. Каквото собствениците са въвели в „Блог“, не се променя. Без двоеточия и тирета.
 */
class PostMetaTitles2026
{
    public const MAP = [
        'meta-zaglavie-i-opisanie' => 'Мета заглавие и описание за по-добри резултати в Google',
        'kakvo-e-geo' => 'Какво е GEO и защо AI асистентите отговарят вместо Google',
        'napomnyashti-imeyli' => 'Напомнящи имейли за клиенти, които не са идвали отдавна',
        'cheren-petak-2026-danni-za-balgarskiya-kupuvach' => 'Черен петък 2026 и какво показват данните за купувача',
        '4-avtomatichni-imeyla-za-onlayn-magazin' => '4 автоматични имейла за всеки онлайн магазин',
        'kak-da-vdignete-srednata-porachka' => 'Как да вдигнете средната поръчка без повече реклама',
        'vtoriyat-tab-klientat-reshava-v-ai' => 'Вторият таб, в който клиентът ви решава с AI',
        'google-shopping-idva-v-balgaria-podgotovka-na-magazina' => 'Google Shopping идва в България, подгответе магазина',
        'avtentichno-video-bez-byudzhet-humor-istoriya-provokatsiya' => 'Автентично видео без бюджет, с хумор и история',
        'zashto-80-ot-ai-proektite-ne-stigat-do-vnedryavane' => 'Защо 80% от AI проектите не стигат до внедряване',
    ];

    /** @return int how many posts got a meta title */
    public static function apply(): int
    {
        $changed = 0;

        foreach (self::MAP as $slug => $metaTitle) {
            $post = Post::where('slug->bg', $slug)->first();
            if (! $post || filled($post->tr('meta_title', 'bg'))) {
                continue;
            }
            $values = (array) $post->meta_title;
            $values['bg'] = $metaTitle;
            $post->meta_title = $values;
            // updated_at is the article's dateModified in the structured data: a meta title is not a change of the article.
            $post->timestamps = false;
            $post->save();
            $changed++;
        }

        return $changed;
    }
}
