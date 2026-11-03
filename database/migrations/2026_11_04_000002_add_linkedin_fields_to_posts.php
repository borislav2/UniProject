<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Публикуване на статиите в страницата на фирмата в LinkedIn (App\Support\LinkedIn\LinkedInPublisher).
        Schema::table('posts', function (Blueprint $table) {
            $table->string('linkedin_post_id')->nullable()->after('facebook_error');
            $table->timestamp('linkedin_posted_at')->nullable()->after('linkedin_post_id');
            $table->text('linkedin_error')->nullable()->after('linkedin_posted_at');
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn(['linkedin_post_id', 'linkedin_posted_at', 'linkedin_error']);
        });
    }
};
