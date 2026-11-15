<?php

namespace App\Support\Opportunities;

/**
 * Ключови думи за услугите ни на английски, български, немски, нидерландски (и няколко други големи езика на ЕС).
 * Силните думи (уебсайт, SEO, дигитален маркетинг...) са съвпадение сами по себе си. Слабите (маркетинг, реклама, PR...) броят само
 * в заглавието и дават малко точки: нужни са за Contracts Finder и Find a Tender, които не филтрират по CPV. „Дизайн“ и „кампания“
 * не са в списъка, защото хващат архитектурен дизайн и ваксинационни кампании. Самата дума „уебсайт“ в описание е навсякъде
 * („вижте на сайта ни“), затова в описанието се брои само до глагол или съществително за работа (изработка, разработка,
 * поддръжка, design, development, Erstellung, ontwikkeling...). Окончанията се хващат с \p{L}*, затова „уебсайтове“ и „Webseiten“ също съвпадат.
 */
class Keywords
{
    /** Думите за сайт, които сами по себе си не са работа: в заглавие са съвпадение, в описание само до дума за работа. */
    private const BARE_WEB = [
        'web\s?sites?', 'уеб\s?сайт\p{L}*', 'уеб\s?страниц\p{L}*', 'интернет\s?страниц\p{L}*', 'интернет\s?сайт\p{L}*', 'интернет\s?портал\p{L}*',
        'webseite\p{L}*', 'website\p{L}*', 'internetauftritt\p{L}*', 'internetseite\p{L}*', 'webauftritt\p{L}*', 'homepage\p{L}*',
        'webshop\p{L}*', 'site\s?web', 'site\s?internet', 'p[áa]gina\s?web', 'sitio\s?web', 'sito\s?web', 'sito\s?internet', 'stron\p{L}*\s?internetow\p{L}*', 'witryn\p{L}*',
    ];

    /** Думите за работа до „уебсайт“ (в описание): изработка, разработка, дизайн, поддръжка, обновяване... */
    private const WORK_WORDS = [
        'design', 'develop', 'build', 'redesign', 'redevelop', 'creat', 'maintain', 'maintenance', 'hosting', 'refresh', 'relaunch', 'migrat', 'launch', 'upgrad',
        'изработ', 'разработ', 'създаван', 'поддръжк', 'дизайн', 'обновя', 'хостинг',
        'entwicklung', 'erstellung', 'gestaltung', 'relaunch', 'pflege', 'neugestaltung', 'erneuerung', 'betrieb',
        'ontwikkel', 'ontwerp', 'beheer', 'bouw', 'onderhoud', 'vernieuw',
        'conception', 'refonte', 'cr[ée]ation', 'd[ée]veloppement', 'dise[ñn]o', 'desarrollo', 'sviluppo', 'realizzazione',
    ];

    /** Темата => силни шаблони без разлика между малки и главни букви (работят и в заглавие, и в описание). */
    private const STRONG = [
        'web' => [
            'web\s?design\p{L}*', 'web\s?development', 'web\s?portals?', 'landing\s?pages?', 'content management system',
            'уеб\s?дизайн\p{L}*', 'уеб\s?портал\p{L}*', 'webdesign\p{L}*', 'webentwicklung\p{L}*', 'webontwikkeling\p{L}*', 'e-?commerce',
        ],
        'seo' => ['search engine optimi[sz]ation', 'оптимизация за търсачки', 'suchmaschinenoptimierung', 'zoekmachine\s?optimalisatie', 'r[ée]f[ée]rencement'],
        'marketing' => [
            'digital marketing', 'online marketing', 'e-?mail marketing', 'pay[- ]per[- ]click',
            'дигитал\p{L}*\s+маркетинг\p{L}*', 'онлайн\s?маркетинг\p{L}*', 'имейл\s?маркетинг\p{L}*',
            'online[- ]?marketing', 'digital[- ]?marketing', 'newsletter[- ]?marketing',
            'marketing digital', 'communication digitale', 'communication num[ée]rique',
        ],
        'social' => ['social media', 'социални медии', 'социалните медии', 'social[- ]media', 'sociale media', 'r[ée]seaux sociaux', 'redes sociales'],
        'design' => [
            'branding', 'rebranding', 'brand (?:strategy|identity|design)', 'graphic design\p{L}*', 'corporate design', 'visual identity', 'identité visuelle',
            'брандиране', 'фирмена идентичност', 'графичен дизайн', 'grafikdesign', 'grafisch ontwerp', 'huisstijl',
        ],
    ];

