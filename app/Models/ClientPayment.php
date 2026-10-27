<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Месечно плащане на клиент на абонамент. period е месецът, за който е плащането („2026-10“). */
class ClientPayment extends Model
{
    protected $fillable = ['project_id', 'user_id', 'period', 'amount', 'paid_on', 'note'];

    protected $casts = ['paid_on' => 'date', 'amount' => 'decimal:2'];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
