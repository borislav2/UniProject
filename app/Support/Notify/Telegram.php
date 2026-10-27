<?php

namespace App\Support\Notify;

use App\Models\OutreachProspect;
use App\Models\Project;
use App\Support\Booking;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Известия до телефона в Telegram: ново запитване (с часа за консултация, ако има), клик по първото писмо.
 * Без настроен бот не прави нищо. Изпраща се след отговора към посетителя (defer), грешка само се записва в лога.
 */
class Telegram
{
    public static function configured(): bool
    {
        return filled(config('services.telegram.bot_token')) && filled(config('services.telegram.chat_id'));
    }

    public static function send(string $html): bool
    {
        if (! self::configured()) {
            return false;
        }

        try {
            $response = Http::asJson()->timeout(8)->post(self::url('sendMessage'), [
                'chat_id' => config('services.telegram.chat_id'),
                'text' => $html,
                'parse_mode' => 'HTML',
                'disable_web_page_preview' => true,
            ]);
            if (! $response->successful()) {
                Log::warning('Telegram: ' . $response->status() . ' ' . $response->json('description'));
            }

            return $response->successful();
        } catch (Throwable $e) {
            Log::warning('Telegram: ' . $e->getMessage());

            return false;
        }
    }

    /** Изпраща след отговора към посетителя, за да не го бави. */
    public static function later(string $html): void
    {
        if (self::configured()) {
            defer(fn () => self::send($html));
        }
    }

    public static function url(string $method, ?string $token = null): string
    {
        return rtrim(config('services.telegram.api_url'), '/') . '/bot' . ($token ?? config('services.telegram.bot_token')) . '/' . $method;
    }

    /** „Ново запитване“, „Нов клиент от Крео“ или „Проверка на сайт“ с данните за връзка и линк към админа. */
    public static function newLead(Project $lead, string $title = 'Ново запитване', bool $defer = true): void
    {
        $lines = ['🆕 <b>' . e($title) . '</b>', e(str_replace('Запитване от ', '', $lead->name))];
        if ($lead->client_phone) {
            $lines[] = '📞 ' . e($lead->client_phone);
        }
        if ($lead->client_email) {
            $lines[] = '✉️ ' . e($lead->client_email);
        }
        if ($lead->serviceLabel()) {
            $lines[] = e($lead->serviceLabel());
        }
        if ($lead->website_url) {
            $lines[] = '🌐 ' . e($lead->website_url);
        }
        if ($lead->consultation_at) {
            $lines[] = '🗓 Консултация: <b>' . e(Booking::label($lead->consultation_at)) . '</b>';
        }
        if ($lead->lead_channel) {
            $lines[] = 'Канал: ' . e($lead->lead_channel);
        }
        $lines[] = route('admin.projects.show', $lead);

        // Вътре в код, който вече върви след отговора (проверката на сайт), пращаме веднага.
        $defer ? self::later(implode("\n", $lines)) : self::send(implode("\n", $lines));
    }

    /** Първи клик на бизнес по линк в писмото (и всеки клик към „Запазване на час“). */
    public static function click(OutreachProspect $prospect, string $link): void
    {
        self::later(implode("\n", [
            '👀 <b>Кликна по писмото</b>',
            e($prospect->displayName()) . ' · ' . e($prospect->email),
            'Линк: ' . e(OutreachProspect::CLICK_LINKS[$link] ?? $link),
            route('admin.outreach.index', ['clicked' => 1]),
        ]));
    }
}
