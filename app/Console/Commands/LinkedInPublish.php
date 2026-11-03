<?php

namespace App\Console\Commands;

use App\Models\Post;
use App\Support\LinkedIn\LinkedInPublisher;
use Illuminate\Console\Command;

/** Пуска една статия в LinkedIn: следващата по ред (както автоматиката) или посочена с --post. */
class LinkedInPublish extends Command
{
    protected $signature = 'creatium:linkedin-publish {--post= : ID на статия, която да се пусне веднага}';

    protected $description = 'Publish the next blog post (or a given one) to the LinkedIn company page';

    public function handle(LinkedInPublisher $publisher): int
    {
        if (! LinkedInPublisher::configured()) {
            $this->error('LinkedIn не е свързан. Пуснете: php artisan creatium:linkedin-connect');

            return self::FAILURE;
        }
        if (LinkedInPublisher::expired()) {
            $this->error('Ключът за LinkedIn е изтекъл. Подновете го: php artisan creatium:linkedin-connect');

            return self::FAILURE;
        }

        $post = $this->option('post') ? Post::find($this->option('post')) : $publisher->due();
        if (! $post) {
            $this->info('Няма статия за пускане в момента (по-старите излизат по една на ден между 09:00 и 20:00).');

            return self::SUCCESS;
        }

        if ($error = $publisher->publish($post)) {
            $this->error($post->tr('title', 'bg') . ': ' . $error);

            return self::FAILURE;
        }

        $this->info('Публикувано: ' . $post->tr('title', 'bg') . ' → ' . LinkedInPublisher::postUrl($post));

        return self::SUCCESS;
    }
}
