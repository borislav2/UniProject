<?php

namespace App\Console\Commands;

use App\Support\Seo\SiteHealth;
use Illuminate\Console\Command;

/** Обхожда нашия сайт отвътре (без заявки навън) и записва отчет за „SEO здраве“. */
class SeoHealth extends Command
{
    protected $signature = 'creatium:seo-health {--top=5 : Колко примера на проблем да покаже}';

    protected $description = 'Crawl our own site internally (no outside requests) and store the SEO health report shown in the admin.';

    public function handle(): int
    {
        if (SiteHealth::running()) {
            $this->warn('Проверката вече върви (друг процес). Опитайте след малко.');

            return self::FAILURE;
        }
        $this->line('Обхождам сайта отвътре...');
        $report = SiteHealth::runLocked();
        if (! $report) {
            $this->error('Проверката не успя: ' . (SiteHealth::state()['last_error'] ?? 'няма подробности') . '.');

            return self::FAILURE;
        }

        $d = $report->data;
        $this->info("Оценка {$report->score}/100, {$report->pages} адреса (sitemap {$d['sitemap']}), {$report->errors} грешки, {$report->warnings} предупреждения, {$report->seconds} сек.");
        $rows = [];
        foreach ($d['issues'] as $code => $items) {
            [$sev, $title] = SiteHealth::ISSUES[$code];
            $rows[] = [$sev, $title, $d['counts'][$code] ?? count($items), collect($items)->take((int) $this->option('top'))->map(fn ($i) => $i['url'])->implode(', ')];
        }
        $this->table(['Тежест', 'Проблем', 'Брой', 'Примери'], $rows);

        return self::SUCCESS;
    }
}
