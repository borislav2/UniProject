<?php

use App\Support\SearchConsoleWins2026;
use App\Support\StarterBlogPosts;
use Illuminate\Database\Migrations\Migration;

// Search Console pass (25.11.2026): meta titles and descriptions for the Black Friday and domain posts and the longer domain article.
// Only empty fields and exact earlier defaults change, and the body only when it is still the original or the previous expanded text.
return new class extends Migration
{
    public function up(): void
    {
        if (app()->runningUnitTests()) {
            return;
        }

        StarterBlogPosts::expand();
        SearchConsoleWins2026::apply();
    }

    public function down(): void
    {
    }
};
