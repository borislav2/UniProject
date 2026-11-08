<?php

namespace App\Support;

use App\Models\ContentBlock;

/**
 * Собствениците (ноември 2026): примерните сайтове вече не се наричат „демо“ и не пише „измислени“; има само малък етикет
 * „Концепция“. Сменя в админ редакциите на `demos`, `home_trust` и `questions` само точните стари текстове (цели низове),
 * които са сменени и в config; всеки променен запис първо се копира в `<ключ>:backup-2026-11-08-d`.
 */
class ConceptWording2026
{
    public const KEYS = ['demos', 'home_trust', 'questions'];

    public const REPLACE = [
        'Разработихме демонстрационни платформи за ключови сектори. Всяка от тях адресира специфичните предизвикателства на бранша, пътя на купувача и необходимите интеграции за генериране на запитвания.'
            => 'Разработихме концепции за ключови сектори. Всяка от тях адресира специфичните предизвикателства на бранша, пътя на купувача и необходимите интеграции за генериране на запитвания.',
        'Демонстрационни платформи' => 'Концепции за сектори',
        'We developed demonstration platforms for key sectors. Each of them addresses the specific challenges of the industry, the buyer\'s journey and the integrations needed to generate enquiries.'
            => 'We developed concepts for key sectors. Each of them addresses the specific challenges of the industry, the buyer\'s journey and the integrations needed to generate enquiries.',
        'Demonstration platforms' => 'Concepts for sectors',
        'В демото има форма за заявка за маса: гостът изпраща ден, час и брой хора, а вие потвърждавате по телефона. Ако използвате система за резервации, можем да сложим връзка към нея.'
            => 'В концепцията има форма за заявка за маса: гостът изпраща ден, час и брой хора, а вие потвърждавате по телефона. Ако използвате система за резервации, можем да сложим връзка към нея.',
        'В демото има форма за заявка: клиентката избира процедура и удобен ден, а вие потвърждавате. Ако използвате платформа за записване, слагаме връзка към нея.'
            => 'В концепцията има форма за заявка: клиентката избира процедура и удобен ден, а вие потвърждавате. Ако използвате платформа за записване, слагаме връзка към нея.',
        'Да. Демото е пример. Вашият сайт е във вашите цветове, с вашето лого и вашите снимки.'
            => 'Да. Концепцията е пример. Вашият сайт е във вашите цветове, с вашето лого и вашите снимки.',
        'В демото има форма за запитване: клиентът изпраща колата и проблема, а вие му се обаждате с удобен час.'
            => 'В концепцията има форма за запитване: клиентът изпраща колата и проблема, а вие му се обаждате с удобен час.',
        'Колкото ви трябват. Демото е малък магазин, но каталогът може да расте.'
            => 'Колкото ви трябват. Концепцията е малък магазин, но каталогът може да расте.',
    ];

    /** @return list<string> променените записи като „ключ/език“ */
    public static function apply(): array
    {
        $changed = [];

        foreach (ContentBlock::whereIn('key', self::KEYS)->get() as $block) {
            $value = self::transform((array) $block->value);
            if ($value === $block->value) {
                continue;
            }

            ContentBlock::firstOrCreate(
                ['key' => $block->key . ':backup-2026-11-08-d', 'locale' => $block->locale],
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

    /** Сменя само низове, които са точно стар текст. */
    public static function transform(array $value): array
    {
        array_walk_recursive($value, function (&$item) {
            if (is_string($item) && isset(self::REPLACE[$item])) {
                $item = self::REPLACE[$item];
            }
        });

        return $value;
    }
}
