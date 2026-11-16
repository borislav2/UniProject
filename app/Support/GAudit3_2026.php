<?php

namespace App\Support;

use App\Models\ContentBlock;

/**
 * Дизайн Г, трети кръг (16.11.2026, третият одит на собствениците): няколко `g_` текста по подразбиране са сменени в config
 * и екипът получава нови `g_*` полета. Тук се пренасят в админ редакциите само точните стари текстове (цели низове или цели списъци);
 * всичко друго остава. Всеки променен запис първо се копира в `<ключ>:backup-2026-11-16` (без да се презаписва съществуващо копие).
 * Новите ключове, които Content::withDefaults показва и при по-стара редакция (services_page.g_title, g_text, g_panel), не се пипат.
 * Два случая са отделни: имената на пакетите на Г получават и `tag` (само когато името е било точно старото), а членовете на екипа
 * получават само липсващите g_role, g_description, g_bio и g_skills, намерени по първото име, без да се променя друго поле.
 */
class GAudit3_2026
{
    public const BACKUP = 'backup-2026-11-16';

    /** Етикетът над името на пакета, добавен заедно със смяната на името. */
    public const PLAN_TAGS = ['CORE', 'PRO', 'ENTERPRISE'];

    /** Новите g_* полета на екипа, които се добавят, когато липсват. */
    public const TEAM_FIELDS = ['g_role', 'g_description', 'g_bio', 'g_skills'];

    /** ключ в content_blocks => [път => [старо => ново]] (цели низове) */
    public const CHANGES = [
        'home_trust' => [
            'g_goal_text' => [
                'Искаме да подкрепим всеки малък, среден и голям бизнес в България и в чужбина и истински да помогнем на всеки, който има нужда от дигитално присъствие. Работим като агенция, която влиза в детайла, говори ясно и остава до вас и след пускането на сайта. Започваме с разговор и с ясен план за следващите стъпки.'
                    => 'Искаме да подкрепим всеки малък, среден и голям бизнес в България и в чужбина и истински да помогнем на всеки, който има нужда от дигитално присъствие. Работим като агенция, която вниква в детайла, говори ясно и остава до вас.',
                'We want to support every small, medium and large business in Bulgaria and abroad, and to truly help everyone who needs a digital presence. We work as an agency that goes into the detail, speaks plainly and stays with you after the site goes live. We start with a conversation and a clear plan for the next steps.'
                    => 'We want to support every small, medium and large business in Bulgaria and abroad, and to truly help everyone who needs a digital presence. We work as an agency that digs into the detail, speaks plainly and stays by your side.',
            ],
        ],
        'about' => [
            'g_standards_title' => [
                'Нашите стандарти' => 'Защо го правим по този начин',
                'Our standards' => 'Why we do it this way',
            ],
        ],
        'plans' => [
            'g_items.0.name' => [
                'Дигитален старт' => 'Изграждане',
                'Digital Start' => 'Build',
            ],
            'g_items.1.name' => [
                'Активен маркетинг' => 'Ускорение',
                'Active Marketing' => 'Accelerate',
            ],
            'g_items.2.name' => [
                'Стратегически маркетинг' => 'Партньорство',
                'Strategic Marketing' => 'Partnership',
            ],
        ],
    ];

