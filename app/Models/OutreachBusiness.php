<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Бизнес, намерен при търсене в картата. Със сайт: търсим имейл; без сайт: отива в списъка за обаждане. */
class OutreachBusiness extends Model
{
    protected $fillable = [
        'outreach_search_id', 'external_id', 'name', 'category', 'city', 'website', 'phone',
        'map_email', 'email', 'email_page', 'processed_at',
    ];

    protected $casts = ['processed_at' => 'datetime'];

    public function search(): BelongsTo
    {
        return $this->belongsTo(OutreachSearch::class, 'outreach_search_id');
    }
}
