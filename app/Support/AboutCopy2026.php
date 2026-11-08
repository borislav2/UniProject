<?php

namespace App\Support;

use App\Models\ContentBlock;

/**
 * Собствениците (08.11.2026): „За нас“ без „от 4 години“ и без текст като CV. Новите текстове са в config
 * (about.story и team[].description/bio/skills, написани с бизнес и маркетинг експерти). В админ редакциите
 * на `about` се сменя само `story`, а в `team` само description/bio/skills на всеки човек (по първото име);
 * името, ролята, снимката и LinkedIn остават. Всеки променен запис първо се копира в `<ключ>:backup-2026-11-08-e`.
 */
class AboutCopy2026
{
    /** @return list<string> променените записи като „ключ/език“ */
    public static function apply(): array
    {
        $changed = [];

        foreach (ContentBlock::whereIn('key', ['about', 'team'])->get() as $block) {
            $value = self::transform($block->key, (array) $block->value, $block->locale);
            if ($value === $block->value) {
                continue;
            }

            ContentBlock::firstOrCreate(
                ['key' => $block->key . ':backup-2026-11-08-e', 'locale' => $block->locale],
                ['value' => $block->value]
            );
            $block->update(['value' => $value]);
            $changed[] = $block->key . '/' . $block->locale;
        }

        if ($changed) {
            app(Content::class)->flush();
        }

        return $changed;
    }

    public static function transform(string $key, array $value, string $locale): array
    {
        $config = $locale === 'en' ? 'creatium_en' : 'creatium';

        if ($key === 'about') {
            $value['story'] = config($config . '.about.story');

            return $value;
        }

        $fresh = (array) config($config . '.team');
        foreach ($value as $i => $member) {
            if (! is_array($member)) {
                continue;
            }
            $first = mb_strtolower(strtok((string) ($member['name'] ?? ''), ' ') ?: '');
            foreach ($fresh as $new) {
                $newFirst = mb_strtolower(strtok((string) ($new['name'] ?? ''), ' ') ?: '');
                if ($first !== '' && $first === $newFirst) {
                    foreach (['description', 'bio', 'skills'] as $field) {
                        $value[$i][$field] = $new[$field];
                    }
                }
            }
        }

        return $value;
    }
}
