<?php

use App\Support\AboutCopy2026;
use Illuminate\Database\Migrations\Migration;

// Собствениците (08.11.2026): новият текст на „За нас“ и в админ редакциите. Вижте App\Support\AboutCopy2026.
return new class extends Migration
{
    public function up(): void
    {
        if (app()->runningUnitTests()) {
            return;
        }

        AboutCopy2026::apply();
    }

    public function down(): void
    {
        // Старите стойности са в записите `<ключ>:backup-2026-11-08-e`.
    }
};
