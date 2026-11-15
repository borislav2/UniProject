<?php

namespace App\Models;

use App\Support\Outreach\BusinessSearch;
use App\Support\Outreach\Markets;
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

    protected $fillable = ['user_id', 'city', 'category', 'status', 'found', 'emails', 'calls', 'error', 'country', 'language', 'serper_pages'];

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
        $label = (BusinessSearch::CATEGORIES[$this->category]['label'] ?? $this->category) . ', ' . $this->city;

        return $this->market() === Markets::DEFAULT ? $label : $label . ' (' . Markets::label($this->country) . ')';
    }

    public function market(): string
    {
        return Markets::normalize($this->country);
    }

    /** Търсенията на автопилота нямат потребител. */
    public function byAutopilot(): bool
    {
        return $this->user_id === null;
    }
}
