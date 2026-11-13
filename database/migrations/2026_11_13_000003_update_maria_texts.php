<?php

use App\Support\TeamMariaTexts2026;
use Illuminate\Database\Migrations\Migration;

// Мария Флорова (13.11.2026): нови текстове в запазените редакции на `team`. Вижте TeamMariaTexts2026::apply().
return new class extends Migration
{
    public function up(): void
    {
        if (app()->runningUnitTests()) {
            return;
        }

        TeamMariaTexts2026::apply();
    }

    public function down(): void
    {
        // Старите стойности са в записите `team:backup-2026-11-13-b`.
    }
};
