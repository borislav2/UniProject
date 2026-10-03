<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PostCategory extends Model
{
    use HasTranslations;

    protected array $translatable = ['name', 'slug'];

    protected $fillable = ['name', 'slug'];

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    public static function findBySlug(string $slug, ?string $locale = null): ?self
    {
        return static::where('slug->' . ($locale ?? app()->getLocale()), $slug)->first();
    }
}
