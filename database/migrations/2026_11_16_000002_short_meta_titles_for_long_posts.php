<?php

use App\Support\PostMetaTitles2026;
use Illuminate\Database\Migrations\Migration;

// SEO одит (16.11.2026): кратки мета заглавия за статии със заглавие над 60 знака. Вижте PostMetaTitles2026::apply().
return new class extends Migration
{
    public function up(): void
    {
        if (app()->runningUnitTests()) {
            return;
        }

        PostMetaTitles2026::apply();
    }

    public function down(): void
    {
        // Мета заглавията се махат от „Блог“ в админа, ако не са нужни.
    }
};
