<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Запис в историята на възможност: бележка или смяна на статуса. */
class OpportunityNote extends Model
{
    protected $fillable = ['opportunity_id', 'user_id', 'kind', 'body'];

    public function opportunity()
    {
        return $this->belongsTo(Opportunity::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
