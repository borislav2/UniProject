<?php

namespace App\Support;

use App\Models\ContentBlock;

/**
 * Собствениците (08.11.2026): бутонът в началото на страницата води към офертата, не към калкулатора.
 * В админ редакцията на `home_hero` сменя само текста на бутона, ако още е старият по подразбиране;
 * преди това копира записа в `home_hero:backup-2026-11-08-f`.
 */
class HeroOfferButton2026
{
    public const OLD = [
        'bg' => ['Изчислете ползата за бизнеса си', 'Изчислете ползата'],
        'en' => ['Calculate the benefit for your business', 'Calculate the benefit'],
    ];

    public static function apply(): array
    {
        $changed = [];

        foreach (ContentBlock::where('key', 'home_hero')->get() as $block) {
            $value = (array) $block->value;
            if (! in_array($value['button'] ?? null, self::OLD[$block->locale] ?? [], true)) {
                continue;
            }

            ContentBlock::firstOrCreate(
                ['key' => 'home_hero:backup-2026-11-08-f', 'locale' => $block->locale],
                ['value' => $block->value]
            );
            $value['button'] = config(($block->locale === 'en' ? 'creatium_en' : 'creatium') . '.home_hero.button');
            $block->update(['value' => $value]);
            $changed[] = 'home_hero/' . $block->locale;
        }

        if ($changed) {
            app(Content::class)->flush();
        }

        return $changed;
    }
}
