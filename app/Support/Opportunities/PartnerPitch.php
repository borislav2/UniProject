<?php

namespace App\Support\Opportunities;

use App\Models\OutreachProspect;

/**
 * Кратко писмо на английски до агенция за партньорство с бял етикет: чернова за „За одобрение“ (никога не се праща само).
 * Правилата за канала от Markets::emailRule() важат както за всяко първо писмо: за Нидерландия и за британски бизнес
 * без регистрирана фирма писмо не се предлага, а обаждане или LinkedIn. Английският текст следва правилото на собствениците:
 * без тирета и без двоеточия (освен в час и адрес).
 *
 * Писмото минава през общата обвивка emails/outreach-en (представяне, връзка към безплатната проверка на сайт, бутон за час).
 */
class PartnerPitch
{
    public static function subject(OutreachProspect $prospect): string
    {
        return 'Extra hands for ' . $prospect->displayName() . ' on websites, SEO and email';
    }

    /** Текстът на черновата (наблюдението в писмото): три абзаца, без списък. */
    public static function body(OutreachProspect $prospect, ?string $examplesUrl = null): string
    {
        $examples = $examplesUrl ?: route('en.portfolio');

        return implode("\n", [
            'Many agencies turn down smaller website, SEO and email jobs because their own team is full or the budget does not justify a new hire. That is the gap we fill for other agencies.',
            'We are a three person team in Bulgaria, working on European time. We build websites on WordPress and Laravel, do SEO and Google Business Profile work, and run email campaigns. All of it can go out under your brand, without any mention of us to your client.',
            'Prices are fixed per project and agreed before we start, so you can quote your own clients with confidence. You can see examples of our work at ' . $examples,
            'If you have a small job you would like a second pair of hands on, reply to this email and we will send you a fixed price.',
        ]);
    }

    /** Кратко обяснение на български за екипа, който чете английската чернова. */
    public static function summaryBg(): string
    {
        return 'Писмото на английски предлага на агенцията да работим за нея под нейното име (бял етикет): сайтове, SEO и имейл кампании на фиксирани цени, от екип от трима души в България, с връзка към примери. Предлага и малка пробна поръчка.';
    }
}
