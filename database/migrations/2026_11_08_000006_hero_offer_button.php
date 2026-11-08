<?php

use App\Support\HeroOfferButton2026;
use Illuminate\Database\Migrations\Migration;

// Собствениците (08.11.2026): бутонът в началото води към офертата. Вижте App\Support\HeroOfferButton2026.
return new class extends Migration
{
    public function up(): void
    {
        if (app()->runningUnitTests()) {
            return;
        }

        HeroOfferButton2026::apply();
    }

    public function down(): void
    {
        // Старата стойност е в `home_hero:backup-2026-11-08-f`.
    }
};
