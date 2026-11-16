<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Ключова дума на статия (по език, като другите полета). Нищо в съществуващите статии не се променя.
        Schema::table('posts', function (Blueprint $table) {
            $table->json('focus_keyword')->nullable();
        });

        // Резултатът от последните проверки на SEO здравето (вътрешно обхождане на нашия сайт).
        Schema::create('seo_reports', function (Blueprint $table) {
            $table->id();
            $table->timestamp('ran_at')->index();
            $table->unsignedTinyInteger('score')->default(0);
            $table->unsignedInteger('pages')->default(0);
            $table->unsignedInteger('errors')->default(0);
            $table->unsignedInteger('warnings')->default(0);
            $table->unsignedInteger('seconds')->default(0);
            $table->longText('data');
            $table->timestamps();
        });

        // Следене на позиции на ръка (няма API за позиции): думата, езикът, целевата страница и записите с дата.
        Schema::create('seo_keywords', function (Blueprint $table) {
            $table->id();
            $table->string('keyword', 200);
            $table->string('locale', 2)->default('bg');
            $table->string('country', 2)->default('bg');
            $table->string('target', 200)->nullable();     // име на маршрут (home, packages ...), post:<id> или адрес
            $table->string('priority', 10)->default('medium'); // high, medium, low
            $table->string('note', 500)->nullable();
            $table->timestamps();
            $table->unique(['keyword', 'locale', 'country']);
        });

        Schema::create('seo_keyword_positions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seo_keyword_id')->constrained('seo_keywords')->cascadeOnDelete();
            $table->date('date');
            $table->decimal('position', 6, 1)->nullable();
            $table->unsignedInteger('impressions')->nullable();
            $table->unsignedInteger('clicks')->nullable();
            $table->timestamps();
            $table->unique(['seo_keyword_id', 'date']);
        });

        // Месечни проверки дали AI асистентите ни споменават (всичко на ръка).
        Schema::create('seo_ai_checks', function (Blueprint $table) {
            $table->id();
            $table->date('checked_on')->index();
            $table->string('assistant', 40)->index();
            $table->string('prompt', 500);
            $table->boolean('mentioned')->default(false);
            $table->string('position_note', 300)->nullable();
            $table->string('link', 1000)->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seo_ai_checks');
        Schema::dropIfExists('seo_keyword_positions');
        Schema::dropIfExists('seo_keywords');
        Schema::dropIfExists('seo_reports');
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn('focus_keyword');
        });
    }
};
