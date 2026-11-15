<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Пазар (държава) и език на писмото за всяко търсене, бизнес, потенциален клиент и обаждане. Досегашните са български.
        foreach (['outreach_searches', 'outreach_businesses', 'outreach_prospects', 'outreach_calls'] as $name) {
            Schema::table($name, function (Blueprint $table) {
                $table->string('country', 2)->default('bg')->index();
                $table->string('language', 2)->default('bg');
            });
        }

        Schema::table('outreach_searches', function (Blueprint $table) {
            // Страници, взети от Serper (по един кредит всяка): за броя кредити на ден.
            $table->unsignedSmallInteger('serper_pages')->default(0);
        });

        Schema::table('outreach_prospects', function (Blueprint $table) {
            $table->string('company_evidence', 255)->nullable();
            $table->json('score_reasons')->nullable();
            $table->string('draft_subject', 200)->nullable();
            $table->text('draft_observation')->nullable();
            $table->timestamp('draft_at')->nullable()->index();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('outreach_search_id')->nullable()->constrained('outreach_searches')->nullOnDelete();
        });

        // Малки настройки на автопилота (план, лимити, пауза) и последното му състояние.
        Schema::create('outreach_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key', 60)->unique();
            $table->json('value')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('outreach_settings');

        Schema::table('outreach_prospects', function (Blueprint $table) {
            $table->dropConstrainedForeignId('outreach_search_id');
            $table->dropConstrainedForeignId('approved_by');
            $table->dropColumn(['company_evidence', 'score_reasons', 'draft_subject', 'draft_observation', 'draft_at']);
        });
        Schema::table('outreach_searches', function (Blueprint $table) {
            $table->dropColumn('serper_pages');
        });
        foreach (['outreach_searches', 'outreach_businesses', 'outreach_prospects', 'outreach_calls'] as $name) {
            Schema::table($name, function (Blueprint $table) {
                $table->dropColumn(['country', 'language']);
            });
        }
    }
};
