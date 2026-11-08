<?php

use App\Support\DepositTerms2026;
use Illuminate\Database\Migrations\Migration;

// Собствениците (08.11.2026): капаро и гаранция и при Индивидуален маркетинг. Вижте DepositTerms2026::applyIndividual().
return new class extends Migration
{
    public function up(): void
    {
        if (app()->runningUnitTests()) {
            return;
        }

        DepositTerms2026::applyIndividual();
    }

    public function down(): void
    {
        // Старите стойности са в записите `<ключ>:backup-2026-11-08-c`.
    }
};
