<?php

namespace App\Support\SiteCheck;

use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

/**
 * Срокът на личния преглед към безплатната проверка на сайт: два работни дни (понеделник до петък, по софийско време)
 * след заявката. Празниците не се броят. Срокът е края на деня, затова „до вторник“ значи целия вторник.
 */
class ReviewDue
{
    public const WORKING_DAYS = 2;

    public static function after(?CarbonInterface $from = null): Carbon
    {
        $day = Carbon::instance($from ?? now())->timezone('Europe/Sofia')->startOfDay();
        $left = self::WORKING_DAYS;
        while ($left > 0) {
            $day = $day->addDay();
            if ($day->isWeekday()) {
                $left--;
            }
        }

        return $day->endOfDay()->timezone('UTC');
    }

    /** Датата на срока за показване (по софийско време), напр. 25.11.2026. */
    public static function label(CarbonInterface $due): string
    {
        return $due->copy()->timezone('Europe/Sofia')->format('d.m.Y');
    }

    /** Бележката за екипа в описанието и в известията. */
    public static function note(CarbonInterface $due): string
    {
        return 'Личен преглед до ' . self::label($due) . '.';
    }

    /** @return array<string, string> цел => етикет (български, за админа) */
    public static function goals(): array
    {
        return (array) config('creatium.site_check_page.goals', []);
    }

    /** Бележка за целта, без двоеточие (правилото за текстовете на собствениците важи и за писмата до екипа). */
    public static function goalNote(?string $key): ?string
    {
        $label = self::goalLabel($key);

        return $label ? "Основната цел на човека е „{$label}“." : null;
    }

    public static function goalLabel(?string $key): ?string
    {
        return $key ? (self::goals()[$key] ?? null) : null;
    }
}
