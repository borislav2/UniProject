<?php

use App\Support\TeamMariaLinkedin2026;
use Illuminate\Database\Migrations\Migration;

// Попълва празния LinkedIn на Мария в запазените редакции на екипа (виж TeamMariaLinkedin2026).
return new class extends Migration
{
    public function up(): void
    {
        if (app()->runningUnitTests()) {
            return;
        }

        TeamMariaLinkedin2026::apply();
    }

    public function down(): void
    {
    }
};
