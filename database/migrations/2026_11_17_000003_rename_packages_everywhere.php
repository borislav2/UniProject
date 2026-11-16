<?php

use App\Support\PackageNames2026;
use Illuminate\Database\Migrations\Migration;

// Нови имена на пакетите навсякъде (17.11.2026): Core Изграждане, Pro Ускорение, Enterprise Партньорство. Вижте PackageNames2026::apply().
return new class extends Migration
{
    public function up(): void
    {
        if (app()->runningUnitTests()) {
            return;
        }

        PackageNames2026::apply();
    }

    public function down(): void
    {
        // Старите стойности са в записите `<ключ>:backup-2026-11-17b`.
    }
};
