<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Бизнес, намерен от Harvey, до който може да пишем първо писмо от админа („Потенциални клиенти“). */
class OutreachProspect extends Model
{
    public const STATUSES = [
        'new' => 'Нов',
        'sent' => 'Изпратено',
        'replied' => 'Отговори',
        'skipped' => 'Пропуснат',
        'unsubscribed' => 'Отписан',
    ];

    /**
     * Потвърдени (verified), catch-all (risky) или публикувани от самия бизнес на домейн, който приема поща (published).
     * „guess“ (налучкан или домейн без поща) не се праща.
     */
    public const SENDABLE_EMAIL_STATUSES = ['verified', 'risky', 'published'];

    public const EMAIL_STATUSES = [
        'verified' => 'потвърден',
        'risky' => 'приема всичко',
        'published' => 'публикуван от бизнеса',
        'guess' => 'непроверен',
    ];

    protected $fillable = [
        'email', 'first_name', 'last_name', 'company', 'title', 'website', 'industry', 'city',
        'personalization', 'findings', 'email_status', 'score', 'status', 'sent_at', 'unsubscribed_at',
    ];

    protected $casts = ['sent_at' => 'datetime', 'unsubscribed_at' => 'datetime', 'score' => 'integer', 'findings' => 'array'];

    public function canBeEmailed(): bool
    {
        return $this->status === 'new'
            && in_array($this->email_status, self::SENDABLE_EMAIL_STATUSES, true)
            && ! OutreachSuppression::where('email', $this->email)->exists();
    }

    public function displayName(): string
    {
        return $this->company ?: $this->email;
    }

    /** Готово наблюдение за първото писмо от проверката на сайта им. Само истински находки, без измислени. */
    public function suggestedObservation(): string
    {
        $host = $this->website ? preg_replace('/^www\./', '', (string) parse_url($this->website, PHP_URL_HOST)) : null;
        if (! $host) {
            return '';
        }
        if (empty($this->findings)) {
            return "Разгледахме сайта ви {$host}. Технически е в добро състояние, затова ви пишем за следващата стъпка: да ви намират повече хора в Google Maps и в отговорите на AI асистенти като ChatGPT.";
        }

        $lines = array_map(function ($finding) {
            $detail = preg_split('/(?<=[.!?])\s/u', trim($finding['detail']))[0] ?? '';

            return '– ' . rtrim($finding['title'], '.') . '. ' . $detail;
        }, $this->findings);

        return "Разгледахме сайта ви {$host} и забелязахме няколко неща, които ви струват клиенти:\n" . implode("\n", $lines);
    }
}
