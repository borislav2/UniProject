<?php

namespace App\Support\Chat;

/**
 * Отговори без AI: когато няма ключ за AI (ANTHROPIC_API_KEY / MISTRAL_API_KEY), при грешка или над дневния лимит.
 * Търси най-близкия въпрос сред честите въпроси на сайта и няколко готови теми (кои сме, екипът, контакти,
 * консултация, проверка на сайт, примери, процес, оферта). Редките думи тежат повече от честите
 * („собствениците“ решава повече от „сайт“), така че и въпрос с една ключова дума намира отговор.
 */
class FaqResponder
{
    /** @return list<array{0: string, 1: string}> */
    public static function pairs(): array
    {
        $pairs = self::topics();
        foreach (site('questions') ?? [] as $item) {
            $pairs[] = [$item['q'], $item['a']];
        }
        foreach (site('service_pages') ?? [] as $page) {
            foreach ($page['faq'] ?? [] as [$q, $a]) {
                $pairs[] = [$q, $a];
            }
        }

        return $pairs;
    }

    /** Готовите теми: въпросът съдържа думите, с които хората питат, отговорът идва от съдържанието на сайта. */
    private static function topics(): array
    {
        $contact = config('creatium.contact');
        $about = app(\App\Support\Content::class)->withDefaults('about');
        $team = site('team') ?? [];
        $topics = [];

        if ($team) {
            $people = implode(__(' и '), array_map(fn ($p) => trim(($p['name'] ?? '') . ' (' . ($p['role'] ?? '') . ')'), $team));
            $topics[] = [
                __('Кои са собствениците, основателите, екипът и хората зад Creatium Lab? Кой стои зад вас, кой ще прави сайта, с кого ще работя, кои сте вие?'),
                __('Creatium Lab са :people. Те са основателите и сами правят работата, така че говорите директно с хората, които правят сайта и маркетинга.', ['people' => $people])
                    . (filled($team[0]['description'] ?? null) ? "\n" . implode("\n", array_map(fn ($p) => '– ' . ($p['name'] ?? $p['role']) . ': ' . ($p['description'] ?? ''), $team)) : '')
                    . "\n" . lroute('about'),
            ];
        }
        if (! empty($about['story'])) {
            $topics[] = [
                __('Откога работите, колко години опит имате, каква е историята ви и защо го правите?'),
                implode(' ', array_slice((array) $about['story'], 0, 3)) . "\n" . lroute('about'),
            ];
        }
        $topics[] = [
            __('Какъв е телефонът, имейлът и адресът ви? Как да се свържа с вас, да ви се обадя или пиша? Къде се намирате, в кой град работите?'),
            __('Имейл: :email. Телефон: :phones. Намираме се в :city и работим с бизнеси от цяла България.', ['email' => $contact['email'], 'phones' => implode(', ', $contact['phones']), 'city' => __($contact['city'])]),
        ];
        $topics[] = [
            __('Може ли безплатна консултация, среща или разговор? Как да запазя час?'),
            __('Да, консултацията е безплатна, около :minutes минути. Изберете ден и час тук: :link', ['minutes' => config('creatium.booking.minutes'), 'link' => lroute('contact') . '#konsultatsiya']),
        ];
        $topics[] = [
            __('Можете ли да проверите моя сайт? Безплатна проверка, анализ или одит на сайта.'),
            __('Да. Въведете адреса на сайта си и имейл и до минута ще получите отчет какво да оправите: :link', ['link' => lroute('site-check')]),
        ];
        $demos = site('demos') ?? [];
        $topics[] = [
            __('Имате ли примери, портфолио, демо сайтове или проекти, които сте правили?'),
            __('Вижте нашите проекти и демо сайтовете за различни браншове:') . ' ' . implode(', ', array_map(fn ($d) => $d['name'] . ' (' . $d['industry'] . ')', $demos)) . '. ' . lroute('portfolio'),
        ];
        $steps = site('home_process')['steps'] ?? [];
        if ($steps) {
            $topics[] = [
                __('Как работите, какъв е процесът и стъпките?'),
                implode("\n", array_map(fn ($s, $i) => ($i + 1) . '. ' . $s['title'] . ': ' . $s['text'], $steps, array_keys($steps))),
            ];
        }
        // Сайт за конкретен бранш (салон, ресторант, автосервиз…): страницата на бранша и подходящият пакет. Само на български.
        if (app()->getLocale() === 'bg') {
            $demoBySlug = collect($demos)->keyBy('slug');
            foreach (config('creatium.industry_pages', []) as $slug => $page) {
                $demo = $demoBySlug[$page['demo']] ?? [];
                $plan = $demo['package']['name'] ?? null;
                $topics[] = [
                    $page['title'] . ', сайт за ' . mb_strtolower($page['menu']) . ' ' . ($demo['industry'] ?? ''),
                    $page['lead'] . ($plan ? ' Подходящ пакет: ' . $plan . '. ' . ($demo['package']['why'] ?? '') : '') . "\n" . route('industry', $slug),
                ];
            }
        }
        $topics[] = [
            __('Искам нов сайт, изработка на сайт, уебсайт, да започнем.'),
            __('Чудесно! Разкажете ни накратко за бизнеса си и ще ви изпратим оферта до един работен ден: :link Или напишете тук телефон или имейл и ще ви се обадим.', ['link' => lroute('packages') . '#oferta']),
        ];
        $plans = site('plans')['items'] ?? [];
        if ($plans) {
            $topics[] = [
                __('Колко струва сайт? Цени на пакетите, такса на месец, абонамент, оферта.'),
                implode("\n", array_map(fn ($p) => $p['name'] . ': ' . implode(', ', (array) $p['prices']), $plans))
                    . "\n" . __('Оферта за вашия бизнес: :link', ['link' => lroute('packages') . '#oferta']),
            ];
        }

        return $topics;
    }

