<?php

namespace App\Support\Outreach;

use App\Mail\OutreachFollowUpMail;
use App\Mail\OutreachMail;
use App\Models\ClientEmail;
use App\Models\OutreachProspect;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Единственият път, по който излиза писмо до потенциален клиент (ръчно от „Напиши“, „Изпрати на избраните“,
 * напомняне и „Одобри и изпрати“ в „За одобрение“). Минава през отделната поща, дневния лимит и правилата на пазара
 * (OutreachProspect::canBeEmailed), записва се в client_emails и в approved_by. Автопилотът никога не вика този клас.
 */
class Sender
{
    /** Изпраща едно първо писмо и го записва. Връща текста на грешката или null. */
    public static function deliver(OutreachProspect $prospect, string $subject, array $data, int $userId): ?string
    {
        if (! Outreach::configured()) {
            return 'Пощата за потенциални клиенти не е настроена (OUTREACH_MAIL_*). Пуснете на сървъра bash deploy/configure-outreach-mail.sh.';
        }
        if (! $prospect->canBeEmailed()) {
            return $prospect->emailBlockReason() ?? 'На този адрес не може да се пише.';
        }
        if (Outreach::remainingToday() < 1) {
            return 'Дневният лимит от ' . config('creatium.outreach.daily_limit') . ' писма е достигнат. Продължете утре, за да не попаднем в спам.';
        }

        $log = ClientEmail::create([
            'outreach_prospect_id' => $prospect->id,
            'user_id' => $userId,
            'template' => 'outreach',
            'to_email' => $prospect->email,
            'subject' => $subject,
            'data' => $data,
        ]);

        try {
            Mail::mailer('outreach')->to($prospect->email)->send(new OutreachMail($prospect, $data, $subject));
        } catch (Throwable $e) {
            Log::error('Outreach email failed', ['prospect_id' => $prospect->id, 'error' => $e->getMessage()]);
            $log->forceFill(['error' => mb_substr($e->getMessage(), 0, 1000)])->save();

            return 'Писмото не беше изпратено: ' . $e->getMessage();
        }

        $log->forceFill(['sent_at' => now()])->save();
        // Който натисне „изпрати“, е одобрил писмото.
        $prospect->forceFill(['status' => 'sent', 'sent_at' => now(), 'approved_by' => $userId, 'draft_at' => null])->save();

        return null;
    }

    /** Едно напомняне до бизнес, който не е отговорил. Връща текста на грешката или null. */
    public static function deliverFollowUp(OutreachProspect $prospect, User $user): ?string
    {
        if (! Outreach::configured()) {
            return 'Пощата за потенциални клиенти не е настроена (OUTREACH_MAIL_*).';
        }
        if (! Markets::emailAllowed($prospect)) {
            return Markets::emailReason($prospect);
        }
        if (! $prospect->canFollowUp()) {
            return 'Не е време за напомняне: още не са минали ' . config('creatium.outreach.followup_after_days') . ' дни, вече е напомнено, отговорили са или са отписани.';
        }
        if (Outreach::remainingToday() < 1) {
            return 'Дневният лимит от ' . config('creatium.outreach.daily_limit') . ' писма е достигнат. Продължете утре.';
        }

        $english = $prospect->mailLanguage() === 'en';
        $first = ClientEmail::where('outreach_prospect_id', $prospect->id)->where('template', 'outreach')->whereNotNull('sent_at')->latest('sent_at')->first();
        $subject = 'Re: ' . ($first?->subject ?? $prospect->defaultSubject());
        $finding = $prospect->findings[0] ?? null;
        $data = [
            'first_name' => $prospect->first_name,
            'sender' => $user->name,
            'first_date' => $english
                ? $prospect->sent_at->timezone('Europe/Sofia')->format('j F Y')
                : $prospect->sent_at->timezone('Europe/Sofia')->format('d.m.Y'),
            'finding' => $english ? ($finding ? (OutreachProspect::findingTextEn($finding)[0] ?? null) : null) : ($finding['title'] ?? null),
        ];

        $log = ClientEmail::create([
            'outreach_prospect_id' => $prospect->id, 'user_id' => $user->id, 'template' => 'outreach-followup',
            'to_email' => $prospect->email, 'subject' => $subject, 'data' => $data,
        ]);
        try {
            Mail::mailer('outreach')->to($prospect->email)->send(new OutreachFollowUpMail($prospect, $data, $subject));
        } catch (Throwable $e) {
            Log::error('Outreach follow-up failed', ['prospect_id' => $prospect->id, 'error' => $e->getMessage()]);
            $log->forceFill(['error' => mb_substr($e->getMessage(), 0, 1000)])->save();

            return 'Напомнянето не беше изпратено: ' . $e->getMessage();
        }
        $log->forceFill(['sent_at' => now()])->save();
        $prospect->forceFill(['followup_sent_at' => now()])->save();

        return null;
    }
}
