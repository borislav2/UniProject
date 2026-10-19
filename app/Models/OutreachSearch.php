<?php

namespace App\Models;

use App\Support\Outreach\BusinessSearch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Едно търсене от админа: град + вид бизнес. Обработва се на части, докато страницата е отворена. */
class OutreachSearch extends Model
{
    public const STATUSES = [
        'queued' => 'Чака',
        'processing' => 'Проверява сайтовете',
        'done' => 'Готово',
        'failed' => 'Неуспешно',
    ];

    protected $fillable = ['user_id', 'city', 'category', 'status', 'found', 'emails', 'calls', 'error'];

    public function businesses(): HasMany
    {
        return $this->hasMany(OutreachBusiness::class);
    }

    public function isActive(): bool
    {
        return in_array($this->status, ['queued', 'processing'], true);
    }

    public function label(): string
    {
        return (BusinessSearch::CATEGORIES[$this->category]['label'] ?? $this->category) . ', ' . $this->city;
    }
}