    public static function answer(string $text): string
    {
        if ($reply = self::smallTalk($text)) {
            return $reply;
        }

        $pairs = self::pairs();
        $questionStems = array_map(fn ($pair) => self::stems($pair[0]), $pairs);
        // Колко въпроса съдържат дадена дума: редките думи тежат повече.
        $df = array_count_values(array_merge(...array_map('array_values', $questionStems ?: [[]])));
        $total = max(1, count($pairs));

        $words = self::stems($text);
        $best = null;
        $bestScore = 0.0;
        foreach ($pairs as $i => [, $a]) {
            $matched = array_intersect($words, $questionStems[$i]);
            if (! $matched) {
                continue;
            }
            $rare = count(array_filter($matched, fn ($w) => $df[$w] <= 2));
            if (count($matched) < 2 && $rare === 0) {
                continue;
            }
            $score = array_sum(array_map(fn ($w) => log(1 + $total / $df[$w]), $matched));
            if ($score > $bestScore) {
                [$best, $bestScore] = [$a, $score];
            }
        }

        if ($best !== null) {
            return $best . "\n\n" . __('Ако искате да говорим за вашия бизнес, напишете телефон или имейл и ще ви се обадим до един работен ден.');
        }

        return __('Мога да отговоря на въпроси за услугите, цените, сроковете и екипа ни. Ако искате да говорите с нас, напишете телефон или имейл и ще ви се обадим до един работен ден, или изберете час за безплатна консултация: :link', ['link' => lroute('contact') . '#konsultatsiya']);
    }

    /** Поздрав и благодарност: кратък човешки отговор вместо шаблона „мога да отговоря на…“. */
    private static function smallTalk(string $text): ?string
    {
        $clean = mb_strtolower(trim($text));
        if (mb_strlen($clean) > 40) {
            return null;
        }
        if (preg_match('/^(здравей|здрасти|здравейте|добър ден|добро утро|добър вечер|hello|hi|hey|good (morning|afternoon|evening))[\s,.!]*(как сте|how are you)?[\s?!.]*$/u', $clean)) {
            return __('Здравейте! Аз съм Крео от Creatium Lab. Мога да ви кажа за услугите, цените и екипа ни или да ви помогна да изберете пакет. С какъв бизнес сте?');
        }
        if (preg_match('/^(благодаря|мерси|thanks|thank you|супер|чудесно)[\s,.!]*(ви|много|a lot)?[\s!.]*$/u', $clean)) {
            return __('Моля! Ако имате още въпроси, питайте. Ако искате да ви се обадим, напишете телефон или имейл.');
        }

        return null;
    }

    /** Думите над 3 букви, съкратени до първите 5, за грубо съвпадение на форми („сайт“, „сайта“, „сайтове“). */
    private static function stems(string $text): array
    {
        preg_match_all('/[\p{L}\p{N}]{4,}/u', mb_strtolower($text), $m);

        return array_values(array_unique(array_map(fn ($w) => mb_substr($w, 0, 5), $m[0])));
    }

    /** Телефон или имейл в свободен текст, за да се запише запитване и без AI. */
    public static function contactIn(string $text): ?array
    {
        $email = preg_match('/[^\s@]+@[^\s@]+\.[^\s@]+/u', $text, $m) ? rtrim($m[0], '.,;') : null;
        $phone = preg_match('/(?:\+359|0)[\s\-]?\d(?:[\s\-]?\d){7,9}/', $text, $p) ? $p[0] : null;

        return ($email || $phone) ? ['phone' => $phone, 'email' => $email] : null;
    }
}
