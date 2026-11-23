<?php

use App\Support\StarterBlogPosts;
use Illuminate\Database\Migrations\Migration;

// The 21 daily articles of week 1 were imported without a cover (import() skips posts that exist). Their covers are
// public/images/blog/{slug}.webp (G version in public/images/blog/g). Sets cover_image only where it is still blank,
// so a cover the owners uploaded or changed is never touched. Skipped in tests.
return new class extends Migration
{
    public function up(): void
    {
        if (app()->runningUnitTests()) {
            return;
        }

        foreach (glob(database_path('data/daily-*.php')) as $path) {
            StarterBlogPosts::fillCovers(basename($path));
        }
    }

    public function down(): void
    {
    }
};
