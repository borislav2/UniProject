<?php

namespace App\Console\Commands;

use App\Support\Seo\IndexNow;
use Illuminate\Console\Command;

/** Подава адреси към IndexNow (Bing, Yandex, Seznam). Без опция само новите статии, с --all всеки адрес от sitemap. */
class IndexNowSubmit extends Command
{
    protected $signature = 'creatium:indexnow {--all : Подай всеки адрес от sitemap}';

    protected $description = 'Submit new posts (or every sitemap URL with --all) to IndexNow, which feeds Bing and so ChatGPT search and Copilot.';

    public function handle(): int
    {
        if (! IndexNow::enabled()) {
            $this->warn('IndexNow е изключен (INDEXNOW_ENABLED=false или няма ключ).');

            return self::FAILURE;
        }

        if ($this->option('all')) {
            $urls = IndexNow::sitemapUrls();
            if (! IndexNow::submit($urls)) {
                $this->error('Подаването не успя: ' . (IndexNow::state()['last_error'] ?? 'няма подробности') . '.');

                return self::FAILURE;
            }
            $this->info(count($urls) . ' адреса са подадени.');

            return self::SUCCESS;
        }

        $count = IndexNow::submitNewPosts();
        $error = IndexNow::state()['last_error'] ?? null;
        if ($error && $count === 0 && IndexNow::pendingPosts()->exists()) {
            $this->error('Подаването не успя: ' . $error . '.');

            return self::FAILURE;
        }
        $this->info($count . ' нови статии са подадени.');

        return self::SUCCESS;
    }
}
