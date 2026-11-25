<?php

use App\Support\SnippetLimits2026;
use Illuminate\Database\Migrations\Migration;

// Screaming Frog pass (25.11.2026): short search phrase meta titles and descriptions for starter articles whose title or
// meta text of ours is wider than 561 px (title) or 985 px (description). Only empty fields and exact earlier values of ours
// change, and updated_at stays. Skipped in tests.
return new class extends Migration
{
    public function up(): void
    {
        if (app()->runningUnitTests()) {
            return;
        }

        SnippetLimits2026::apply();
    }

    public function down(): void
    {
    }
};
