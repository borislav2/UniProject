<?php

namespace App\Support;

use App\Models\ContentBlock;

/**
 * Екипът (13.11.2026): Мария Флорова е третият член. В config е добавена като трети запис, а тук се дописва към
 * запазените в админа редакции на `team` (всеки език), ако в тях още я няма. Съществуващите членове не се пипат.
 * Първо редът се копира в `team:backup-2026-11-13` (без да се презаписва съществуващо копие).
 */
class TeamMaria2026
{
    /** @return list<string> променените записи като „ключ/език“ */
    public static function apply(): array
    {
        $changed = [];

        foreach (ContentBlock::where('key', 'team')->get() as $block) {
            $team = (array) $block->value;
            if (self::hasMaria($team)) {
                continue;
            }

            $entry = self::entry((string) $block->locale);
            if ($entry === null) {
                continue;
            }

            ContentBlock::firstOrCreate(
                ['key' => 'team:backup-2026-11-13', 'locale' => $block->locale],
                ['value' => $block->value]
            );
            $team[] = $entry;
            $block->update(['value' => array_values($team)]);
            $changed[] = 'team/' . $block->locale;
        }

        if ($changed) {
            app(Content::class)->flush();
        }

        return $changed;
    }

    public static function hasMaria(array $team): bool
    {
        foreach ($team as $member) {
            $name = mb_strtolower((string) (is_array($member) ? ($member['name'] ?? '') : ''));
            if (str_contains($name, 'мария') || str_contains($name, 'maria')) {
                return true;
            }
        }

        return false;
    }

    /** Записът на Мария от config за дадения език (за en от creatium_en). */
    public static function entry(string $locale): ?array
    {
        $team = $locale === 'en' ? (array) config('creatium_en.team', []) : (array) config('creatium.team', []);
        if ($locale === 'en' && ! $team) {
            $team = (array) (require config_path('creatium_en.php'))['team'];
        }

        foreach ($team as $member) {
            if (self::hasMaria([$member])) {
                return $member;
            }
        }

        return null;
    }
}
