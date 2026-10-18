<?php

namespace App\Console\Commands;

use App\Support\Outreach\Outreach;
use Illuminate\Console\Command;

/** Внася списъка на Harvey в „Потенциални клиенти“. Нищо не изпраща. */
class ImportOutreachProspects extends Command
{
    protected $signature = 'creatium:outreach-import
        {path? : CSV от `harvey export` (по подразбиране creatium.outreach.harvey_csv)}
        {--calls : Внася бизнесите без сайт (за обаждане) от creatium.outreach.harvey_no_website_csv}';

    protected $description = 'Import Harvey prospects into the admin (Потенциални клиенти). Sends nothing.';

    public function handle(): int
    {
        $calls = $this->option('calls');
        $path = $this->argument('path') ?: config($calls ? 'creatium.outreach.harvey_no_website_csv' : 'creatium.outreach.harvey_csv');
        if (! is_readable($path)) {
            $this->error("Файлът не се чете: $path");

            return self::FAILURE;
        }

        $result = $calls ? Outreach::importCalls($path) : Outreach::import($path);
        $this->info("Внесени: {$result['added']} нови, {$result['updated']} обновени, {$result['skipped']} пропуснати" . (isset($result['foreign']) ? ", {$result['foreign']} извън България" : '') . '. Нищо не е изпратено.');

        return self::SUCCESS;
    }
}
