<?php

namespace App\Support\Geo;

use Illuminate\Support\Str;

/**
 * How many people we are, said the same way everywhere: the visible line on /za-nas (design G), /llms.txt, /llms-full.txt
 * and numberOfEmployees in the Organization JSON-LD. Everything is read from site('team'), nothing is written by hand.
 */
class TeamFacts
{
    private const WORDS = [
        'bg' => [1 => 'един', 2 => 'двама', 3 => 'трима', 4 => 'четирима', 5 => 'петима', 6 => 'шестима', 7 => 'седмина', 8 => 'осмина', 9 => 'деветима', 10 => 'десетима'],
        'en' => [1 => 'one', 2 => 'two', 3 => 'three', 4 => 'four', 5 => 'five', 6 => 'six', 7 => 'seven', 8 => 'eight', 9 => 'nine', 10 => 'ten'],
    ];

    /** Team members with a name, in the order of the team block. */
    public static function members(): array
    {
        return array_values(array_filter((array) site('team'), fn ($m) => filled($m['name'] ?? null)));
    }

    public static function count(): int
    {
        return count(self::members());
    }

    /** The number as a word („трима“, „three“), digits above ten. */
    public static function word(int $n, ?string $locale = null): string
    {
        $locale = ($locale ?? app()->getLocale()) === 'en' ? 'en' : 'bg';

        return self::WORDS[$locale][$n] ?? (string) $n;
    }

    /** „Борислав, Владимир и Мария“ (English „Borislav, Vladimir and Maria“). */
    public static function names(?string $locale = null): string
    {
        $locale ??= app()->getLocale();
        $first = array_map(fn ($m) => Str::before(trim((string) $m['name']), ' '), self::members());
        $and = $locale === 'en' ? ' and ' : ' и ';

        return count($first) > 1 ? implode(', ', array_slice($first, 0, -1)) . $and . end($first) : (string) ($first[0] ?? '');
    }

    /**
     * The visible team sentence. `about.g_team_line` is the editable text with {count} (the number as a word) and {names};
     * the count and the names always come from the team block, so the sentence cannot go out of date.
     */
    public static function line(?array $about = null): string
    {
        if (self::count() === 0) {
            return '';
        }
        $about ??= app(\App\Support\Content::class)->withDefaults('about');
        $template = trim((string) ($about['g_team_line'] ?? ''));

        return $template === '' ? '' : strtr($template, ['{count}' => self::word(self::count()), '{names}' => self::names()]);
    }

    /** „Екипът (трима души)“ / „The team (three people)“: the heading of the team list in /llms.txt and /llms-full.txt. */
    public static function heading(?string $locale = null): string
    {
        $locale = ($locale ?? app()->getLocale()) === 'en' ? 'en' : 'bg';
        $n = self::count();

        return $locale === 'en'
            ? 'The team (' . self::word($n, 'en') . ($n === 1 ? ' person)' : ' people)')
            : 'Екипът (' . self::word($n, 'bg') . ($n === 1 ? ' човек)' : ' души)');
    }

    /** Who the founders are: the first two members (the same rule as the Kreo knowledge), the rest are part of the team. Empty for a team of one. */
    public static function founderNote(?string $locale = null): string
    {
        $locale = ($locale ?? app()->getLocale()) === 'en' ? 'en' : 'bg';
        $first = array_map(fn ($m) => Str::before(trim((string) $m['name']), ' '), self::members());
        if (count($first) < 2) {
            return '';
        }
        $join = fn (array $names) => count($names) > 1 ? implode(', ', array_slice($names, 0, -1)) . ($locale === 'en' ? ' and ' : ' и ') . end($names) : $names[0];
        $founders = $join(array_slice($first, 0, 2));
        $rest = array_slice($first, 2);

        if ($locale === 'en') {
            return 'The founders and owners of Creatium Lab are ' . $founders . '.' . ($rest ? ' ' . $join($rest) . (count($rest) > 1 ? ' are' : ' is') . ' part of the team.' : '');
        }

        return 'Основатели и собственици на Creatium Lab са ' . $founders . '.' . ($rest ? ' ' . $join($rest) . (count($rest) > 1 ? ' са' : ' е') . ' част от екипа.' : '');
    }
}
