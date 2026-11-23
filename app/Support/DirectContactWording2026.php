<?php

namespace App\Support;

use App\Models\ContentBlock;

/**
 * Owners (23.11.2026): drop „Говорите директно с хората, които вършат работата“ and its siblings everywhere.
 * In saved admin overrides it replaces only whole strings that are exactly the old default (about.story,
 * home_trust.principle_items[].text) and removes the exact old home_problem.system_trust chip; the row is copied to
 * `<key>:backup-2026-11-23` first (never overwriting a backup).
 */
class DirectContactWording2026
{
    public const BACKUP = ':backup-2026-11-23';

    public const OLD = [
        'bg' => [
            'story' => 'Говорите директно с хората, които вършат работата. Цената е записана в офертата, а отговор получавате до един работен ден. Ние сме с вас по пътя на реализирането.',
            'chip' => 'Говорите директно с хората, които работят за вас',
            'principle' => 'Работите директно с хората, които правят сайта и маркетинга. Въпросите ви не минават през акаунт мениджъри.',
        ],
        'en' => [
            'story' => 'You talk directly to the people who do the work. The price is written in the offer, and you get a reply within one working day. We are with you all the way to results.',
            'chip' => 'You talk directly to the people who work for you',
            'principle' => 'You work directly with the people who build your website and marketing. Your questions don\'t go through account managers.',
        ],
    ];

    public static function apply(): array
    {
        $changed = [];
        foreach (ContentBlock::whereIn('key', ['about', 'home_problem', 'home_trust'])->get() as $block) {
            $old = self::OLD[$block->locale] ?? null;
            if (! $old) {
                continue;
            }
            $cfg = $block->locale === 'en' ? 'creatium_en' : 'creatium';
            $value = (array) $block->value;
            $new = self::transform($block->key, $value, $old, $cfg);
            if ($new === $value) {
                continue;
            }
            ContentBlock::firstOrCreate(['key' => $block->key . self::BACKUP, 'locale' => $block->locale], ['value' => $block->value]);
            $block->update(['value' => $new]);
            $changed[] = $block->key . '/' . $block->locale;
        }
        if ($changed) {
            app(Content::class)->flush();
        }

        return $changed;
    }

    public static function transform(string $key, array $value, array $old, string $cfg): array
    {
        if ($key === 'about' && is_array($value['story'] ?? null)) {
            foreach ($value['story'] as $i => $line) {
                if ($line === $old['story']) {
                    $value['story'][$i] = config("$cfg.about.story.3");
                }
            }
        }
        if ($key === 'home_problem' && is_array($value['system_trust'] ?? null)) {
            $kept = array_values(array_filter($value['system_trust'], fn ($chip) => ($chip['text'] ?? null) !== $old['chip']));
            if (count($kept) !== count($value['system_trust'])) {
                $value['system_trust'] = $kept;
            }
        }
        if ($key === 'home_trust' && is_array($value['principle_items'] ?? null)) {
            foreach ($value['principle_items'] as $i => $item) {
                if (($item['text'] ?? null) === $old['principle']) {
                    $value['principle_items'][$i]['text'] = config("$cfg.home_trust.principle_items.2.text");
                }
            }
        }

        return $value;
    }
}
