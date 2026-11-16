<?php

use App\Support\GAudit3_2026;
use Illuminate\Database\Migrations\Migration;

// Дизайн Г, шести кръг (16.11.2026): нови стойности по подразбиране на няколко g_ текста, имена и етикети на пакетите и g_* полета на екипа. Вижте GAudit3_2026::apply().
return new class extends Migration
{
    public function up(): void
    {
        if (app()->runningUnitTests()) {
            return;
        }

        GAudit3_2026::apply();
    }

    public function down(): void
    {
        // Старите стойности са в записите `<ключ>:backup-2026-11-16`.
    }
};
