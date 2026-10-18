<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Чернова на отговор на отзив в Google за клиент. Публикува се ръчно след преглед. */
class ReviewReply extends Model
{
    public const TONES = [
        'friendly' => 'Приятелски',
        'formal' => 'Официален',
    ];

    public const SOURCES = [
        'ai' => 'Claude',
        'template' => 'Шаблон',
    ];

    protected $fillable = [
        'user_id', 'business_name', 'business_type', 'reviewer_name', 'rating', 'review_text',
        'tone', 'notes', 'signature', 'reply_text', 'source',
    ];

    protected $casts = ['rating' => 'integer'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
