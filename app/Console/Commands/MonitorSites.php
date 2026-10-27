<?php

namespace App\Console\Commands;

use App\Support\Admin\SiteMonitor;
use Illuminate\Console\Command;

class MonitorSites extends Command
{
    protected $signature = 'creatium:monitor-sites {--all : Провери всички сайтове сега, не само чакащите}';

    protected $description = 'Check client websites: up, SSL expiry, domain expiry (Telegram alert on problems)';

    public function handle(SiteMonitor $monitor): int
    {
        $sites = SiteMonitor::watched()->get();
        foreach ($sites as $project) {
            if ($this->option('all') || ! $project->monitor_checked_at || $project->monitor_checked_at->lt(now()->subHours(SiteMonitor::EVERY_HOURS))) {
                $monitor->check($project);
                $project->refresh();
            }
            $problems = SiteMonitor::problems($project);
            $this->line($project->name . ' (' . $project->website_url . '): ' . ($problems ? implode('; ', $problems) : 'наред'));
        }
        $this->info('Сайтове: ' . $sites->count() . '.');

        return self::SUCCESS;
    }
}
