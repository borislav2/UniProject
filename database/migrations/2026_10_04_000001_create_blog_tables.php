<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Translatable fields are JSON objects keyed by locale: {"bg": "...", "en": "..."}.
    public function up(): void
    {
        Schema::create('post_categories', function (Blueprint $table) {
            $table->id();
            $table->json('name');
            $table->json('slug');
            $table->timestamps();
        });

        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_category_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->json('title');
            $table->json('slug');
            $table->json('excerpt')->nullable();
            $table->json('body')->nullable();
            $table->json('meta_title')->nullable();
            $table->json('meta_description')->nullable();
            $table->json('canonical_url')->nullable();
            $table->json('og_title')->nullable();
            $table->json('og_description')->nullable();
            $table->string('cover_image')->nullable();
            $table->string('og_image')->nullable();
            $table->string('status')->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'published_at']);
        });

        // Admin overrides for site content (config/creatium*.php) and per-page SEO, one row per key and language.
        Schema::create('content_blocks', function (Blueprint $table) {
            $table->id();
            $table->string('key');
            $table->string('locale', 5);
            $table->json('value');
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['key', 'locale']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_blocks');
        Schema::dropIfExists('posts');
        Schema::dropIfExists('post_categories');
    }
};
