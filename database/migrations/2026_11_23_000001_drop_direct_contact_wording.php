<?php

use App\Support\DirectContactWording2026;
use Illuminate\Database\Migrations\Migration;

// Owners (23.11.2026): drop the "you talk directly to the people who do the work" lines. See App\Support\DirectContactWording2026.
return new class extends Migration
{
    public function up(): void
    {
        if (app()->runningUnitTests()) {
            return;
        }

        DirectContactWording2026::apply();
    }

    public function down(): void
    {
        // The old values are in `<key>:backup-2026-11-23`.
    }
};
