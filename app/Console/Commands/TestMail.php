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

            if (str_contains($e->getMessage(), '535') || str_contains($e->getMessage(), 'BadCredentials')) {
                $this->newLine();
                $this->warn('Google не приема входа. Най-честите причини:');
                $this->line(' 1. Грешна парола. Нужна е ПАРОЛА НА ПРИЛОЖЕНИЕ (16 малки букви), не паролата на акаунта.');
                $this->line('    Пишете я внимателно: телефонната клавиатура слага главна буква или поправя думи.');
                $this->line(' 2. Паролата е създадена за друг акаунт. Трябва да е за същия акаунт, с който влизате (MAIL_USERNAME).');
                $this->line(' 3. Няма двустепенна проверка или админът на Workspace не разрешава пароли на приложения.');
                $this->line(' 4. hello@ е псевдоним/група, а не потребител. Влезте с истински акаунт, а hello@ задайте за подател:');
                $this->line('    bash deploy/configure-mail.sh smtp.gmail.com акаунт@creatiumlab.com hello@creatiumlab.com');
            }

            return self::FAILURE;
        }

        $this->info($mailer === 'smtp' ? 'Писмото е предадено на SMTP сървъра. Проверете пощата (и спама).' : 'Готово (но не е реално изпратено, виж предупреждението).');

        return self::SUCCESS;
    }
}
