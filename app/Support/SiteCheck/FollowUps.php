<?php

namespace App\Support\SiteCheck;

use App\Mail\SiteCheckFollowUpMail;
use App\Models\ClientEmail;
use App\Models\OutreachSuppression;
use App\Models\Project;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Три кратки писма след отчета от безплатната проверка (ден 2 съвет, ден 5 оферта, ден 14 проверка отново), без cron: пускат се от
 * SendSiteCheckFollowUps след отговора, най-много 10 писма на тик, само от 09:00 до 19:00 по София. Пишем само когато всичко е вярно за
 * запитването (виж due()). Неуспешно изпращане само се записва и не мести стъпката; след max_failures неуспеха писмата спират.
 */
class FollowUps
{
    public const STEPS = 3;

    public static function enabled(): bool
    {
        return (bool) config('creatium.site_check.followups.enabled');
    }

    /** Между 09:00 и 19:00 по София, когато е нормално да получиш такова писмо. */
    public static function inWindow(?Carbon $now = null): bool
    {
        $hour = ($now ?? now())->copy()->timezone('Europe/Sofia')->hour;

        return $hour >= 9 && $hour < 19;
    }

    /** Запитвания, които още могат да получат писмо (груб филтър в базата; точната проверка е due()). */
    public static function candidates(): Builder
    {
        return Project::siteChecks()
            ->whereNull('followup_stopped_at')->whereNull('site_check_offer')
            ->whereIn('stage', ['new', 'contacted'])
            ->where('followup_step', '<', self::STEPS)
            ->whereNotNull('client_email')->where('client_email', '!=', '')
            ->where('created_at', '>=', now()->subDays((int) config('creatium.site_check.followups.max_age_days', 30) + 2));
    }

    /** Кога е изпратен отчетът (за ден 2, 5 и 14), или null, ако отчет с проблеми не е изпращан. */
    public static function reportAt(Project $project): ?Carbon
    {
        $report = $project->site_check_report;
        if (! is_array($report) || empty($report['sent']) || ! isset($report['score'])) {
            return null;
        }
        // Най-малко един проблем за оправяне (без бележките), иначе няма какво да посочим.
        if (Offers::topIssues($project, 1) === []) {
            return null;
        }

        return Carbon::parse($report['checked_at'] ?? $project->created_at);
    }

    /** Следващата стъпка (1 до 3), ако е дошъл нейният ден и всички условия са изпълнени, иначе null. */
    public static function due(Project $project, ?Carbon $now = null): ?int
    {
        $now ??= now();
        $step = (int) $project->followup_step + 1;
        $cfg = (array) config('creatium.site_check.followups');

        if ($step > self::STEPS || $project->leadKind() !== 'site-check' || $project->followup_stopped_at || $project->site_check_offer
            || ! in_array($project->stage, ['new', 'contacted'], true) || blank($project->client_email)) {
            return null;
        }
        $at = self::reportAt($project);
        $days = $cfg['days'][$step - 1] ?? null;
        if (! $at || $days === null || $now->lt($at->copy()->addDays($days)) || $now->gt($at->copy()->addDays((int) ($cfg['max_age_days'] ?? 30)))) {
            return null;
        }
        // Два писма не тръгват едно след друго, ако сървърът е бил изключен.
        if ($project->followup_sent_at && $project->followup_sent_at->gt($now->copy()->subDays((int) ($cfg['min_gap_days'] ?? 1)))) {
            return null;
        }
        if (OutreachSuppression::where('email', mb_strtolower(trim($project->client_email)))->exists()) {
            return null;
        }

        return $step;
    }

    /** Изпраща до $limit дължими писма. Връща колко са изпратени. $ignoreHours е за ръчната команда. */
    public function run(?int $limit = null, bool $ignoreHours = false): int
    {
        if (! $ignoreHours && ! self::inWindow()) {
            return 0;
        }
        $lock = Cache::lock('site-check-followups', 300);
        if (! $lock->get()) {
            return 0;
        }

        $sent = 0;
        $limit ??= (int) config('creatium.site_check.followups.per_tick', 10);
        try {
            foreach (self::candidates()->orderBy('id')->limit(500)->get() as $project) {
                if ($sent >= $limit) {
                    break;
                }
                if (($step = self::due($project)) !== null && $this->send($project, $step)) {
                    $sent++;
                }
            }
        } finally {
            $lock->release();
        }

        return $sent;
    }

    /** Едно писмо. Никога не хвърля. Връща true при успех. */
    public function send(Project $project, int $step): bool
    {
        $previous = App::getLocale();
        App::setLocale($project->locale === 'en' ? 'en' : 'bg');
        $log = null;
        try {
            $mail = new SiteCheckFollowUpMail($project, $step);
            $log = ClientEmail::create([
                'project_id' => $project->id, 'template' => 'site-check-followup-' . $step, 'to_email' => $project->client_email,
                'subject' => $mail->envelope()->subject, 'data' => ['step' => $step],
            ]);
            Mail::to($project->client_email)->send($mail);
        } catch (Throwable $e) {
            Log::error('Site check follow-up failed: ' . $e->getMessage(), ['project_id' => $project->id, 'step' => $step]);
            $log?->forceFill(['error' => mb_substr($e->getMessage(), 0, 1000)])->save();
            $this->failed($project);

            return false;
        } finally {
            App::setLocale($previous);
        }

        $log->forceFill(['sent_at' => now()])->save();
        $project->forceFill(['followup_step' => $step, 'followup_sent_at' => now(), 'followup_failures' => 0])->save();

        return true;
    }

    private function failed(Project $project): void
    {
        $failures = (int) $project->followup_failures + 1;
        $values = ['followup_failures' => $failures];
        $stop = $failures >= (int) config('creatium.site_check.followups.max_failures', 3);
        if ($stop) {
            $values['followup_stopped_at'] = now();
        }
        $project->forceFill($values)->save();
        if ($stop) {
            $project->notes()->create(['user_id' => null, 'kind' => 'note', 'body' => 'Писмата след проверката са спрени, защото ' . $failures . ' пъти не успяха да се изпратят. Вижте „Имейли до клиенти“ за грешката.']);
        }
    }

    /** Състоянието за админа: [етикет, цвят]. */
    public static function status(Project $project): array
    {
        return match (true) {
            (bool) $project->site_check_offer => ['Избрана оферта', 'bg-green-100 text-green-800'],
            (bool) $project->followup_stopped_at => ['Спрени', 'bg-gray-200 text-gray-700'],
            (int) $project->followup_step >= self::STEPS => ['Изпратени и трите', 'bg-blue-100 text-blue-800'],
            default => [(int) $project->followup_step . ' от ' . self::STEPS . ' изпратени', 'bg-amber-100 text-amber-800'],
        };
    }
}
