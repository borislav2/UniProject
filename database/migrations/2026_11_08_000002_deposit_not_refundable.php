<?php

use App\Support\DepositTerms2026;
use Illuminate\Database\Migrations\Migration;

// Собствениците (08.11.2026): капарото не се връща, капацитетът е на месец. Вижте App\Support\DepositTerms2026.
// В тестовете се пропуска (логиката се тества директно).
return new class extends Migration
{
    public function up(): void
    {
        if (app()->runningUnitTests()) {
            return;
        }

        DepositTerms2026::apply();
    }

    public function down(): void
    {
        // Старите стойности са в записите `<ключ>:backup-2026-11-08-b`.
    }
};
