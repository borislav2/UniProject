<?php

namespace App\Console\Commands;

use App\Support\Outreach\ProspectChecker;
use Illuminate\Console\Command;

/** Проверява сайта и имейла на непроверените бизнеси в „Потенциални клиенти“. Нищо не изпраща. */
class CheckOutreachProspects extends Command
{
    protected $signature = 'creatium:outreach-check {--limit=0 : Най-много толкова бизнеса (0 = всички)}';

    protected $description = 'Check the site and confirm the email of unchecked outreach prospects. Sends nothing.';

    public function handle(ProspectChecker $checker): int
    {
        $limit = (int) $this->option('limit');
        $done = 0;
        while (($limit === 0 || $done < $limit) && ($prospect = ProspectChecker::pending()->first())) {
            $checker->check($prospect);
            $prospect->refresh();
            $this->line(sprintf('%s <%s>: %s%s', $prospect->displayName(), $prospect->email, $prospect->email_status, $prospect->check_note ? ' · ' . $prospect->check_note : ''));
            $done++;
        }
        $this->info("Проверени: {$done}. Остават: " . ProspectChecker::pending()->count() . '. Нищо не е изпратено.');

        return self::SUCCESS;
    }
}
