<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Project extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'name',
        'description',
        'start_date',
        'end_date',
        'status',
        'manager',
        'category_id',
        'file_path',
        'client_email',
        'client_phone',
        'source',
        'service',
        'business_size',
        'website_url',
        'read_at',
        'email_sent_at',
        'consultation_at',
        'locale',
        'is_public',
        'lead_channel',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'referrer',
    ];
    
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_public' => 'boolean',
        'read_at' => 'datetime',
        'email_sent_at' => 'datetime',
        'consultation_at' => 'datetime',
    ];
    
    /** Статусите в базата са на английски (валидират се така); в админа се показват на български. */
    public const STATUSES = [
        'Planning' => ['Планиране', 'bg-yellow-100 text-yellow-800'],
        'In Progress' => ['В работа', 'bg-blue-100 text-blue-800'],
        'Completed' => ['Завършен', 'bg-green-100 text-green-800'],
        'On Hold' => ['На пауза', 'bg-orange-100 text-orange-800'],
        'Cancelled' => ['Отказан', 'bg-red-100 text-red-800'],
    ];

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status][0] ?? (string) $this->status;
    }

    public function statusClass(): string
    {
        return self::STATUSES[$this->status][1] ?? 'bg-gray-100 text-gray-800';
    }

    /** Темата от формата (new-system, modernization, support) с текста ѝ; други стойности (напр. „Проверка на сайт“) се показват както са. */
    public function serviceLabel(): ?string
    {
        return $this->service ? (config('creatium.contact_topics')[$this->service] ?? $this->service) : null;
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    
    public function technologies()
    {
        return $this->belongsToMany(Technology::class);
    }
    
    public function scopeSearch(Builder $query, string $search): Builder
    {
        return $query->where('name', 'like', "%{$search}%")
            ->orWhere('description', 'like', "%{$search}%")
            ->orWhere('manager', 'like', "%{$search}%")
            ->orWhere('client_email', 'like', "%{$search}%")
            ->orWhere('client_phone', 'like', "%{$search}%")
            ->orWhere('status', 'like', "%{$search}%")
            ->orWhereHas('category', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            })
            ->orWhereHas('technologies', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
    }
}
