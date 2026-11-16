<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Ключова дума, която следим на ръка (позициите се вписват или внасят от Search Console, няма външно API). */
class SeoKeyword extends Model
{
    public const PRIORITIES = ['high' => 'Висок', 'medium' => 'Среден', 'low' => 'Нисък'];

    public const LOCALES = ['bg' => 'Български', 'en' => 'English'];

    public const COUNTRIES = ['bg' => 'България', 'gb' => 'Великобритания', 'ie' => 'Ирландия', 'nl' => 'Нидерландия', 'us' => 'САЩ'];

    protected $fillable = ['keyword', 'locale', 'country', 'target', 'priority', 'note'];

    public function positions(): HasMany
    {
        return $this->hasMany(SeoKeywordPosition::class)->orderBy('date');
    }

    /** Последният запис с позиция. */
    public function last(): ?SeoKeywordPosition
    {
        return $this->positions->whereNotNull('position')->last();
    }

    /** Промяна спрямо предишния запис: положително число = по-добра позиция (по-близо до 1). */
    public function change(): ?float
    {
        $with = $this->positions->whereNotNull('position')->values();
        if ($with->count() < 2) {
            return null;
        }

        return round($with[$with->count() - 2]->position - $with->last()->position, 1);
    }

    public static function normalize(string $keyword): string
    {
        return mb_strtolower(trim(preg_replace('/\s+/u', ' ', $keyword)));
    }
}
