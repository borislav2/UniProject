<?php

namespace App\Support\Chat;

/**
 * Отговори без AI: когато няма ANTHROPIC_API_KEY, при грешка или над дневния лимит.
 * Търси най-близкия въпрос в честите въпроси на сайта (За нас + страниците на услугите) по общи думи.
 */
class FaqResponder
{
    /** @return list<array{0: string, 1: string}> */
    public static function pairs(): array
    {
        $pairs = [];
        foreach (site('questions') ?? [] as $item) {
            $pairs[] = [$item['q'], $item['a']];
        }
        foreach (site('service_pages') ?? [] as $page) {
            foreach ($page['faq'] ?? [] as [$q, $a]) {
                $pairs[] = [$q, $a];
            }
        }
        $plans = site('plans')['items'] ?? [];
        if ($plans) {
            $pairs[] = [__('Колко струва сайт? Цени на пакетите'), implode("\n", array_map(fn ($p) => $p['name'] . ': ' . implode(', ', (array) $p['prices']), $plans)) . "\n" . lroute('packages')];
        }

        return $pairs;
    }

    public static function answer(string $text): string
    {
        $words = self::stems($text);
        $best = null;
        $bestScore = 0;
        foreach (self::pairs() as [$q, $a]) {
            $score = count(array_intersect($words, self::stems($q)));
            if ($score > $bestScore) {
                [$best, $bestScore] = [$a, $score];
            }
        }

        if ($best !== null && $bestScore >= 2) {
            return $best . "\n\n" . __('Ако искате да говорим за вашия бизнес, напишете телефон или имейл и ще ви се обадим до един работен ден.');
        }

        return __('Мога да отговоря на въпроси за услугите, цените и сроковете ни. Ако искате да говорите с нас, напишете телефон или имейл и ще ви се обадим до един работен ден, или изберете час за безплатна консултация: :link', ['link' => lroute('contact') . '#konsultatsiya']);
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
