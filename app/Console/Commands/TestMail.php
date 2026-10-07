<?php

namespace App\Console\Commands;

use App\Mail\MailTestMail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestMail extends Command
{
    protected $signature = 'creatium:test-mail {to? : Получател (по подразбиране адресът за известия за запитвания)}';

    protected $description = 'Send a test email to check that the site can really send mail';

    public function handle(): int
    {
        $to = $this->argument('to') ?: config('creatium.notify_email');

        if (! filter_var($to, FILTER_VALIDATE_EMAIL)) {
            $this->error('Невалиден имейл адрес: ' . $to);

            return self::FAILURE;
        }

        $mailer = config('mail.default');
        $this->line("Изпращане през '$mailer' към $to ...");

        if (in_array($mailer, ['log', 'array'], true)) {
            $this->warn("MAIL_MAILER=$mailer: писмата само се записват в лога и НЕ се изпращат. Настройте SMTP (bash deploy/configure-mail.sh).");
        }

        try {
            Mail::to($to)->send(new MailTestMail());
        } catch (\Throwable $e) {
            $this->error('Неуспех: ' . $e->getMessage());

            return self::FAILURE;
        }

        $this->info($mailer === 'smtp' ? 'Писмото е предадено на SMTP сървъра. Проверете пощата (и спама).' : 'Готово (но не е реално изпратено, виж предупреждението).');

        return self::SUCCESS;
    }
}
