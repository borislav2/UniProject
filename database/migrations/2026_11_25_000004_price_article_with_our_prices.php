<?php

use App\Support\StarterBlogPosts;
use Illuminate\Database\Migrations\Migration;

// „Колко струва сайт за малък бизнес през 2026 година“ now has a section with our own prices (owners, 25.11.2026). The new text sits in
// the data file and in database/data/en, the earlier text in database/data/blog-previous. Each language is replaced only where the
// text in the database is still exactly the earlier one, so an edit by the owners is never overwritten. Runs after the date fix
// (000003), so the genuine change moves updated_at. Skipped in tests.
return new class extends Migration
{
    public function up(): void
    {
        if (app()->runningUnitTests()) {
            return;
        }

        StarterBlogPosts::refreshBodies();
    }

    public function down(): void
    {
    }
};
