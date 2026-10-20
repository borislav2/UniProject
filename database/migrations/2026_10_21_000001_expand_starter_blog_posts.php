<?php

use App\Support\StarterBlogPosts;
use Illuminate\Database\Migrations\Migration;

// Еднократно: по-дълъг текст и корица за първите 18 статии и 4-те чернови от eCommerce Annual Summit 2026
// (database/data/blog-expanded, public/images/blog). Статиите, редактирани от админа, запазват своя текст и своята снимка.
return new class extends Migration
{
    public function up(): void
    {
        StarterBlogPosts::expand();
    }

    public function down(): void
    {
    }
};
