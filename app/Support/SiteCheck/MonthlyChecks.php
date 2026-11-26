<?php

namespace App\Support\SiteCheck;

use App\Mail\SiteCheckReportMail;
use App\Models\ClientEmail;
use App\Models\Project;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Месечна проверка за клиентите на плана „Следене на сайта“ (отметка monthly_check в проекта): на 30 дни пускаме пълната проверка,
 * пазим отчета и пращаме на клиента кратък отчет с предишния резултат. Без cron: най-много един проект на тик (SendSiteCheckFollowUps).
 */
class MonthlyChecks
{
    public static function enabled(): bool
    {
        return (bool) config('creatium.site_check.monthly.enabled');
    }

    /** Проектите, на които е време за месечна проверка: най-старата (или още непускана) първа. */
    public static function due(): Builder
    {
        return Project::where('monthly_check', true)->where('status', '!=', 'Cancelled')
            ->whereNotNull('website_url')->where('website_url', '!=', '')
            ->where(fn ($q) => $q->whereNull('monthly_check_at')->orWhere('monthly_check_at', '<=', now()->subDays((int) config('creatium.site_check.monthly.every_days', 30))))
            ->orderByRaw('monthly_check_at is not null')->orderBy('monthly_check_at')->orderBy('id');
    }

    /** Проверява най-много $limit проекта. Връща колко са проверени. */
    public function run(int $limit = 1): int
    {
        $lock = Cache::lock('monthly-checks', 600);
        if (! $lock->get()) {
            return 0;
        }
        try {
            $done = 0;
            foreach (self::due()->limit($limit)->get() as $project) {
                $this->check($project);
                $done++;
            }

            return $done;
        } finally {
            $lock->release();
        }
    }

    public function check(Project $project): void
    {
        $domain = SiteChecker::normalizeDomain($project->website_url) ?? (string) parse_url($project->website_url, PHP_URL_HOST);
        $previousReport = $project->site_check_report;
        $previous = is_array($previousReport) && isset($previousReport['score']) ? (int) $previousReport['score'] : null;
        $every = (int) config('creatium.site_check.monthly.every_days', 30);

        if (function_exists('set_time_limit')) {
            @set_time_limit((int) config('creatium.site_check.crawl_seconds', 60) + 180);
        }
        $locale = App::getLocale();
        App::setLocale($project->locale === 'en' ? 'en' : 'bg');
        try {
            try {
                $report = app(SiteAudit::class)->run($domain);
            } catch (Throwable $e) {
                Log::error('Monthly site check failed: ' . $e->getMessage(), ['project_id' => $project->id, 'domain' => $domain]);
                $report = null;
            }

            if (! $report) {
                // Нова проба след един ден, не на всеки тик.
                $project->forceFill(['monthly_check_at' => now()->subDays(max(0, $every - 1))])->save();

                return;
            }

            $sent = false;
            $log = null;
            if (filled($project->client_email)) {
                $mail = new SiteCheckReportMail($report, null, true, $previous);
                try {
                    $log = ClientEmail::create(['project_id' => $project->id, 'template' => 'monthly-site-check', 'to_email' => $project->client_email, 'subject' => $mail->envelope()->subject, 'data' => ['score' => $report['score'], 'previous' => $previous]]);
                    Mail::to($project->client_email)->send($mail);
                    $log->forceFill(['sent_at' => now()])->save();
                    $sent = true;
                } catch (Throwable $e) {
                    Log::error('Monthly site check email failed: ' . $e->getMessage(), ['project_id' => $project->id]);
                    $log?->forceFill(['error' => mb_substr($e->getMessage(), 0, 1000)])->save();
                    $project->notes()->create(['user_id' => null, 'kind' => 'note', 'body' => 'Месечният отчет за ' . $domain . ' не беше изпратен на ' . $project->client_email . '.']);
                }
            } else {
                $project->notes()->create(['user_id' => null, 'kind' => 'note', 'body' => 'Месечна проверка на ' . $domain . ' с оценка ' . $report['score'] . ' от 100, но проектът няма имейл на клиента.']);
            }

            $project->forceFill(['site_check_report' => SendSiteCheckReport::stored($report, $domain, $sent), 'monthly_check_at' => now()])->save();
        } finally {
            App::setLocale($locale);
        }
    }
}
