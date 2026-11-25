<?php

use App\Support\StarterBlogPosts;
use Illuminate\Database\Migrations\Migration;

// The English back-fill and the cover back-fill bumped updated_at on dozens of posts at once (2026-11-24 20:03, 2026-11-25 07:53 and
// 11:09 UTC). That made dateModified in the JSON-LD, the sitemap lastmod and the visible „Обновено“ line wrong. For posts from the data
// files whose updated_at falls within 10 minutes of those moments and is later than the publish date, updated_at goes back to the
// publish date (the creation date when there is none). A post edited at any other time keeps its date. Skipped in tests.
return new class extends Migration
{
    public function up(): void
    {
        if (app()->runningUnitTests()) {
            return;
        }

        StarterBlogPosts::restoreBulkTimestamps();
    }

    public function down(): void
    {
    }
};
