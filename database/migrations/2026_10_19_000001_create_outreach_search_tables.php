<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Търсене на бизнеси от админа (град + вид бизнес) в OpenStreetMap, без Harvey.
        Schema::create('outreach_searches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('city', 50);
            $table->string('category', 50);
            $table->string('status', 20)->default('queued'); // queued, processing, done, failed
            $table->unsignedInteger('found')->default(0);
            $table->unsignedInteger('emails')->default(0);
            $table->unsignedInteger('calls')->default(0);
            $table->string('error')->nullable();
            $table->timestamps();
        });

        // Всеки намерен бизнес: със сайт се проверява за имейл, без сайт отива в списъка за обаждане.
        Schema::create('outreach_businesses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outreach_search_id')->nullable()->constrained()->nullOnDelete();
            $table->string('external_id')->unique(); // osm:node/123
            $table->string('name');
            $table->string('category', 50)->nullable();
            $table->string('city')->nullable();
            $table->string('website')->nullable();
            $table->string('phone', 100)->nullable();
            $table->string('map_email')->nullable();
            $table->string('email')->nullable();
            $table->string('email_page')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->index(['outreach_search_id', 'processed_at']);
        });

        Schema::table('outreach_prospects', function (Blueprint $table) {
            $table->string('city')->nullable()->after('industry');
            $table->json('findings')->nullable()->after('personalization');
        });
    }

    public function down(): void
    {
        Schema::table('outreach_prospects', function (Blueprint $table) {
            $table->dropColumn(['city', 'findings']);
        });
        Schema::dropIfExists('outreach_businesses');
        Schema::dropIfExists('outreach_searches');
    }
};
