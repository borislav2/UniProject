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
    ];
    
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
            ->orWhere('status', 'like', "%{$search}%")
            ->orWhereHas('category', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            })
            ->orWhereHas('technologies', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
    }
}
