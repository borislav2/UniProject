<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Запис в историята на запитване/проект: бележка, разговор или смяна на етапа. */
class ProjectNote extends Model
{
    public const KINDS = [
        'note' => 'Бележка',
        'call' => 'Разговор',
        'stage' => 'Етап',
    ];

    protected $fillable = ['project_id', 'user_id', 'kind', 'body'];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
