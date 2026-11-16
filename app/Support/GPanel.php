<?php

namespace App\Support;

/**
 * The interactive panel of the services page in design G (third round, 16.11.2026): seven switches, the share of active ones and exactly one message.
 * The numbers are the owners' (1 Google Business Profile, 2 SEO and AI visibility, 3 ads, 4 brand, 5 fast mobile site, 6 a clear path to contact, 7 automatic emails).
 * The browser repeats these rules in resources/js/g-bar.js; the first screen is rendered here, so the page also works without JavaScript.
 */
class GPanel
{
    public const COUNT = 7;

    /** The switches that are on when the page loads (1 and 5, so it is clear how the panel works). */
    public const DEFAULT = [1, 5];

    /** The quick buttons: the switches each one turns on. */
    public const PRESETS = ['start' => [1, 5], 'ads' => [1, 3], 'full' => [1, 2, 3, 4, 5, 6, 7]];

    /** Where a missing switch leads in the contact form (`contact_directions_g` key); null = no matching direction. */
    public const DIRECTIONS = [1 => 'google', 2 => 'seo', 3 => 'ads', 4 => null, 5 => 'website', 6 => 'website', 7 => 'email'];

    /** The message rules in the order they are tried; the first that fits wins. */
    public const RULE_ORDER = ['full', 'traffic', 'base', 'low', 'mixed'];

    /** @param list<int> $on the numbers (1 to 7) of the active switches */
    public static function state(array $on): array
    {
        $on = array_values(array_unique(array_filter(array_map('intval', $on), fn ($n) => $n >= 1 && $n <= self::COUNT)));
        sort($on);
        $count = count($on);
        $missing = array_values(array_diff(range(1, self::COUNT), $on));

        return [
            'on' => $on,
            'count' => $count,
            'percent' => intdiv($count * 100, self::COUNT),
            'message' => self::message($on),
            'missing' => $missing,
            'directions' => self::directions($missing),
            'preset' => self::presetFor($on),
        ];
    }

    /**
     * Which of the five messages shows:
     * 1. full: 6 or 7 active;
     * 2. traffic: ads or SEO (2 or 3) without the base (4, 5 or 6 missing);
     * 3. base: the base (4, 5 and 6) without any traffic (1, 2 and 3 all off);
     * 4. low: 0 to 2 active;
     * 5. mixed: everything else (3 to 5 active).
     * The order matters: "Business with ads" (1 and 3) has only 2 active but must show the traffic message, not the low one.
     */
    public static function message(array $on): string
    {
        $count = count($on);
        $has = fn (int $n) => in_array($n, $on, true);

        return match (true) {
            $count >= 6 => 'full',
            ($has(2) || $has(3)) && ! ($has(4) && $has(5) && $has(6)) => 'traffic',
            $has(4) && $has(5) && $has(6) && ! $has(1) && ! $has(2) && ! $has(3) => 'base',
            $count <= 2 => 'low',
            default => 'mixed',
        };
    }

    /** The contact form directions for the missing switches, in the order of the form, without repeats. */
    public static function directions(array $missing): array
    {
        $wanted = array_filter(array_map(fn ($n) => self::DIRECTIONS[$n] ?? null, $missing));

        return array_values(array_filter(array_keys((array) config('creatium.contact_directions_g')), fn ($key) => in_array($key, $wanted, true)));
    }

    /** The preset whose switches are exactly the active ones, if any. */
    public static function presetFor(array $on): ?string
    {
        foreach (self::PRESETS as $key => $numbers) {
            if ($numbers === array_values($on)) {
                return $key;
            }
        }

        return null;
    }

    /** The link of the panel button: the contact form with the missing directions ticked. */
    public static function contactUrl(array $directions): string
    {
        return lroute('contact') . ($directions ? '?napravleniya=' . implode(',', $directions) : '') . '#kontakt';
    }

    /** The panel texts: the saved ones over the config defaults (a missing key never breaks the page). */
    public static function texts(): array
    {
        $default = (array) (app(Content::class)->default('services_page', app()->getLocale())['g_panel'] ?? []);
        $saved = (array) (app(Content::class)->withDefaults('services_page')['g_panel'] ?? []);
        $texts = array_replace($default, array_filter($saved, 'filled'));
        $switches = array_values((array) ($texts['switches'] ?? []));
        $texts['switches'] = array_slice(array_replace(array_values((array) ($default['switches'] ?? [])), $switches), 0, self::COUNT);

        return $texts;
    }
}
