<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Адрес, на който не пишем повече: отписал се е или е изключен ръчно. */
class OutreachSuppression extends Model
{
    protected $fillable = ['email', 'reason'];

    public static function add(string $email, string $reason): void
    {
        static::firstOrCreate(['email' => mb_strtolower(trim($email))], ['reason' => $reason]);
    }
}
