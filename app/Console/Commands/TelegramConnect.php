<?php

namespace App\Console\Commands;

use App\Support\EnvFile;
use App\Support\Notify\Telegram;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;

/**
 * Свързва известията с Telegram: пита за ключа на бота (от @BotFather), намира чата, в който сте писали на бота
 * (личен или група), записва TELEGRAM_BOT_TOKEN / TELEGRAM_CHAT_ID в .env и праща пробно съобщение.
 */
class TelegramConnect extends Command
{
    protected $signature = 'creatium:telegram-connect';

    protected $description = 'Connect lead notifications to Telegram (stores the bot token and chat id in .env)';

    public function handle(): int
    {
        $token = trim((string) $this->secret('Ключ на бота от @BotFather (не се показва)'));
        $me = $token !== '' ? Http::acceptJson()->timeout(20)->get(Telegram::url('getMe', $token)) : null;
        if (! $me?->json('ok')) {
            $this->error('Telegram не прие ключа. Копирайте го отново от @BotFather.');

            return self::FAILURE;
        }

        $bot = '@' . $me->json('result.username');
        $this->line("Ботът е {$bot}. Отворете го в Telegram и му напишете нещо (напр. „здравей“).");
        $this->line('За обща група: добавете бота в групата и напишете съобщение там.');
        $this->ask('Натиснете Enter, когато сте написали');

        $updates = Http::acceptJson()->timeout(20)->get(Telegram::url('getUpdates', $token))->json('result') ?? [];
        $chats = collect($updates)->map(fn ($u) => $u['message']['chat'] ?? $u['my_chat_member']['chat'] ?? null)->filter()->unique('id')->values();
        if ($chats->isEmpty()) {
            $this->error("Не виждам съобщение до {$bot}. Напишете му и пуснете командата отново.");

            return self::FAILURE;
        }

        $names = $chats->map(fn ($c) => ($c['title'] ?? trim(($c['first_name'] ?? '') . ' ' . ($c['last_name'] ?? ''))) . ' (' . $c['id'] . ')')->all();
        $chosen = count($names) === 1 ? $names[0] : $this->choice('Къде да идват известията?', $names, count($names) - 1);
        $chat = $chats[array_search($chosen, $names, true)];

        EnvFile::set(['TELEGRAM_BOT_TOKEN' => $token, 'TELEGRAM_CHAT_ID' => $chat['id']]);
        config(['services.telegram.bot_token' => $token, 'services.telegram.chat_id' => $chat['id']]);
        if (app()->configurationIsCached()) {
            Artisan::call('config:cache');
        }

        if (! Telegram::send('✅ <b>Creatium Lab</b>: известията работят. Тук ще идват новите запитвания и кликовете по писмата.')) {
            $this->error('Записах настройките, но пробното съобщение не мина. Вижте storage/logs/laravel.log.');

            return self::FAILURE;
        }
        $this->info("Свързано. Пробно съобщение е изпратено в {$chosen}.");

        return self::SUCCESS;
    }
}
