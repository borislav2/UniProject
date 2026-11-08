<?php

use App\Support\FinalPrices2026;
use Illuminate\Database\Migrations\Migration;

// Окончателните цени на собствениците (08.11.2026) в текстовете, които админът е редактирал (content_blocks): вижте App\Support\FinalPrices2026.
// Всеки променен запис първо се копира в `<ключ>:backup-2026-11-08`; променят се само ценовите ключове. В тестовете се пропуска (логиката се тества директно).
return new class extends Migration
{
    public function up(): void
    {
        if (app()->runningUnitTests()) {
            return;
        }

        FinalPrices2026::apply();
    }

    public function down(): void
    {
        // Старите стойности са в записите `<ключ>:backup-2026-11-08`; връщането е ръчно от тях.
    }
};
