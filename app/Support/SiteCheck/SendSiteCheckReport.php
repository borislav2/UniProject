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
    public function __construct(private SiteAudit $audit)
    {
    }

    public function __invoke(Project $lead, string $domain, string $email, string $locale): void
    {
        app()->setLocale($locale);
        // Обхождането на сайта е по-дълго от една заявка; изпълнява се след отговора и не бива да се прекъсва от лимита на PHP.
        if (function_exists('set_time_limit')) {
            @set_time_limit((int) config('creatium.site_check.crawl_seconds', 60) + 180);
        }

        try {
            $report = $this->audit->run($domain);
        } catch (\Throwable $e) {
            Log::error('Site check failed: ' . $e->getMessage(), ['project_id' => $lead->id, 'domain' => $domain]);
            $report = null;
        }

        // Срокът за личния преглед и целта на човека отиват най-отгоре в бележките (описанието се пише наново).
        $notes = array_values(array_filter([
            $lead->review_due_at ? ReviewDue::note($lead->review_due_at) : null,
            ReviewDue::goalNote($lead->check_goal),
        ]));
        $sent = false;
        if ($report) {
            $problems = collect(SiteAudit::items($report))->where('severity', '!=', 'notice')->pluck('title');
            $notes[] = "Проверка на сайт {$domain}: оценка {$report['score']} от 100.";
            $areaLine = collect(Areas::scores($report))->map(fn ($a) => $a['label'] . ' ' . $a['score'])->implode(', ');
            $notes[] = "По области (само от нашите проверки): {$areaLine}.";
            $notes[] = $problems->isEmpty() ? 'Без открити проблеми.' : "За оправяне:\n- " . $problems->implode("\n- ");

            try {
                Mail::to($email)->send(new SiteCheckReportMail($report, $lead));
                $notes[] = "Отчетът е изпратен на {$email}.";
                $sent = true;
            } catch (\Throwable $e) {
                Log::error('Site check report email failed: ' . $e->getMessage(), ['project_id' => $lead->id]);
                $notes[] = "Отчетът НЕ беше изпратен на {$email}: " . $e->getMessage();
            }
        } else {
            $notes[] = "Проверката на {$domain} не успя (вижте лога). Отчет не е изпратен, свържете се с клиента на {$email}.";
        }

        // Резултатът се пази цял: страницата „Проверки на сайт“ го показва като обобщение най-отгоре.
        $lead->forceFill([
            'description' => implode("\n\n", $notes),
            'site_check_report' => self::stored($report, $domain, $sent),
        ])->save();

        try {
            Mail::to(config('creatium.notify_email'))->send(new NewLeadMail($lead));
            $lead->forceFill(['email_sent_at' => now()])->save();
        } catch (\Throwable $e) {
            Log::error('Lead notification email failed: ' . $e->getMessage(), ['project_id' => $lead->id]);
        }
        \App\Support\Notify\Telegram::newLead($lead, 'Проверка на сайт', defer: false);
    }

    /** Отчетът във вида, в който се пази в projects.site_check_report (общ за проверката и за месечната проверка на абонатите). */
    public static function stored(?array $report, string $domain, bool $sent): array
    {
        return $report ? [
            'domain' => $domain,
            'score' => $report['score'],
            'home_score' => $report['home_score'] ?? $report['score'],
            'crawl' => $report['crawl'] ?? null,
            'issues' => $report['issues'] ?? [],
            'pagespeed' => $report['pagespeed'] ?? null,
            'shopping' => $report['shopping'] ?? null,
            'checks' => collect($report['checks'])->map(fn ($c) => ['key' => $c['key'] ?? null, 'status' => $c['status'], 'title' => $c['title'], 'detail' => $c['detail'] ?? ''])->values()->all(),
            'sent' => $sent,
            'checked_at' => now()->toIso8601String(),
        ] : ['domain' => $domain, 'failed' => true, 'checked_at' => now()->toIso8601String()];
    }
}
