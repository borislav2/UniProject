<?php

namespace App\Support;

/**
 * Helpers for the packages (`plans` content block).
 */
class Plans
{
    /**
     * Old plan names that still match the current plan: until November 2026 the plans were „Стартов двигател“ / „Активен маркетинг и растеж“ /
     * „Индивидуален маркетинг“ (before that „Индивидуален маркетинг и мащабиране“), and since 17.11.2026 they are „Core Изграждане“, „Pro Ускорение“
     * and „Enterprise Партньорство“ (English „Core Build“, „Pro Accelerate“, „Enterprise Partnership“). Old leads, offers and saved projects keep the old text.
     */
    private const ALIASES = [
        'стартов двигател' => 'core изграждане',
        'активен маркетинг и растеж' => 'pro ускорение',
        'активен маркетинг' => 'pro ускорение',
        'индивидуален маркетинг и мащабиране' => 'enterprise партньорство',
        'индивидуален маркетинг' => 'enterprise партньорство',
        'starter engine' => 'core build',
        'active marketing and growth' => 'pro accelerate',
        'active marketing' => 'pro accelerate',
        'custom marketing and scaling' => 'enterprise partnership',
        'custom marketing' => 'enterprise partnership',
    ];

    /** A plan name in a comparable form: lower case, "&" = "и", single spaces, old names mapped to the current one. */
    public static function normalize(?string $name): string
    {
        $name = str_replace(['&amp;', '&'], ' и ', (string) $name);
        $name = trim((string) preg_replace('/\s+/u', ' ', mb_strtolower($name)));

        return self::ALIASES[$name] ?? $name;
    }

    /** The plan with this name (case, "&" / "и" and spacing do not matter), or null. */
    public static function find(array $items, ?string $name): ?array
    {
        if (! filled($name)) {
            return null;
        }

        $wanted = self::normalize($name);
        foreach ($items as $plan) {
            if (self::normalize($plan['name'] ?? null) === $wanted) {
                return $plan;
            }
        }

        return null;
    }

    /** FAQ entries for the packages page: whole topics from `faq_groups` plus single questions from `faq_questions`. */
    public static function faq(array $plans, array $questions): array
    {
        $groups = array_filter((array) ($plans['faq_groups'] ?? []), 'filled');
        $texts = array_filter((array) ($plans['faq_questions'] ?? []), 'filled');

        return array_values(array_filter($questions, fn ($item) => in_array($item['group'] ?? null, $groups, true) || in_array($item['q'] ?? null, $texts, true)));
    }
}
