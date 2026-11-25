<?php

namespace App\Support;

use App\Models\Post;

/**
 * Screaming Frog pass (25.11.2026): page titles over 561 px (Arial 20px) and descriptions over 985 px (Arial 14px). Cyrillic is wide,
 * so a title of 55 Bulgarian characters can already be too long. Gives the starter posts whose title is too wide, or whose
 * meta text of ours (PostMetaTitles2026, PostPlainMeta2026) is too wide, a short search phrase title or description.
 * A field changes only when it is empty or still holds exactly the earlier text of ours listed here, so whatever the owners
 * typed in „Блог“ is never touched, and the article title (the h1) and updated_at stay as they are. No dashes, no colons.
 */
class SnippetLimits2026
{
    /**
     * slug (Bulgarian) => field => locale => [new text, earlier texts of ours that may be replaced].
     * An English field is filled only when the post has an English title.
     */
    public const POSTS = [
        'kolko-barzo-tryabva-da-se-zarezhda-saytat' => [
            'meta_title' => ['bg' => ['Колко бързо трябва да се зарежда сайтът', []]],
        ],
        'kakvo-e-geo' => [
            'meta_title' => [
                'bg' => ['Какво е GEO и защо AI отговаря вместо Google', ['Какво е GEO и защо AI асистентите отговарят вместо Google']],
                'en' => ['What is GEO and why AI answers instead of Google', []],
            ],
        ],
        'zashto-da-sabirate-imeyli-ot-klientite-si' => [
            'meta_title' => ['bg' => ['Защо да събирате имейли от клиентите си', []]],
        ],
        'kak-da-napishete-stranitsa-uslugi' => [
            'meta_title' => ['bg' => ['Как да напишете страница за услуги, която продава', []]],
        ],
        'forma-za-zapitvane-greshki' => [
            'meta_title' => ['bg' => ['Форма за запитване, шест грешки, които губят клиенти', ['Шест грешки във формата за запитване, които губят клиенти']]],
        ],
        'napomnyashti-imeyli' => [
            'meta_title' => ['bg' => ['Напомнящи имейли за клиенти, които не идват', ['Напомнящи имейли за клиенти, които не са идвали отдавна']]],
        ],
        'koga-iztichat-domeynat-i-hostingat' => [
            'meta_title' => [
                'bg' => ['Кога изтичат домейнът и хостингът на сайта', []],
                'en' => ['When your domain and hosting expire', []],
            ],
        ],
        'segmentirane-na-imeyl-spisak' => [
            'meta_title' => ['bg' => ['Сегментиране на имейл списък за малък бизнес', []]],
        ],
        'kak-da-vdignete-srednata-porachka' => [
            'meta_title' => ['en' => ['How to raise your average order without more ads', []]],
        ],
        'vtoriyat-tab-klientat-reshava-v-ai' => [
            'meta_title' => ['en' => ['The second tab where customers check you in AI', []]],
        ],
        'kakvo-da-podgotvite-predi-da-porachate-sayt' => [
            'meta_title' => ['en' => ['What to prepare before you order a website', []]],
        ],
        'posledniyat-klik-lazhe-kak-klientite-vi-namirat-predi-google' => [
            'meta_title' => ['en' => ['The last click lies, where customers find you first', []]],
            'meta_description' => ['bg' => [
                'Поръчката се записва на последния клик, но пътят започва другаде. Как да четете отчетите, без да се заблуждавате.',
                ['Поръчката се записва на последния клик, но пътят започва другаде, в TikTok, Meta или разговор с ChatGPT. Как да четете отчетите, без да се заблуждавате.'],
            ]],
        ],
        'avtentichno-video-bez-byudzhet-humor-istoriya-provokatsiya' => [
            'meta_description' => ['bg' => [
                'Видеа с над милион гледания без рекламен бюджет. Какво общо имат хуморът, историята и провокацията и как да започнете с телефон.',
                ['Видеа с над милион гледания без рекламен бюджет. Какво общо имат хуморът, историята, провокацията и коментарите и как да започнете с телефон.'],
            ]],
        ],
        'tema-na-imeyl-koyato-se-otvarya' => [
            'meta_title' => ['en' => ['Email subject lines that get opened', []]],
        ],
    ];

