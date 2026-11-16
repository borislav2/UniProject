<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class SeoKeywordPosition extends Model
{
    protected $fillable = ['seo_keyword_id', 'date', 'position', 'impressions', 'clicks'];

    protected $casts = ['position' => 'float'];

    /** Дата без час: пази се като текст Y-m-d (търсим по нея), а се чете като Carbon. */
    protected function date(): Attribute
    {
        return Attribute::make(get: fn ($value) => $value ? Carbon::parse($value) : null);
    }
}
