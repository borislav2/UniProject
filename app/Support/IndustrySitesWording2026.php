<?php

namespace App\Support;

use App\Models\ContentBlock;

/**
 * Собствениците (ноември 2026): сайтовете по браншове на /proekti вече не носят етикет „Концепция“ и никъде не се наричат
 * концепции или модели. Сменя в админ редакциите на `home_trust` само точните стари текстове (цели низове), които са
 * сменени и в config; всеки променен запис първо се копира в `<ключ>:backup-2026-11-11`.
 */
class IndustrySitesWording2026
{
    public const KEYS = ['home_trust'];

    public const REPLACE = [
        'Разгледайте част от нашите персонализирани концепции за различни индустрии. Всеки проект е изграден с фокус върху бързината, визията и високата конверсия.'
            => 'Разгледайте част от нашите персонализирани решения за различни индустрии. Всеки проект е изграден с фокус върху бързината, визията и високата конверсия.',
        'Готови концепции и работещи модели за конкретни бизнес ниши' => 'Готови решения и работещи сайтове за конкретни бизнес ниши',
        'Разработихме концепции за ключови сектори. Всяка от тях адресира специфичните предизвикателства на бранша, пътя на купувача и необходимите интеграции за генериране на запитвания.'
            => 'Разработихме сайтове за ключови сектори. Всеки от тях адресира специфичните предизвикателства на бранша, пътя на купувача и необходимите интеграции за генериране на запитвания.',
        'Концепции за сектори' => 'Сайта за сектори',
        'Разгледайте интерактивните модели, за да видите как структурата, дизайнът и фунията работят в реални условия за вашия сектор.'
            => 'Разгледайте сайтовете, за да видите как структурата, дизайнът и фунията работят в реални условия за вашия сектор.',
        'Explore some of our custom concepts for different industries. Every project is built with a focus on speed, design and high conversion.'
            => 'Explore some of our custom solutions for different industries. Every project is built with a focus on speed, design and high conversion.',
        'Ready-made concepts and working models for specific business niches' => 'Ready-made solutions and working websites for specific business niches',
        'We developed concepts for key sectors. Each of them addresses the specific challenges of the industry, the buyer\'s journey and the integrations needed to generate enquiries.'
            => 'We developed websites for key sectors. Each of them addresses the specific challenges of the industry, the buyer\'s journey and the integrations needed to generate enquiries.',
        'Concepts for sectors' => 'Websites for sectors',
        'Explore the interactive models to see how the structure, the design and the funnel work in real conditions for your sector.'
            => 'Explore the websites to see how the structure, the design and the funnel work in real conditions for your sector.',
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
                ['key' => $block->key . ':backup-2026-11-11', 'locale' => $block->locale],
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
