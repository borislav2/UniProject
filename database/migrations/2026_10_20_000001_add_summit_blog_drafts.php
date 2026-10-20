<?php

use App\Support\StarterBlogPosts;
use Illuminate\Database\Migrations\Migration;

// Еднократно добавя четири статии по eCommerce Annual Summit 2026 (database/data/summit-posts.php) като ЧЕРНОВИ.
// Публикуват се ръчно от админ панела след преглед. В тестовете се пропуска.
return new class extends Migration
{
    public function up(): void
    {
        if (app()->runningUnitTests()) {
            return;
        }

        StarterBlogPosts::import(file: 'summit-posts.php', status: 'draft');
    }

    public function down(): void
    {
    }
};
