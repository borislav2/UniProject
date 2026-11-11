<?php

use App\Support\IndustrySitesWording2026;
use Illuminate\Database\Migrations\Migration;

// Собствениците (ноември 2026): сайтовете по браншове без етикет „Концепция“. Вижте IndustrySitesWording2026::apply().
return new class extends Migration
{
    public function up(): void
    {
        if (app()->runningUnitTests()) {
            return;
        }

        IndustrySitesWording2026::apply();
    }

    public function down(): void
    {
        // Старите стойности са в записите `<ключ>:backup-2026-11-11`.
    }
};
