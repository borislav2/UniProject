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

    /** Само адреси, които Harvey е потвърдил (verified) или са catch-all (risky); „guess“ не се пращат. */
    public const SENDABLE_EMAIL_STATUSES = ['verified', 'risky'];

    protected $fillable = [
        'email', 'first_name', 'last_name', 'company', 'title', 'website', 'industry',
        'personalization', 'email_status', 'score', 'status', 'sent_at', 'unsubscribed_at',
    ];

    protected $casts = ['sent_at' => 'datetime', 'unsubscribed_at' => 'datetime', 'score' => 'integer'];

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
}
