<?php

namespace App\Console\Commands;

use App\Support\EnvFile;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;

/**
 * Свързва сайта с LinkedIn страницата на фирмата: записва в .env ключа за достъп (от инструмента за OAuth ключове
 * в LinkedIn Developer Portal, с права w_organization_social, важи 60 дни), номера на организацията и датата на изтичане.
 * Нищо не се проверява по мрежата: първата публикация показва дали ключът е верен. Ключът се въвежда скрито.
 */
class LinkedInConnect extends Command
{
    protected $signature = 'creatium:linkedin-connect';

    protected $description = 'Connect the blog to the LinkedIn company page (stores the access token, organization id and expiry in .env)';

    public function handle(): int
    {
        $token = trim((string) $this->secret('Ключ за достъп (access token от Developer Portal → Tools → OAuth token generator; не се показва)'));
        $organization = trim((string) $this->ask('Номер на организацията (числото в адреса на страницата: linkedin.com/company/<число>/admin)', (string) config('services.linkedin.organization_id') ?: null));
        $default = now('Europe/Sofia')->addDays(60)->toDateString();
        $expires = trim((string) $this->ask('До кога важи ключът (ГГГГ-ММ-ДД)', $default));

        if ($token === '' || ! ctype_digit($organization)) {
            $this->error('Нужни са ключ и номер на организацията (само цифри).');

            return self::FAILURE;
        }
        if (! $this->validDate($expires)) {
            $this->error('Датата трябва да е във вида ГГГГ-ММ-ДД.');

            return self::FAILURE;
        }

        EnvFile::set(['LINKEDIN_ORGANIZATION_ID' => $organization, 'LINKEDIN_ACCESS_TOKEN' => $token, 'LINKEDIN_TOKEN_EXPIRES' => $expires]);
        if (app()->configurationIsCached()) {
            Artisan::call('config:cache');
        }

        $this->info("Записано. Статиите ще излизат в LinkedIn сами (новите веднага, по-старите по една на ден) до {$expires}.");
        $this->line('Ключът не е проверен по мрежата. Проба веднага: php artisan creatium:linkedin-publish');
        $this->line('Подновете ключа преди тази дата със същата команда.');

        return self::SUCCESS;
    }

    private function validDate(string $value): bool
    {
        try {
            return preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) === 1 && Carbon::createFromFormat('Y-m-d', $value)->format('Y-m-d') === $value;
        } catch (\Throwable) {
            return false;
        }
    }
}
