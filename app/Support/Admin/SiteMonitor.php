<?php

namespace App\Support\Admin;

use App\Models\Project;
use App\Support\Notify\Telegram;
use App\Support\SiteCheck\HostResolver;
use App\Support\SiteCheck\SiteChecker;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Наблюдение на сайтовете на клиентите на абонамент: отговаря ли сайтът, кога изтича SSL сертификатът и кога домейнът.
 * Без cron: посещенията пускат по няколко проверки (MonitorClientSites), всеки сайт най-много веднъж на EVERY_HOURS.
 * При спиране на сайта или скорошно изтичане идва известие в Telegram (най-много едно на ден за сайт).
 */
class SiteMonitor
{
    public const EVERY_HOURS = 6;

    /** Предупреждаваме толкова дни преди изтичане на SSL или домейн. */
    public const WARN_DAYS = 14;

    public function __construct(private SiteChecker $checker, private HostResolver $resolver)
    {
    }

    /** Сайтовете за проверка: клиенти на абонамент с адрес на сайт. */
    public static function watched()
    {
        return Project::subscribers()->whereNotNull('website_url')->where('website_url', '!=', '');
    }

    /** Проверява до $limit сайта, чакащи най-дълго. */
    public function run(int $limit = 3): int
    {
        $lock = Cache::lock('site-monitor', 120);
        if (! $lock->get()) {
            return 0;
        }
        try {
            $due = self::watched()
                ->where(fn ($q) => $q->whereNull('monitor_checked_at')->orWhere('monitor_checked_at', '<', now()->subHours(self::EVERY_HOURS)))
                ->orderByRaw('monitor_checked_at is not null')->orderBy('monitor_checked_at')->limit($limit)->get();
            foreach ($due as $project) {
                $this->check($project);
            }

            return $due->count();
        } finally {
            $lock->release();
        }
    }

    public function check(Project $project): void
    {
        $url = $project->website_url;
        $host = strtolower((string) parse_url($url, PHP_URL_HOST));
        $wasUp = $project->monitor_status !== 'down';

        $result = $this->checker->fetch($url);
        $status = $result['response']?->status();
        $up = $status !== null && $status < 400;
        $values = [
            'monitor_status' => $up ? 'up' : 'down',
            'monitor_error' => $up ? null : ($status ? "Сайтът връща грешка {$status}" : match ($result['error'] ?? null) {
                'dns' => 'Домейнът не се намира (DNS)',
                'blocked' => 'Адресът не може да се провери',
                default => 'Сайтът не отговаря',
            }),
            'monitor_checked_at' => now(),
        ];
        if ($host !== '' && str_starts_with(strtolower($url), 'https')) {
            $values['ssl_expires_at'] = $this->sslExpiry($host) ?? $project->ssl_expires_at;
        }
        if ($host !== '' && (! $project->domain_expires_at || ! $project->monitor_checked_at || $project->monitor_checked_at->lt(now()->subDay()))) {
            $values['domain_expires_at'] = $this->domainExpiry($host) ?? $project->domain_expires_at;
        }
        $project->forceFill($values)->save();

        $this->alert($project, $wasUp);
    }

    /** Проблемите на сайта на клиента, за таблото и „Днес“ (празно = всичко е наред). */
    public static function problems(Project $project): array
    {
        $problems = [];
        if ($project->monitor_status === 'down') {
            $problems[] = $project->monitor_error ?: 'Сайтът не работи';
        }
        $soon = now()->addDays(self::WARN_DAYS);
        if ($project->ssl_expires_at && $project->ssl_expires_at->lt($soon)) {
            $problems[] = ($project->ssl_expires_at->isPast() ? 'SSL сертификатът е изтекъл ' : 'SSL сертификатът изтича ') . $project->ssl_expires_at->format('d.m.Y');
        }
        if ($project->domain_expires_at && $project->domain_expires_at->lt($soon)) {
            $problems[] = ($project->domain_expires_at->isPast() ? 'Домейнът е изтекъл ' : 'Домейнът изтича ') . $project->domain_expires_at->format('d.m.Y');
        }

        return $problems;
    }

    private function alert(Project $project, bool $wasUp): void
    {
        $problems = self::problems($project);
        $justWentDown = $wasUp && $project->monitor_status === 'down';
        $remind = $problems && (! $project->monitor_alerted_at || $project->monitor_alerted_at->lt(now()->subDay()));
        if (! $justWentDown && ! $remind) {
            return;
        }

        Telegram::send(implode("\n", [
            '⚠️ <b>Сайт на клиент</b>: ' . e($project->name),
            e(parse_url($project->website_url, PHP_URL_HOST) ?: $project->website_url),
            ...array_map('e', $problems),
            route('admin.clients.index'),
        ]));
        $project->forceFill(['monitor_alerted_at' => now()])->save();
    }

    /** Датата на изтичане на SSL сертификата (връзка към публичния IP на сайта, със SNI). */
    protected function sslExpiry(string $host): ?Carbon
    {
        $ip = $this->resolver->publicIp($host);
        if (! $ip) {
            return null;
        }
        try {
            $context = stream_context_create(['ssl' => ['capture_peer_cert' => true, 'verify_peer' => false, 'verify_peer_name' => false, 'peer_name' => $host, 'SNI_enabled' => true]]);
            $socket = @stream_socket_client("ssl://{$ip}:443", $errno, $error, 8, STREAM_CLIENT_CONNECT, $context);
            if (! $socket) {
                return null;
            }
            $cert = stream_context_get_params($socket)['options']['ssl']['peer_certificate'] ?? null;
            fclose($socket);
            $info = $cert ? openssl_x509_parse($cert) : null;

            return isset($info['validTo_time_t']) ? Carbon::createFromTimestamp($info['validTo_time_t']) : null;
        } catch (Throwable) {
            return null;
        }
    }

    /** Датата на изтичане на домейна от RDAP (за .com/.eu/.net и др.; за .bg регистърът няма RDAP и остава празно). */
    protected function domainExpiry(string $host): ?Carbon
    {
        $labels = explode('.', preg_replace('/^www\./', '', $host));
        if (count($labels) < 2 || end($labels) === 'bg') {
            return null;
        }
        $domain = implode('.', array_slice($labels, -2));
        try {
            $events = Http::acceptJson()->timeout(8)->get('https://rdap.org/domain/' . $domain)->json('events') ?? [];
            foreach ($events as $event) {
                if (($event['eventAction'] ?? null) === 'expiration' && ! empty($event['eventDate'])) {
                    return Carbon::parse($event['eventDate']);
                }
            }
        } catch (Throwable) {
        }

        return null;
    }
}
