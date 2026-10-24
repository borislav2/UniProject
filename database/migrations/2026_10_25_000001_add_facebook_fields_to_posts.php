<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Публикуване на статиите във Facebook страницата (App\Support\Facebook\FacebookPublisher).
        Schema::table('posts', function (Blueprint $table) {
            $table->string('facebook_post_id')->nullable()->after('published_at');
            $table->timestamp('facebook_posted_at')->nullable()->after('facebook_post_id');
            $table->text('facebook_error')->nullable()->after('facebook_posted_at');
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn(['facebook_post_id', 'facebook_posted_at', 'facebook_error']);
        });
    }
};
