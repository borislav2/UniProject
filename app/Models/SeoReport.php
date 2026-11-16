<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Един резултат от App\Support\Seo\SiteHealth (вътрешно обхождане на сайта). */
class SeoReport extends Model
{
    protected $fillable = ['ran_at', 'score', 'pages', 'errors', 'warnings', 'seconds', 'data'];

    protected $casts = ['ran_at' => 'datetime', 'data' => 'array'];

    public static function latestOne(): ?self
    {
        return static::orderByDesc('ran_at')->orderByDesc('id')->first();
    }
}
