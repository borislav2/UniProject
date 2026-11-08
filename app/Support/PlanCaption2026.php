<?php

namespace App\Support;

use App\Models\ContentBlock;

/**
 * Собствениците (09.11.2026): „изграждането или поправката на сегашния ви сайт“ звучи зле.
 * Сменя само точния стар текст на цената на втория пакет в админ редакциите на `plans`;
 * преди това копира записа в `plans:backup-2026-11-09-a`.
 */
class PlanCaption2026
{
    public const MAP = [
        '€800 еднократно за изграждането или поправката на сегашния ви сайт и първия месец' => '€800 еднократно за нов или обновен сайт и първия месец',
        '€800 one-off to build or fix your current website and the first month' => '€800 one-off for a new or refreshed website and the first month',
    ];

    public static function apply(): array
    {
        $changed = [];

        foreach (ContentBlock::where('key', 'plans')->get() as $block) {
            $value = (array) $block->value;
            array_walk_recursive($value, function (&$item) {
                if (is_string($item) && isset(self::MAP[$item])) {
                    $item = self::MAP[$item];
                }
            });
            if ($value === $block->value) {
                continue;
            }

            ContentBlock::firstOrCreate(
                ['key' => 'plans:backup-2026-11-09-a', 'locale' => $block->locale],
                ['value' => $block->value]
            );
            $block->update(['value' => $value]);
            $changed[] = 'plans/' . $block->locale;
        }

        if ($changed) {
            app(Content::class)->flush();
        }

        return $changed;
    }
}
