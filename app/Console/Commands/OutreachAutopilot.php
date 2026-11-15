<?php

namespace App\Console\Commands;

use App\Support\Outreach\Autopilot;
use Illuminate\Console\Command;

/** Пуска стъпки на автопилота ръчно (без чакане 10 минути и без работно време). Намира и подготвя чернови; никога не праща писма. */
class OutreachAutopilot extends Command
{
    protected $signature = 'creatium:autopilot {--steps=1 : Колко стъпки да направи (всяка е порция работа по търсене)}';

    protected $description = 'Run outreach autopilot steps (find, score, draft). Never sends any email.';

    public function handle(Autopilot $autopilot): int
    {
        if (! Autopilot::enabled()) {
            $this->warn('Автопилотът е изключен или на пауза (OUTREACH_AUTOPILOT=true в .env, после php artisan config:cache).');

            return self::SUCCESS;
        }

        $steps = max(1, (int) $this->option('steps'));
        for ($i = 1; $i <= $steps; $i++) {
            $result = $autopilot->tick(force: true);
            $this->line(sprintf('Стъпка %d: %s%s', $i, $result['status'], isset($result['search']) ? ' · ' . $result['search'] : '') . (isset($result['drafts']) ? " · чернови {$result['drafts']}" : '') . (! empty($result['error']) ? ' · ' . $result['error'] : ''));
            if (in_array($result['status'], ['daily_new', 'daily_searches', 'nothing_to_search', 'busy', 'error', 'off'], true)) {
                break;
            }
        }
        $this->info(sprintf('Днес: %d нови бизнеса, %d търсения, %d страници от Serper. Чакат одобрение: %d. Нищо не е изпратено.', Autopilot::newToday(), Autopilot::searchesToday(false), Autopilot::pagesToday(), Autopilot::waiting()));

        return self::SUCCESS;
    }
}
