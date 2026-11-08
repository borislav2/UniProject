<?php

use App\Support\ConceptWording2026;
use Illuminate\Database\Migrations\Migration;

// Собствениците (ноември 2026): „Концепция“ вместо „Демо“ за примерните сайтове. Вижте ConceptWording2026::apply().
return new class extends Migration
{
    public function up(): void
    {
        if (app()->runningUnitTests()) {
            return;
        }

        ConceptWording2026::apply();
    }

    public function down(): void
    {
        // Старите стойности са в записите `<ключ>:backup-2026-11-08-d`.
    }
};
