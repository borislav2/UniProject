<?php

namespace App\Support;

use App\Models\ContentBlock;

/**
 * LinkedIn на Мария Флорова (17.11.2026). Запазените редакции на `team` имат нейния запис с празен `linkedin`
 * (дописан от TeamMaria2026). Тук се попълва само празно поле на член с име Мария/Maria; попълнен от админа
 * линк не се пипа. Редът първо се копира в `team:backup-2026-11-17` (съществуващо копие не се презаписва).
 */
class TeamMariaLinkedin2026
{
    public const URL = 'https://www.linkedin.com/in/maria-florova/';

    /** @return list<string> променените записи като „ключ/език“ */
    public static function apply(): array
    {
        $changed = [];

        foreach (ContentBlock::where('key', 'team')->get() as $block) {
            $team = is_array($block->value) ? $block->value : [];
            $touched = false;

            foreach ($team as $i => $member) {
                if (! is_array($member) || ! preg_match('/мария|maria/iu', (string) ($member['name'] ?? ''))) {
                    continue;
                }
                if (trim((string) ($member['linkedin'] ?? '')) === '') {
                    $team[$i]['linkedin'] = self::URL;
                    $touched = true;
                }
            }

            if (! $touched) {
                continue;
            }

            ContentBlock::firstOrCreate(
                ['key' => 'team:backup-2026-11-17', 'locale' => $block->locale],
                ['value' => $block->value]
            );
            $block->value = $team;
            $block->save();
            $changed[] = 'team/' . $block->locale;
        }

        if ($changed) {
            app(Content::class)->flush();
        }

        return $changed;
    }
}
