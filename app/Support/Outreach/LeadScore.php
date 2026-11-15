<?php

namespace App\Support\Outreach;

use App\Models\OutreachProspect;
use App\Models\OutreachSuppression;
use App\Support\SiteCheck\SiteChecker;

/**
 * Оценка 0 до 100 на потенциален клиент и причините за нея (на български). Тежестите са в creatium.outreach.scoring.
 *
 * - Нужда от нас (до 50): колко и колко тежки проблеми има сайтът му. Грешките тежат повече от предупрежденията.
 * - Качество на имейла (до 20) и телефон (8): може ли наистина да стигнем до човека.
 * - Видът бизнес (до 12): местни услуги, на които сайтът и Google носят клиенти.
 * - Приоритетът на пазара (до 10).
 * - Минус за голяма платформа (Booking, Facebook, Glovo…) вместо собствен сайт.
 * Вече писано, отписано или пропуснато = изключен (0).
 */
class LeadScore
{
    /** Заглавията на находки, които са грешки („fail“ в проверката), за записи отпреди да се пази статусът. */
    private const FAIL_PREFIXES = [
        'Сайтът не се отваря', 'Сайтът не работи през HTTPS', 'Сървърът отговаря много бавно', 'Няма заглавие за Google',
        'Няма описание за Google', 'Не е настроен за телефон', 'Сайтът е скрит от Google', 'AI асистентите са спрени', 'Няма HTTPS',
    ];

    /** @return array{score: int, reasons: list<array{label: string, points: int}>} */
    public static function for(OutreachProspect $prospect): array
    {
        $c = config('creatium.outreach.scoring');
        $reasons = [];

        if ($prospect->status !== 'new' || ($prospect->email && OutreachSuppression::where('email', $prospect->email)->exists())) {
            return ['score' => 0, 'reasons' => [['label' => 'Изключен: вече е писано, пропуснат или отписан', 'points' => 0]]];
        }

        // Нужда: находките на проверката на сайта.
        $need = 0;
        if (! $prospect->site_checked_at && $prospect->findings === null) {
            $need = (int) $c['need_unchecked'];
            $reasons[] = ['label' => 'Сайтът още не е проверен', 'points' => $need];
        } elseif (empty($prospect->findings)) {
            $reasons[] = ['label' => 'Сайтът е в добро състояние, нуждата е по-малка', 'points' => 0];
        } else {
            foreach ($prospect->findings as $finding) {
                $fail = ($finding['status'] ?? null) === 'fail'
                    || (! isset($finding['status']) && collect(self::FAIL_PREFIXES)->contains(fn ($prefix) => str_starts_with((string) $finding['title'], $prefix)));
                $points = (int) $c[$fail ? 'need_fail' : 'need_warn'];
                $need += $points;
                $reasons[] = ['label' => rtrim((string) $finding['title'], '.') . ($fail ? ' (грешка)' : ' (предупреждение)'), 'points' => $points];
            }
            if ($need > $c['need_max']) {
                $reasons[] = ['label' => 'Таван за нуждата от сайта', 'points' => (int) $c['need_max'] - $need];
                $need = (int) $c['need_max'];
            }
        }
        $total = $need;

        // Имейл и телефон.
        $email = (int) ($c['email'][$prospect->email_status] ?? 0);
        $total += $email;
        $reasons[] = ['label' => 'Имейл: ' . (OutreachProspect::EMAIL_STATUSES[$prospect->email_status] ?? 'непроверен'), 'points' => $email];
        if (filled($prospect->phone)) {
            $total += (int) $c['phone'];
            $reasons[] = ['label' => 'Има телефон', 'points' => (int) $c['phone']];
        }

        // Видът бизнес и пазарът.
        $category = (int) ($c['category'][mb_strtolower((string) $prospect->industry)] ?? $c['category_default']);
        $total += $category;
        $reasons[] = ['label' => 'Вид бизнес: ' . (\App\Models\OutreachCall::INDUSTRIES[mb_strtolower((string) $prospect->industry)] ?? ($prospect->industry ?: 'непознат')), 'points' => $category];
        $market = Markets::priority($prospect->country);
        $total += $market;
        $reasons[] = ['label' => 'Пазар: ' . Markets::label($prospect->country), 'points' => $market];

        // Голяма платформа вместо собствен сайт.
        $site = $prospect->website ? SiteChecker::normalizeDomain($prospect->website) : null;
        if ($site && ProspectChecker::isPlatform(preg_replace('/^www\./', '', $site))) {
            $total -= (int) $c['platform_penalty'];
            $reasons[] = ['label' => 'Голяма платформа вместо собствен сайт', 'points' => -(int) $c['platform_penalty']];
        }

        return ['score' => max(0, min(100, $total)), 'reasons' => $reasons];
    }

    /** Смята оценката и я записва при бизнеса. */
    public static function apply(OutreachProspect $prospect): OutreachProspect
    {
        $result = self::for($prospect);
        $prospect->forceFill(['score' => $result['score'], 'score_reasons' => $result['reasons']])->save();

        return $prospect;
    }
}
