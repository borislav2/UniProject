<?php

namespace App\Support\SiteCheck;

use App\Mail\NewLeadMail;
use App\Mail\SiteCheckReportMail;
use App\Models\Project;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Пуска проверката и изпраща отчета на клиента, след като страницата вече е върната (defer()).
 * Записва резултата в запитването и уведомява екипа като при обикновено запитване.
 */
class SendSiteCheckReport
{
    public function __construct(private SiteChecker $checker)
    {
    }

    public function __invoke(Project $lead, string $domain, string $email, string $locale): void
    {
        app()->setLocale($locale);

        try {
            $report = $this->checker->check($domain);
        } catch (\Throwable $e) {
            Log::error('Site check failed: ' . $e->getMessage(), ['project_id' => $lead->id, 'domain' => $domain]);
            $report = null;
        }

        $notes = [];
        if ($report) {
            $problems = collect($report['checks'])->where('status', '!=', 'ok')->pluck('title');
            $notes[] = "Проверка на сайт {$domain}: оценка {$report['score']} от 100.";
            $notes[] = $problems->isEmpty() ? 'Без открити проблеми.' : "За оправяне:\n- " . $problems->implode("\n- ");

            try {
                Mail::to($email)->send(new SiteCheckReportMail($report));
                $notes[] = "Отчетът е изпратен на {$email}.";
            } catch (\Throwable $e) {
                Log::error('Site check report email failed: ' . $e->getMessage(), ['project_id' => $lead->id]);
                $notes[] = "Отчетът НЕ беше изпратен на {$email}: " . $e->getMessage();
            }
        } else {
            $notes[] = "Проверката на {$domain} не успя (вижте лога). Отчет не е изпратен, свържете се с клиента на {$email}.";
        }

        $lead->forceFill(['description' => implode("\n\n", $notes)])->save();

        try {
            Mail::to(config('creatium.notify_email'))->send(new NewLeadMail($lead));
            $lead->forceFill(['email_sent_at' => now()])->save();
        } catch (\Throwable $e) {
            Log::error('Lead notification email failed: ' . $e->getMessage(), ['project_id' => $lead->id]);
        }
    }
}
