<?php

namespace App\Support;

use App\Models\ContentBlock;

/**
 * Собствениците (ноември 2026): картите на /paketi са кратки (цена „от €400“, само годишният абонамент, до пет точки)
 * и стоят в горната част на страницата. Новото поле на пакета е `card_items` (кратък списък за картата).
 *
 * Админ редакциите на `plans` са запазени цели и не се презаписват: списъкът с пакети идва от последното записване в админа,
 * затова нови полета в config не се виждат в тях. Тук се добавя `card_items` само към пакет, който още няма такъв и
 * чието име е точно името по подразбиране на същото място (български и английски). Нищо друго не се пипа: цените, текстовете
 * и редакциите на собствениците остават. Без `card_items` картата пак е кратка: взима първите пет заглавия от `build_items`.
 * Всеки променен запис първо се копира в `plans:backup-2026-11-08-h` (съществуващо копие не се презаписва).
 */
class PackagesCards2026
{
    public const BACKUP = ':backup-2026-11-08-h';

    /** @return list<string> променените записи като „ключ/език“ */
    public static function apply(): array
    {
        $changed = [];

        foreach (ContentBlock::where('key', 'plans')->whereIn('locale', ['bg', 'en'])->get() as $block) {
            $defaults = (array) config($block->locale === 'en' ? 'creatium_en.plans.items' : 'creatium.plans.items');
            $value = self::transform((array) $block->value, $defaults);
            if ($value === $block->value) {
                continue;
            }

            ContentBlock::firstOrCreate(
                ['key' => 'plans' . self::BACKUP, 'locale' => $block->locale],
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

    /** Adds `card_items` to the packages that are still the default packages and have none. */
    public static function transform(array $plans, array $defaults): array
    {
        foreach (array_values((array) ($plans['items'] ?? [])) as $i => $item) {
            $default = $defaults[$i] ?? null;
            if (! is_array($item) || ! is_array($default) || ! isset($default['card_items'])) {
                continue;
            }
            if (($item['name'] ?? null) !== ($default['name'] ?? '') || ! empty($item['card_items'])) {
                continue;
            }
            $plans['items'][$i]['card_items'] = $default['card_items'];
        }

        return $plans;
    }
}
