<?php

namespace App\Models\Concerns;

/**
 * JSON columns keyed by locale ({"bg": "...", "en": "..."}), listed in $translatable and cast to arrays.
 */
trait HasTranslations
{
    public function initializeHasTranslations(): void
    {
        foreach ($this->translatable as $field) {
            $this->mergeCasts([$field => 'array']);
        }
    }

    /** Value in the given (or current) language, or null when that language is empty. */
    public function tr(string $field, ?string $locale = null): ?string
    {
        $value = ($this->{$field} ?? [])[$locale ?? app()->getLocale()] ?? null;

        return filled($value) ? $value : null;
    }

    /** Like tr(), but falls back to Bulgarian. For labels such as category names. */
    public function trOrBg(string $field, ?string $locale = null): ?string
    {
        return $this->tr($field, $locale) ?? $this->tr($field, 'bg');
    }
}
