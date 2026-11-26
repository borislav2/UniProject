<?php

namespace App\Console\Commands;

use App\Support\SiteCheck\MonthlyChecks as Checks;
use Illuminate\Console\Command;

class MonthlyChecks extends Command
{
    protected $signature = 'creatium:monthly-checks {--limit=1 : Колко проекта да проверим}';

    protected $description = 'Run the monthly full website check for projects with the monthly check ticked and email the client a short report';

    public function handle(Checks $checks): int
    {
        $this->info('Проверени проекти ' . $checks->run(max(1, (int) $this->option('limit'))) . '.');

        return self::SUCCESS;
    }
}
