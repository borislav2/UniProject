<?php

use App\Support\StarterBlogPosts;
use Illuminate\Database\Migrations\Migration;

// Owners (25.11.2026): every blog article must also exist in English. The English texts sit in database/data/en/{Bulgarian slug}.md
// and are merged into the data files by StarterBlogPosts::data(). Posts that are already in the database get their English
// version only where the English title is still empty, so an English text the owners wrote or edited is never touched, and the
// Bulgarian fields are never changed. Posts that are not imported yet (a fresh database) already get both languages from import().
// Skipped in tests.
return new class extends Migration
{
    public function up(): void
    {
        if (app()->runningUnitTests()) {
            return;
        }

        foreach (glob(database_path('data/*.php')) as $path) {
            StarterBlogPosts::fillEnglish(basename($path));
        }
    }

    public function down(): void
    {
    }
};
