<?php

namespace App\Support;

/**
 * Helpers for the packages (`plans` content block).
 */
class Plans
{
    /** Old plan names that still match the current plan (the third plan was „Индивидуален маркетинг и мащабиране“ until November 2026). */
    private const ALIASES = [
        'индивидуален маркетинг и мащабиране' => 'индивидуален маркетинг',
        'custom marketing and scaling' => 'custom marketing',
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