    /**
     * Posts whose title is long enough that " | Creatium Lab" does not fit, so the page title was the h1 word for word (Screaming Frog
     * "Page Titles Same as H1"). A search phrase variant, [bg, en]; filled only where the meta title is empty.
     */
    public const TITLES = [
        'kakvo-tryabva-da-ima-saytat-na-malak-biznes' => ['Сайт за малък бизнес, какво трябва да има', 'Small business website, what it needs to win customers'],
        'kakvo-da-proveryavate-v-sayta-si-vseki-mesets' => ['Проверка на сайта всеки месец, чек-лист', 'Monthly website checklist for small business'],
        'imeyl-za-dobre-doshli' => ['Имейл за добре дошли, какво да напишете', 'Welcome email for new subscribers, with examples'],
        'kak-da-izlizate-v-google-blizo-do-men' => ['Търсене близо до мен в Google, как да излизате', 'Near me searches in Google, how to appear on the map'],
        'napomnyashti-imeyli' => [null, 'Reminder emails that bring back customers'],
        'kak-da-razberete-dali-saytat-vi-nosi-klienti' => ['Дали сайтът носи клиенти, как да разберете', 'How to tell if your website brings customers'],
        'kakvo-da-podgotvite-predi-da-porachate-sayt' => ['Поръчка на сайт, какво да подготвите', null],
        'cheren-petak-2026-danni-za-balgarskiya-kupuvach' => [null, 'Black Friday 2026 in Bulgaria, the shopper data'],
        'gotov-li-e-saytat-vi-za-ai-asistentite' => ['Сайт за AI асистентите, готов ли е вашият', 'Website ready for AI assistants, five steps'],
        'kolko-struva-sait-za-malak-biznes-2026' => ['Цена на сайт за малък бизнес през 2026', 'Small business website cost in 2026'],
        'kak-chatgpt-izbira-koi-firmi-da-preporacha' => ['Как ChatGPT избира кои фирми да препоръча', 'How ChatGPT chooses which businesses to recommend'],
        'stranitsa-za-nas-na-koyato-klientite-vyarvat' => ['Страница За нас, на която клиентите вярват', 'About page that customers trust, how to write it'],
        'snimki-za-sayt-koito-karat-klientite-da-se-obadyat' => ['Снимки за сайт, които карат клиентите да звънят', 'Website photos that make customers call'],
        'tema-na-imeyl-koyato-se-otvarya' => ['Тема на имейл, която се отваря', null],
        'wordpress-wix-ili-sayt-po-porachka' => ['WordPress, Wix или сайт по поръчка', 'WordPress, Wix or a custom website for small business'],
        'zashto-google-ne-pokazva-sayta-mi' => ['Google не показва сайта ми, как да проверя', 'Google does not show my website, how to check'],
        'rezervni-kopia-na-sayta-kolko-chesto' => ['Резервни копия на сайта, колко често', 'Website backups, how often to make them'],
        'mestno-seo-za-biznes-s-nyakolko-obekta' => ['Местно SEO за няколко града или обекта', 'Local SEO for several cities or locations'],
    ];

    /** @return int how many meta fields were filled or replaced */
    public static function apply(): int
    {
        $changed = 0;

        $all = self::POSTS;
        foreach (self::TITLES as $slug => [$bg, $en]) {
            foreach (['bg' => $bg, 'en' => $en] as $locale => $text) {
                if ($text !== null && ! isset($all[$slug]['meta_title'][$locale])) {
                    $all[$slug]['meta_title'][$locale] = [$text, []];
                }
            }
        }

        foreach ($all as $slug => $fields) {
            $post = Post::where('slug->bg', $slug)->first();
            if (! $post) {
                continue;
            }

            foreach ($fields as $field => $locales) {
                foreach ($locales as $locale => [$text, $earlier]) {
                    if (trim((string) $post->tr('title', $locale)) === '') {
                        continue; // that language does not exist for this post
                    }

                    $current = trim((string) $post->tr($field, $locale));
                    if ($current === '' || in_array($current, $earlier, true)) {
                        $values = (array) $post->{$field};
                        $values[$locale] = $text;
                        $post->{$field} = $values;
                        $changed++;
                    }
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
