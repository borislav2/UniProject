<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Едно съобщение от разговора, както го вижда посетителят (за админа). source: ai, faq или null за посетителя. */
class ChatMessage extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = ['chat_conversation_id', 'role', 'text', 'source'];
}
