<?php

use App\Support\StarterBlogPosts;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Carbon;

// Еднократно: четирите статии от eCommerce Annual Summit 2026 излизат на 21 октомври 2026 в 9:00 българско време
// (по желание на собствениците). Ако миграцията мине след тази дата, излизат веднага. Публикувани или насрочени
// от админа статии не се пипат.
return new class extends Migration
{
    public function up(): void
    {
        StarterBlogPosts::publishDraftsAt(Carbon::parse('2026-10-21 09:00', 'Europe/Sofia'), 'summit-posts.php');
    }

    public function down(): void
    {
    }
};
