<?php

use App\Support\GAudit2026;
use Illuminate\Database\Migrations\Migration;

// Дизайн Г, пети кръг (13.11.2026): нови стойности по подразбиране на няколко g_ текста. Вижте GAudit2026::apply().
return new class extends Migration
{
    public function up(): void
    {
        if (app()->runningUnitTests()) {
            return;
        }

        GAudit2026::apply();
    }

    public function down(): void
    {
        // Старите стойности са в записите `<ключ>:backup-2026-11-13`.
    }
};