    /** ключ => път => [[стар списък, нов списък], ...]: целият списък се сменя само когато е точно старият */
    public const LISTS = [
        'about' => [
            'g_intro' => [
                [
                    [
                        'Creatium Lab е дигитална агенция за бизнеси в България. Свързваме бранда, сайта, видимостта в Google и AI асистентите, рекламите и имейлите в една система, която води до запитвания.',
                        'Работим по ясен метод и с фиксирана цена, записана в офертата. Всеки месец получавате отчет какво е свършено и какво следва.',
                    ],
                    [
                        'Създадохме Creatium Lab с мисията да подпомагаме компании в България и чужбина да изградят дигитално присъствие по съвременни стандарти, а не по остарели шаблони. Израснахме с технологиите и виждаме колко бързо се променя начинът, по който хората търсят и купуват. Затова свързваме бранда, модерния и бърз сайт, видимостта в Google и AI търсачките, рекламите и автоматизациите в един работещ механизъм.',
                        'Съчетаваме скоростта и глада за резултати на новото поколение с високи стандарти на работа. Получавате ясен план, редовни отчети и директна комуникация с нас на всеки етап от развитието на бизнеса ви.',
                    ],
                ],
                [
                    [
                        'Creatium Lab is a digital agency for businesses in Bulgaria. We connect the brand, the website, visibility in Google and AI assistants, ads and emails into one system that leads to enquiries.',
                        'We work with a clear method and a fixed price written into the offer. Every month you get a report on what was done and what comes next.',
                    ],
                    [
                        'We founded Creatium Lab with the mission to help companies in Bulgaria and abroad build a digital presence to modern standards, not from outdated templates. We grew up with technology and we see how fast the way people search and buy is changing. That is why we connect the brand, a modern and fast website, visibility in Google and AI search, ads and automations into one working mechanism.',
                        'We combine the speed and hunger for results of the new generation with high standards of work. You get a clear plan, regular reports and direct communication with us at every stage of your business growth.',
                    ],
                ],
            ],
            'g_standards' => [
                [
                    [
                        ['title' => 'Фиксирана цена', 'text' => 'Цената е записана в офертата и я знаете, преди да започнем.'],
                        ['title' => 'Отговор до един работен ден', 'text' => 'На всяко запитване отговаряме най-късно до един работен ден.'],
                        ['title' => 'Прозрачен отчет', 'text' => 'Всеки месец получавате ясен отчет за свършеното и за следващите стъпки.'],
                        ['title' => 'Директна връзка', 'text' => 'Говорите директно с хората, които вършат работата.'],
                    ],
                    [
                        ['title' => 'Не губим време в излишни приказки', 'text' => 'Докато другите агенции правят безкрайни презентации и бавят процесите със седмици, ние предпочитаме да действаме. Изграждаме, тестваме в реална среда, гледаме числата и подобряваме това, което работи.'],
                        ['title' => 'Играем по новите правила на интернет', 'text' => 'Начинът, по който хората търсят и избират фирми днес, няма нищо общо с този отпреди пет години. Вместо да разчитаме на остарели похвати, използваме най-новите технологии, скоростта на съвременния уеб и присъствието в AI асистенти като ChatGPT и Gemini, за да ви поставим пред конкурентите ви.'],
                        ['title' => 'Гледаме на вашия бизнес като на свой собствен', 'text' => 'За нас успехът не се измерва с празни кликове, а с реални клиенти и ръст на продажбите. Винаги даваме честна преценка и насочваме енергията и бюджета ви само към ходовете, които имат смисъл.'],
                        ['title' => 'Всичко, което изградим, остава за вас', 'text' => 'Не вярваме в изкуственото обвързване и скритите вратички. Всички акаунти, данни, системи и ресурси, които създадем заедно, са изцяло ваша собственост от първия ден.'],
                    ],
                ],
                [
                    [
                        ['title' => 'Fixed price', 'text' => 'The price is written into the offer and you know it before we start.'],
                        ['title' => 'A reply within one working day', 'text' => 'We reply to every enquiry within one working day at the latest.'],
                        ['title' => 'A transparent report', 'text' => 'Every month you get a clear report on what was done and the next steps.'],
                        ['title' => 'A direct line', 'text' => 'You talk directly to the people who do the work.'],
                    ],
                    [
                        ['title' => 'We do not waste time on empty talk', 'text' => 'While other agencies make endless presentations and slow things down for weeks, we prefer to act. We build, test in the real world, watch the numbers and improve what works.'],
                        ['title' => 'We play by the new rules of the internet', 'text' => 'The way people search for and choose companies today has nothing to do with five years ago. Instead of relying on outdated methods, we use the latest technology, the speed of the modern web and presence in AI assistants such as ChatGPT and Gemini, to put you ahead of your competitors.'],
                        ['title' => 'We treat your business as our own', 'text' => 'For us success is not measured in empty clicks but in real customers and sales growth. We always give an honest assessment and direct your energy and budget only to the moves that make sense.'],
                        ['title' => 'Everything we build stays with you', 'text' => 'We do not believe in artificial lock-in and hidden loopholes. All the accounts, data, systems and resources we create together are entirely yours from day one.'],
                    ],
                ],
            ],
        ],
    ];

    /** @return list<string> променените записи като „ключ/език“ */
    public static function apply(): array
    {
        $changed = [];
        $keys = array_unique([...array_keys(self::CHANGES), ...array_keys(self::LISTS), 'plans', 'team']);

        foreach (ContentBlock::whereIn('key', $keys)->get() as $block) {
            $value = self::transform($block->key, (array) $block->value, $block->locale);
            if ($value === $block->value) {
                continue;
            }

            ContentBlock::firstOrCreate(
                ['key' => $block->key . ':' . self::BACKUP, 'locale' => $block->locale],
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

    /** Сменя само низове и списъци, които са точно старият текст, и добавя липсващите нови полета; всичко друго остава. */
    public static function transform(string $key, array $value, string $locale = 'bg'): array
    {
        foreach (self::CHANGES[$key] ?? [] as $path => $map) {
            $current = data_get($value, $path);
            if (is_string($current) && isset($map[$current])) {
                data_set($value, $path, $map[$current]);

                // Етикетът на пакета идва със смяната на името му: g_items.N.name => g_items.N.tag.
                if ($key === 'plans' && preg_match('/^g_items\.(\d+)\.name$/', $path, $m) && ! filled(data_get($value, "g_items.{$m[1]}.tag"))) {
                    data_set($value, "g_items.{$m[1]}.tag", self::PLAN_TAGS[(int) $m[1]]);
                }
            }
        }

        foreach (self::LISTS[$key] ?? [] as $path => $pairs) {
            $current = data_get($value, $path);
            foreach ($pairs as [$old, $new]) {
                if (is_array($current) && $current === $old) {
                    data_set($value, $path, $new);
                    break;
                }
            }
        }

        return $key === 'team' ? self::addTeamFields($value, $locale) : $value;
    }

    /** Добавя g_role, g_description, g_bio и g_skills на всеки член на екипа, който ги няма, по първото име; нищо съществуващо не се променя. */
    public static function addTeamFields(array $team, string $locale): array
    {
        $defaults = (array) app(Content::class)->default('team', $locale);
        $first = fn ($member) => mb_strtolower((string) strtok(trim((string) ($member['name'] ?? '')), ' '));

        foreach ($team as $i => $member) {
            if (! is_array($member) || $first($member) === '') {
                continue;
            }
            $default = collect($defaults)->first(fn ($d) => $first($d) === $first($member));
            if (! $default) {
                continue;
            }
            foreach (self::TEAM_FIELDS as $field) {
                if (! array_key_exists($field, $member) && array_key_exists($field, $default)) {
                    $team[$i][$field] = $default[$field];
                }
            }
        }

        return $team;
    }
}
