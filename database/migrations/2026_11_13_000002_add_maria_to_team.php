<?php

use App\Support\TeamMaria2026;
use Illuminate\Database\Migrations\Migration;

// Екипът (13.11.2026): Мария Флорова като трети член в запазените редакции на `team`. Вижте TeamMaria2026::apply().
return new class extends Migration
{
    public function up(): void
    {
        if (app()->runningUnitTests()) {
            return;
        }

        TeamMaria2026::apply();
    }

    public function down(): void
    {
        // Старите стойности са в записите `team:backup-2026-11-13`.
    }
};
