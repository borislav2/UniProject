<?php

namespace App\Support\Chat;

use App\Mail\NewLeadMail;
use App\Models\Category;
use App\Models\ChatConversation;
use App\Models\Project;
use App\Support\LeadAttribution;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Запитване от чата: същото като от формата (Project със source = website, категория „Ново запитване“,
 * имейл до екипа), с услуга „Запитване от чата“ и връзка към разговора. Един разговор дава едно запитване:
 * ако посетителят даде данните си пак, запитването се обновява.
 */
class ChatLeads
{
    public const SERVICE = 'Запитване от чата';

    /**
     * @param array{name?: ?string, phone?: ?string, email?: ?string, business?: ?string, need?: ?string} $data
     * @return string|null грешка за посетителя или null при успех
     */
    public static function save(ChatConversation $chat, array $data, ?array $attribution): ?string
    {
        $phone = trim((string) ($data['phone'] ?? ''));
        $email = mb_strtolower(trim((string) ($data['email'] ?? '')));
        $phoneOk = $phone !== '' && preg_match('/^[0-9+()\-\s]{6,25}$/', $phone);
        $emailOk = $email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL);
        if (! $phoneOk && ! $emailOk) {
            return 'A valid phone number or email address is needed.';
        }

        $name = mb_substr(trim((string) ($data['name'] ?? '')) ?: 'посетител от чата', 0, 120);
        $lines = array_filter([
            trim((string) ($data['need'] ?? '')),
            filled($data['business'] ?? null) ? 'Бизнес: ' . trim($data['business']) : null,
            'Разговор в чата: ' . route('admin.chats.show', $chat),
        ]);

        $values = [
            'description' => mb_substr(implode("\n\n", $lines), 0, 5000),
            'client_phone' => $phoneOk ? $phone : null,
            'client_email' => $emailOk ? $email : null,
        ];

        if ($chat->lead) {
            $chat->lead->update(array_filter($values) + ['name' => 'Запитване от ' . $name]);

            return null;
        }

        $category = Category::firstOrCreate(
            ['name' => 'Ново запитване'],
            ['description' => 'Автоматично създадени запитвания от сайта, изчакващи категоризация']
        );

        $lead = Project::create($values + [
            'name' => 'Запитване от ' . $name,
            'start_date' => now()->toDateString(),
            'status' => 'Planning',
            'manager' => 'Неразпределен',
            'category_id' => $category->id,
            'service' => self::SERVICE,
            'locale' => $chat->locale,
            'source' => 'website',
            'lead_channel' => LeadAttribution::channel($attribution),
            'utm_source' => $attribution['utm_source'] ?? null,
            'utm_medium' => $attribution['utm_medium'] ?? null,
            'utm_campaign' => $attribution['utm_campaign'] ?? null,
            'referrer' => $attribution['referrer'] ?? null,
        ]);
        $chat->update(['lead_id' => $lead->id]);
        $chat->setRelation('lead', $lead);

        try {
            Mail::to(config('creatium.notify_email'))->send(new NewLeadMail($lead));
            $lead->forceFill(['email_sent_at' => now()])->save();
        } catch (\Throwable $e) {
            Log::error('Chat lead notification email failed: ' . $e->getMessage(), ['project_id' => $lead->id]);
        }
        \App\Support\Notify\Telegram::newLead($lead, 'Нов клиент от чата с Крео');

        return null;
    }
}
