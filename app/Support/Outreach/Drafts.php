<?php

namespace App\Support\Outreach;

use App\Models\OutreachProspect;

/**
 * Черновите за „За одобрение“: тема и наблюдение на езика на бизнеса, подготвени от проверката на сайта му.
 * Подготовката никога не изпраща нищо. draft_at означава „предложен за преглед от човек“.
 */
class Drafts
{
    /**
     * Подготвя черновата. За бизнес, на когото не може да се пише по имейл (Markets::emailRule), няма писмо:
     * предлага се само за обаждане или LinkedIn. Връща false, ако няма от какво да се напише писмо.
     */
    public static function prepare(OutreachProspect $prospect): bool
    {
        if (! $prospect->canBeEmailed()) {
            $prospect->forceFill(['draft_at' => now(), 'draft_subject' => null, 'draft_observation' => null])->save();

            return true;
        }

        $observation = $prospect->suggestedObservation();
        if ($observation === '') {
            return false;
        }
        $prospect->forceFill(['draft_at' => now(), 'draft_subject' => $prospect->defaultSubject(), 'draft_observation' => $observation])->save();

        return true;
    }

    /** Една кратка реплика на български за това, което казва английското писмо, за да го следва целият екип. */
    public static function summaryBg(OutreachProspect $prospect): ?string
    {
        if ($prospect->mailLanguage() !== 'en') {
            return null;
        }
        $titles = [];
        foreach ($prospect->findings ?: [] as $finding) {
            if (OutreachProspect::findingTextEn($finding)) {
                $titles[] = mb_strtolower(rtrim((string) $finding['title'], '.'), 'UTF-8');
            }
        }

        $what = $titles
            ? 'казва, че при проверката на сайта са намерени ' . count($titles) . ' неща (' . implode('; ', array_slice($titles, 0, 3)) . ')'
            : 'казва, че сайтът е в добро състояние и предлага проста проверка в Google';

        return 'Писмото на английски представя Creatium Lab като малка агенция за сайтове, ' . $what . ', предлага безплатната проверка на сайта и кратък разговор.';
    }
}
