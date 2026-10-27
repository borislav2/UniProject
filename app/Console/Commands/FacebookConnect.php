<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;

/**
 * Свързва сайта с Facebook страницата: от краткия ключ от Graph API Explorer взима дългосрочен,
 * от него постоянен ключ за страницата и го записва в .env (FACEBOOK_PAGE_ID / _TOKEN / _NAME).
 * Ключовете се въвеждат скрито и не се показват; App Secret не се пази.
 */
class FacebookConnect extends Command
{
    protected $signature = 'creatium:facebook-connect';

    protected $description = 'Connect the blog to the Facebook page (stores a non-expiring Page token in .env)';

    public function handle(): int
    {
        $graph = rtrim(config('services.facebook.graph_url'), '/');
        $appId = trim((string) $this->ask('App ID (developers.facebook.com → вашето приложение → App settings → Basic)'));
        $secret = trim((string) $this->secret('App Secret (същата страница, „Show“; не се показва)'));
        $userToken = trim((string) $this->secret('User access token от Graph API Explorer (не се показва)'));
        if ($appId === '' || $secret === '' || $userToken === '') {
            $this->error('Нужни са и трите стойности.');

            return self::FAILURE;
        }

        $long = Http::acceptJson()->timeout(30)->get("$graph/oauth/access_token", [
            'grant_type' => 'fb_exchange_token', 'client_id' => $appId, 'client_secret' => $secret, 'fb_exchange_token' => $userToken,
        ]);
        if (! $long->successful() || ! $long->json('access_token')) {
            $this->error('Facebook не прие ключа: ' . ($long->json('error.message') ?? $long->status()));

            return self::FAILURE;
        }

        $pages = Http::acceptJson()->timeout(30)->get("$graph/me/accounts", [
            'fields' => 'id,name,access_token,tasks', 'access_token' => $long->json('access_token'),
        ]);
        $list = collect($pages->json('data') ?? [])->filter(fn ($page) => filled($page['access_token'] ?? null))->values();
        if ($list->isEmpty()) {
            $this->error('Няма страници с достъп. В Graph API Explorer дайте pages_show_list, pages_manage_posts и pages_read_engagement и изберете страницата.');

            return self::FAILURE;
        }

        $names = $list->map(fn ($page) => $page['name'] . ' (' . $page['id'] . ')')->all();
        $chosen = count($names) === 1 ? $names[0] : $this->choice('Коя страница?', $names, 0);
        $page = $list[array_search($chosen, $names, true)];
        if (! in_array('CREATE_CONTENT', $page['tasks'] ?? ['CREATE_CONTENT'], true)) {
            $this->warn('Внимание: акаунтът няма право да публикува в тази страница (CREATE_CONTENT).');
        }

        \App\Support\EnvFile::set(['FACEBOOK_PAGE_ID' => $page['id'], 'FACEBOOK_PAGE_TOKEN' => $page['access_token'], 'FACEBOOK_PAGE_NAME' => $page['name']]);
        if (app()->configurationIsCached()) {
            Artisan::call('config:cache');
        }

        $this->info("Свързано със страницата „{$page['name']}“. Статиите ще започнат да излизат сами (новите веднага, по-старите по една на ден).");
        $this->line('Проба веднага: php artisan creatium:facebook-publish');

        return self::SUCCESS;
    }
}
