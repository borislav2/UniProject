<?php

namespace App\Support\Outreach;

use App\Models\OutreachProspect;
use App\Support\SiteCheck\SiteChecker;
use Illuminate\Support\Facades\Cache;
use Throwable;

/**
 * Проверява бизнесите, внесени без проверка (напр. от Harvey): прави проверката на сайта и потвърждава имейла.
 * Адресът става „публикуван от бизнеса“ само ако е на домейна на сайта (или безплатна поща) и наистина
 * се намира на сайта им. Иначе остава „непроверен“ с бележка защо, за ръчен преглед.
 */
class ProspectChecker
{
    /** Големи платформи и каталози: имейлът там е на платформата, не на бизнеса. */
    public const PLATFORM_DOMAINS = [
        'notino.bg', 'notino.com', 'emag.bg', 'olx.bg', 'bazar.bg', 'booking.com', 'airbnb.com', 'tripadvisor.com',
        'facebook.com', 'instagram.com', 'google.com', 'business.site', 'foursquare.com', 'glovo.com', 'foodpanda.bg',
        'takeaway.com', 'superdoc.bg', 'imot.bg', 'zlatnistranici.bg', 'goldenpages.bg', 'bgmaps.com', 'linktr.ee',
    ];

    public function __construct(
        private EmailFinder $emails,
        private EmailVerifier $verifier,
        private SiteChecker $checker,
    ) {
    }

    /** Бизнесите, които чакат проверка: нови, с непотвърден имейл или без проверка на сайта, още непроверени. */
    public static function pending()
    {
        return OutreachProspect::query()
            ->where('status', 'new')
            ->whereNull('site_checked_at')
            ->where(fn ($q) => $q->whereNotIn('email_status', OutreachProspect::SENDABLE_EMAIL_STATUSES)
                ->orWhereNull('email_status')
                ->orWhereNull('findings'))
            ->orderByDesc('score')->orderBy('id');
    }

    /** Работи до $seconds секунди и връща колко са проверени сега и колко остават. */
    public function work(float $seconds = 20): array
    {
        $lock = Cache::lock('outreach-check', 120);
        if (! $lock->get()) {
            return ['checked' => 0, 'remaining' => self::pending()->count(), 'busy' => true];
        }

        $checked = 0;
        try {
            $started = microtime(true);
            while (microtime(true) - $started < $seconds && ($prospect = self::pending()->first())) {
                $this->check($prospect);
                $checked++;
            }
        } finally {
            $lock->release();
        }

        return ['checked' => $checked, 'remaining' => self::pending()->count(), 'busy' => false];
    }

    public function check(OutreachProspect $prospect): void
    {
        $values = ['site_checked_at' => now(), 'check_note' => null];

        try {
            $domain = $prospect->website ? SiteChecker::normalizeDomain($prospect->website) : null;
            if (! $domain) {
                $prospect->forceFill(['check_note' => 'Няма сайт за проверка.'] + $values)->save();

                return;
            }
            $site = preg_replace('/^www\./', '', $domain);
            $host = substr(strrchr($prospect->email, '@'), 1);

            if (self::isPlatform($site)) {
                $prospect->forceFill([
                    'email_status' => 'guess',
                    'check_note' => "Сайтът ({$site}) е голяма платформа, не на бизнеса. Проверете ръчно.",
                ] + $values)->save();

                return;
            }

            if (empty($prospect->findings)) {
                $values['findings'] = self::findings($this->checker, $domain);
            }

            if (! EmailFinder::ownDomain($host, $domain) && ! in_array($host, EmailFinder::FREE_MAIL, true)) {
                $values = ['email_status' => 'guess', 'check_note' => "Имейлът е на чужд домейн ({$host}), не на {$site}."] + $values;
            } elseif (! $this->emails->publishedOn($prospect->website, $prospect->email, self::hintPage($prospect))) {
                $values = ['email_status' => 'guess', 'check_note' => 'Имейлът не се намира на сайта им.'] + $values;
            } else {
                $status = $this->verifier->status($prospect->email);
                $values['email_status'] = $status;
                if ($status === 'guess') {
                    $values['check_note'] = "Домейнът {$host} не приема поща.";
                } elseif ($status === 'invalid') {
                    $values['check_note'] = 'Адресът не съществува (проверка на пощата).';
                }
            }
        } catch (Throwable $e) {
            report($e);
            $values['check_note'] = 'Проверката не успя: ' . mb_substr($e->getMessage(), 0, 150);
        }

        $prospect->forceFill($values)->save();
    }

    /** До три конкретни неща от проверката на сайта: първо грешките, после предупрежденията. */
    public static function findings(SiteChecker $checker, string $domain): array
    {
        $report = $checker->check($domain);

        return collect($report['checks'])
            ->filter(fn ($check) => $check['status'] !== 'ok')
            ->sortBy(fn ($check) => $check['status'] === 'fail' ? 0 : 1)
            ->take(3)
            ->map(fn ($check) => ['title' => $check['title'], 'detail' => $check['detail']])
            ->values()
            ->all();
    }

    public static function isPlatform(string $site): bool
    {
        foreach (self::PLATFORM_DOMAINS as $platform) {
            if ($site === $platform || str_ends_with($site, '.' . $platform)) {
                return true;
            }
        }

        return false;
    }

    /** Страницата, на която Harvey или търсенето са видели адреса („Имейлът е публикуван на …“). */
    private static function hintPage(OutreachProspect $prospect): ?string
    {
        return preg_match('#(https?://\S+)#u', (string) $prospect->personalization, $m) ? rtrim($m[1], '.,;)') : null;
    }
}
