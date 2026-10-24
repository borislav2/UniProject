<?php

namespace App\Console\Commands;

use App\Models\Post;
use App\Support\Facebook\FacebookPublisher;
use Illuminate\Console\Command;

/** Пуска една статия във Facebook: следващата по ред (както автоматиката) или посочена с --post. */
class FacebookPublish extends Command
{
    protected $signature = 'creatium:facebook-publish {--post= : ID на статия, която да се пусне веднага}';

    protected $description = 'Publish the next blog post (or a given one) to the Facebook page';

    public function handle(FacebookPublisher $publisher): int
    {
        if (! FacebookPublisher::configured()) {
            $this->error('Facebook не е свързан. Пуснете: php artisan creatium:facebook-connect');

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

        $this->info('Публикувано: ' . $post->tr('title', 'bg') . ' → ' . FacebookPublisher::postUrl($post));

        return self::SUCCESS;
    }
}
