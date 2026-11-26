<?php

namespace App\Console\Commands;

use App\Models\Project;
use App\Support\SiteCheck\FollowUps;
use Illuminate\Console\Command;

class SiteCheckFollowUps extends Command
{
    protected $signature = 'creatium:site-check-followups {--dry-run : Само покажи кой е на ред, без да пращаш} {--ignore-hours : Прати и извън часовете 09:00 до 19:00}';

    protected $description = 'Send the follow-up emails after the free website check (day 2 tip, day 5 offer, day 14 check again)';

    public function handle(FollowUps $followUps): int
    {
        if ($this->option('dry-run')) {
            $rows = FollowUps::candidates()->get()->map(fn (Project $p) => [$p->id, $p->client_email, FollowUps::due($p) ?? '-'])->all();
            $this->table(['Проект', 'Имейл', 'Стъпка за днес'], $rows);

            return self::SUCCESS;
        }

        $sent = $followUps->run(null, (bool) $this->option('ignore-hours'));
        $this->info('Изпратени писма ' . $sent . '.');

        return self::SUCCESS;
    }
}