    /** Силни шаблони, които са чувствителни към главни букви (съкращения). */
    private const STRONG_CASED = ['seo' => ['SEO', 'SEM'], 'web' => ['CMS'], 'marketing' => ['PPC']];

    /** Слаби думи: само в заглавието. */
    private const WEAK = [
        'marketing\p{L}*', 'advertis\p{L}*', 'public relations', 'creative (?:agency|services)', 'media agency', 'communications? (?:strategy|agency|services|support)',
        'werbe\p{L}*', 'werbung', 'reclame', 'маркетинг\p{L}*', 'реклам\p{L}*',
    ];

    private static function compile(array $patterns, string $flags): string
    {
        return '/(?<![\p{L}\p{N}])(?:' . implode('|', $patterns) . ')(?![\p{L}\p{N}])/u' . $flags;
    }

    /** „Разработка на нов уебсайт“, „website redesign“: дума за работа до три думи от думата за сайт. */
    private static function bareWebNearWork(): string
    {
        $bare = '(?:' . implode('|', self::BARE_WEB) . ')';
        $work = '(?:' . implode('|', self::WORK_WORDS) . ')\p{L}*';
        $gap = '(?:[\p{L}\p{N}-]+[\s,;:]+){0,3}';

        return '/(?<![\p{L}\p{N}])(?:' . $work . '[\s,;:]+' . $gap . $bare . '|' . $bare . '[\s,;:]+' . $gap . $work . ')(?![\p{L}\p{N}])/iu';
    }

    /**
     * @return array{strong_title: list<string>, strong_text: list<string>, weak_title: list<string>, topics: list<string>}
     */
    public static function match(?string $title, ?string $text = null): array
    {
        $result = ['strong_title' => [], 'strong_text' => [], 'weak_title' => [], 'topics' => []];
        $title = (string) $title;
        $text = (string) $text;

        $patterns = self::STRONG;
        foreach ($patterns as $topic => $list) {
            $cased = self::STRONG_CASED[$topic] ?? [];
            foreach ([[$list, 'i'], [$cased, '']] as [$set, $flags]) {
                if (! $set) {
                    continue;
                }
                $regex = self::compile($set, $flags);
                if (preg_match($regex, $title, $m)) {
                    $result['strong_title'][] = mb_strtolower($m[0]);
                    $result['topics'][] = $topic;
                }
                if ($text !== '' && preg_match($regex, $text, $m)) {
                    $result['strong_text'][] = mb_strtolower($m[0]);
                    $result['topics'][] = $topic;
                }
            }
        }
        // Бавният „уебсайт“: в заглавието е съвпадение сам, в описанието само до дума за работа.
        if (preg_match(self::compile(self::BARE_WEB, 'i'), $title, $m)) {
            $result['strong_title'][] = mb_strtolower($m[0]);
            $result['topics'][] = 'web';
        }
        if ($text !== '' && preg_match(self::bareWebNearWork(), $text, $m)) {
            $result['strong_text'][] = mb_strtolower(trim($m[0]));
            $result['topics'][] = 'web';
        }
        if (preg_match(self::compile(self::WEAK, 'i'), $title, $m)) {
            $result['weak_title'][] = mb_strtolower($m[0]);
        }
        foreach (['strong_title', 'strong_text', 'weak_title', 'topics'] as $key) {
            $result[$key] = array_values(array_unique($result[$key]));
        }

        return $result;
    }
}
