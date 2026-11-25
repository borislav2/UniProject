<?php

use App\Support\PostPlainMeta2026;
use Illuminate\Database\Migrations\Migration;

// GEO/SEO audit (25.11.2026): plain meta titles and descriptions (no colons) for starter articles whose title or excerpt has one.
// Only empty fields and exact earlier values of ours change, and updated_at stays. Skipped in tests.
return new class extends Migration
{
    public function up(): void
    {
        if (app()->runningUnitTests()) {
            return;
        }

        PostPlainMeta2026::apply();
    }

    public function down(): void
    {
    }
};
