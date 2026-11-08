<?php

use App\Support\PlainCopy2026;
use Illuminate\Database\Migrations\Migration;

// Собствениците (ноември 2026): без тирета и двоеточия във видимия текст. Вижте PlainCopy2026::apply().
return new class extends Migration
{
    public function up(): void
    {
        if (app()->runningUnitTests()) {
            return;
        }

        PlainCopy2026::apply();
    }

    public function down(): void
    {
        // Старите стойности са в записите `<ключ>:backup-2026-11-08-g`.
    }
};
