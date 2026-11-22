<?php

use App\Support\StarterBlogPosts;
use Illuminate\Database\Migrations\Migration;

// Owners (22.11.2026): three articles a day for faster indexing. The first week (23 to 29.11.2026) comes from
// database/data/daily-2026-11-{a,b,c}.php; every post has its own publish_at (Sofia time) and shows by itself when the time comes.
// Posts that already exist (by Bulgarian slug) are never touched. Skipped in tests.
return new class extends Migration
{
    public function up(): void
    {
        if (app()->runningUnitTests()) {
            return;
        }

        foreach (['a', 'b', 'c'] as $part) {
            if (is_file(database_path("data/daily-2026-11-$part.php"))) {
                StarterBlogPosts::import(file: "daily-2026-11-$part.php");
            }
        }
    }

    public function down(): void
    {
    }
};
