<?php

use App\Support\StarterBlogPosts;
use Illuminate\Database\Migrations\Migration;

// Еднократно добавя първите статии на блога (насрочени през 2–4 дни). Миграцията се пуска само веднъж,
// така че статия, изтрита после от админ панела, не се връща. В тестовете се пропуска.
return new class extends Migration
{
    public function up(): void
    {
        if (app()->runningUnitTests()) {
            return;
        }

        StarterBlogPosts::import();
    }

    public function down(): void
    {
    }
};
