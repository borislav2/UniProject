<?php

namespace App\Models;

use App\Support\ClientEmails;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientEmail extends Model
{
    protected $fillable = ['project_id', 'user_id', 'template', 'to_email', 'subject', 'data', 'sent_at', 'error'];

    protected $casts = [
        'data' => 'array',
        'sent_at' => 'datetime',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function templateLabel(): string
    {
        return ClientEmails::label($this->template);
    }
}
