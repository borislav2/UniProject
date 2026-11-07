<?php

use App\Support\StarterBlogPosts;
use Illuminate\Database\Migrations\Migration;

// Еднократно добавя шест статии по лекции от Digital Community Day 2026 (database/data/dcd-2026-posts.php) като ЧЕРНОВИ.
// Публикуват се ръчно от админ панела след преглед. В тестовете се пропуска.
return new class extends Migration
{
    public function up(): void
    {
        if (app()->runningUnitTests()) {
            return;
        }

        StarterBlogPosts::import(file: 'dcd-2026-posts.php', status: 'draft');
    }

    public function down(): void
    {
    }
};
