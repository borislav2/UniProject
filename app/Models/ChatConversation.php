<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/** Разговор в чата на сайта. Изтрива се след creatium.chat.retention_days дни без нови съобщения. */
class ChatConversation extends Model
{
    protected $fillable = ['public_id', 'locale', 'page', 'ip_hash', 'api_messages', 'user_messages', 'lead_id'];

    protected $casts = ['api_messages' => 'array'];

    protected static function booted(): void
    {
        static::creating(fn (self $chat) => $chat->public_id ??= (string) Str::uuid());
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ChatMessage::class)->orderBy('id');
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'lead_id');
    }

    /** Трие старите разговори. Вика се при всеки нов разговор и при отваряне на списъка в админа. */
    public static function prune(): int
    {
        return static::where('updated_at', '<', now()->subDays((int) config('creatium.chat.retention_days', 30)))->delete();
    }
}
