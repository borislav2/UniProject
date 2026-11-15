<?php

namespace App\Console\Commands;

use App\Models\Opportunity;
use App\Support\Opportunities\Collector;
use Illuminate\Console\Command;

/** Събира възможности (обществени поръчки) от TED, Contracts Finder и Find a Tender веднага, без чакане 6 часа. Нищо не подава и не праща. */
class CollectOpportunities extends Command
{
    protected $signature = 'creatium:opportunities {--source= : ted, contracts-finder или find-a-tender (по подразбиране всички)} {--days= : Колко дни назад по дата на публикуване (по подразбиране creatium.opportunities.lookback_days)} {--top=10 : Колко най-добри да покаже}';

    protected $description = 'Fetch public tenders (TED, Contracts Finder, Find a Tender), score them and store them as opportunities. Never submits or sends anything.';

    public function handle(Collector $collector): int
    {
        $feeds = Collector::feeds();
        if ($only = $this->option('source')) {
            if (! isset($feeds[$only])) {
                $this->error('Непознат източник „' . $only . '“. Възможни: ' . implode(', ', array_keys($feeds)) . '.');

                return self::FAILURE;
            }
            $feeds = [$only => $feeds[$only]];
        }
        $days = $this->option('days') !== null ? max(1, (int) $this->option('days')) : null;

        $failed = false;
        $rows = [];
        foreach ($feeds as $key => $feed) {
            $this->line('Взимам ' . $feed->label() . '...');
            $r = $collector->run($feed, $days);
            $failed = $failed || $r['error'] !== null;
            $rows[] = [$key, $r['returned'], $r['matched'], $r['new'], $r['updated'], $r['rejected'], $r['expired'], $r['error'] ?? 'ок'];
        }
        $this->table(['Източник', 'Върнати', 'Съвпаднали', 'Нови', 'Обновени', 'Неподходящи', 'Изтекли', 'Резултат'], $rows);

        $top = Opportunity::where('kind', 'tender')->open()->orderByDesc('score')->orderBy('deadline_at')->limit(max(1, (int) $this->option('top')))->get();
        if ($top->isNotEmpty()) {
            $this->table(['Оценка', 'Заглавие', 'Държава', 'Срок', 'Източник'], $top->map(fn ($o) => [$o->score, mb_strimwidth($o->title, 0, 70, '…'), $o->countryName(), $o->deadlineText() ?? 'без срок', $o->source])->all());
        }

        return $failed ? self::FAILURE : self::SUCCESS;
    }
}
