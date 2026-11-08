<?php

use App\Support\PlanCaption2026;
use Illuminate\Database\Migrations\Migration;

// Собствениците (09.11.2026): по-добър текст под цената на втория пакет. Вижте App\Support\PlanCaption2026.
return new class extends Migration
{
    public function up(): void
    {
        if (app()->runningUnitTests()) {
            return;
        }

        PlanCaption2026::apply();
    }

    public function down(): void
    {
        // Старата стойност е в `plans:backup-2026-11-09-a`.
    }
};
