<?php

namespace App\Support\Admin;

use App\Models\OutreachCall;
use App\Models\OutreachProspect;
use App\Models\Project;

/** Какво чака днес: един списък за страницата „Днес“, брояча в менюто и таблото. Часовете са по София. */
class Today
{
    /** Отворени запитвания със следваща стъпка за днес или по-рано (просрочените първи). */
    public static function due()
    {
        return Project::openLeads()->whereNotNull('next_action_at')
            ->where('next_action_at', '<=', now('Europe/Sofia')->endOfDay()->utc())
            ->orderBy('next_action_at')->get();
    }

    /** Непрочетени запитвания, най-старите първи (те чакат най-дълго). */
    public static function unread()
    {
        return Project::where('source', 'website')->whereNull('read_at')->oldest()->get();
    }

    /** Прочетени, още отворени запитвания, на които не сме записали следваща стъпка. */
    public static function withoutNextStep()
    {
        return Project::openLeads()->whereNotNull('read_at')->whereNull('next_action_at')->oldest('stage_changed_at')->get();
    }

    /** Консултации днес и утре. */
    public static function consultations()
    {
        $start = now('Europe/Sofia')->startOfDay()->utc();

        return Project::whereNotNull('consultation_at')
            ->whereBetween('consultation_at', [$start, $start->copy()->addDays(2)])
            ->orderBy('consultation_at')->get();
    }

    /** Бизнеси от списъка за обаждане, на които трябва да се обадим отново днес. */
    public static function callbacks(): int
    {
        return OutreachCall::whereNotNull('callback_on')->whereDate('callback_on', '<=', today())->count();
    }

    /** Потенциални клиенти, кликнали по писмото през последните 7 дни, които още не са отговорили. */
    public static function hotProspects()
    {
        return OutreachProspect::where('last_clicked_at', '>=', now()->subDays(7))
            ->where('status', 'sent')->orderByDesc('last_clicked_at')->get();
    }

    /** Броячът в менюто: неща, които не търпят отлагане. */
    public static function urgentCount(): int
    {
        return Project::where('source', 'website')->whereNull('read_at')->count()
            + Project::openLeads()->whereNotNull('next_action_at')->where('next_action_at', '<=', now('Europe/Sofia')->endOfDay()->utc())->count()
            + self::callbacks();
    }
}
