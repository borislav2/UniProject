<?php

use App\Support\PackagesCards2026;
use Illuminate\Database\Migrations\Migration;

// Собствениците (ноември 2026): кратки карти на /paketi. Вижте PackagesCards2026::apply().
return new class extends Migration
{
    public function up(): void
    {
        if (app()->runningUnitTests()) {
            return;
        }

        PackagesCards2026::apply();
    }

    public function down(): void
    {
        // Старата стойност е в записа `plans:backup-2026-11-08-h`.
    }
};
