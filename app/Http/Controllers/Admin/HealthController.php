<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\Admin\SiteMonitor;
use App\Support\Chat\ChatAssistant;
use App\Support\Facebook\FacebookPublisher;
use App\Support\Notify\Telegram;
use App\Support\Outreach\Outreach;
use Illuminate\Support\Carbon;

/**
 * „Здраве на системата“: всичко, което трябва да е настроено и да работи, на едно място.
 * Всеки ред е ok / warn / off (по желание) с какво да се направи. Ключове и пароли не се показват.
 */
class HealthController extends Controller
{
    public function __invoke()
    {
        $checks = [];
        $add = function (string $group, string $name, string $state, string $text, ?string $fix = null) use (&$checks) {
            $checks[$group][] = compact('name', 'state', 'text', 'fix');
        };

        $mailer = config('mail.default');
        $add('Известия', 'Имейл при ново запитване', in_array($mailer, ['log', 'array'], true) ? 'warn' : 'ok',
            in_array($mailer, ['log', 'array'], true) ? "Пощата е „{$mailer}“: писмата не пристигат, запитванията се виждат само в админа." : 'Пращат се до ' . config('creatium.notify_email') . '.',
            in_array($mailer, ['log', 'array'], true) ? 'bash deploy/configure-mail.sh, после php artisan creatium:test-mail' : null);
        $add('Известия', 'Telegram', Telegram::configured() ? 'ok' : 'warn',
            Telegram::configured() ? 'Новите запитвания и кликовете идват в Telegram.' : 'Не е свързан: научавате за запитванията само като отворите админа.',
            Telegram::configured() ? null : 'php artisan creatium:telegram-connect (tools/telegram/README.md)');

        $add('Продажби', 'Поща за първи писма', Outreach::configured() ? 'ok' : 'warn',
            Outreach::configured() ? 'Подател: ' . config('creatium.outreach.from_address') . ', до ' . config('creatium.outreach.daily_limit') . ' писма на ден.' : 'Не е настроена: писмата до потенциални клиенти са спрени.',
            Outreach::configured() ? null : 'bash deploy/configure-outreach-mail.sh');
        $add('Продажби', 'Търсене в Google Maps', filled(config('services.serper.key')) ? 'ok' : 'off',
            filled(config('services.serper.key')) ? 'Търсенето на бизнеси е през Google Maps.' : 'Без ключ търсенето е в OpenStreetMap (по-малко бизнеси със сайт).', filled(config('services.serper.key')) ? null : 'SERPER_API_KEY в .env');
        $add('Продажби', 'Проверка на имейли (Reoon)', filled(config('services.reoon.key')) ? 'ok' : 'off',
            filled(config('services.reoon.key')) ? 'Имейлите се проверяват през Reoon.' : 'По желание. Без него се проверява само дали домейнът приема поща.');

        $chat = ChatAssistant::providerName();
        $add('Сайт', 'Чат Крео', ! config('creatium.chat.enabled') ? 'off' : ($chat ? 'ok' : 'warn'),
            ! config('creatium.chat.enabled') ? 'Чатът е изключен (CHAT_ENABLED).' : ($chat ? "Отговаря с AI ({$chat})." : 'Няма AI ключ: Крео отговаря само от честите въпроси.'),
            config('creatium.chat.enabled') && ! $chat ? 'MISTRAL_API_KEY или ANTHROPIC_API_KEY в .env' : null);
        $paused = FacebookPublisher::paused();
        $add('Сайт', 'Блог във Facebook', ! FacebookPublisher::configured() ? 'off' : ($paused ? 'warn' : 'ok'),
            ! FacebookPublisher::configured() ? 'Не е свързан: статиите не излизат във Facebook сами.' : ($paused ? 'Спряно временно: ' . $paused : 'Статиите излизат автоматично.'),
            ! FacebookPublisher::configured() || $paused ? 'php artisan creatium:facebook-connect (tools/facebook/README.md)' : null);
        $add('Сайт', 'Google Tag Manager', filled(config('creatium.gtm_id')) ? 'ok' : 'warn',
            filled(config('creatium.gtm_id')) ? 'Контейнер ' . config('creatium.gtm_id') . ', зарежда се след съгласие.' : 'Няма GTM: няма статистика и рекламно проследяване.');
        $sites = SiteMonitor::watched()->get();
        $broken = $sites->filter(fn ($p) => SiteMonitor::problems($p))->count();
        $add('Сайт', 'Сайтове на клиенти', $broken ? 'warn' : ($sites->isEmpty() ? 'off' : 'ok'),
            $sites->isEmpty() ? 'Няма клиенти на абонамент със сайт за наблюдение.' : "Наблюдават се {$sites->count()}, с проблем: {$broken}.");

        $add('Сървър', 'Режим', app()->isProduction() && config('app.debug') ? 'warn' : 'ok',
            app()->environment() . (config('app.debug') ? ', с показване на грешки' : ''), app()->isProduction() && config('app.debug') ? 'APP_DEBUG=false в .env' : null);
        $add('Сървър', 'Адрес', str_starts_with(config('app.url'), 'https://') ? 'ok' : 'warn', config('app.url'));
        $build = public_path('build/manifest.json');
        $add('Сървър', 'Последно качване', is_file($build) ? 'ok' : 'warn', is_file($build) ? Carbon::createFromTimestamp(filemtime($build))->timezone('Europe/Sofia')->format('d.m.Y H:i') : 'Няма build (npm run build).');
        $free = @disk_free_space(base_path());
        $add('Сървър', 'Свободно място', $free === false ? 'off' : ($free < 1024 ** 3 ? 'warn' : 'ok'), $free === false ? 'Не може да се провери.' : number_format($free / 1024 ** 3, 1, ',', ' ') . ' GB');
        $add('Сървър', 'Запис в storage', is_writable(storage_path('logs')) ? 'ok' : 'warn', is_writable(storage_path('logs')) ? 'Наред.' : 'Няма права за запис в storage/logs.');
        $backups = is_readable('/var/backups/creatium') ? glob('/var/backups/creatium/db-*.sql.gz') : null;
        $last = $backups ? max(array_map('filemtime', $backups)) : null;
        $add('Сървър', 'Резервно копие', $backups === null ? 'off' : ($last && $last > now()->subDays(2)->timestamp ? 'ok' : 'warn'),
            $backups === null ? 'Не се вижда оттук (/var/backups/creatium). Проверете с bash deploy/backup.sh.' : ($last ? 'Последно: ' . Carbon::createFromTimestamp($last)->timezone('Europe/Sofia')->format('d.m.Y H:i') : 'Няма копия.'),
            $backups !== null && (! $last || $last < now()->subDays(2)->timestamp) ? 'bash deploy/backup.sh (и ежедневно в cron на root)' : null);

        return view('admin.health', ['checks' => $checks]);
    }
}
