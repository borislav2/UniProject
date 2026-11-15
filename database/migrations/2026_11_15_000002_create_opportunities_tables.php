<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Възможности: обществени поръчки, агенции партньори, свободна практика, грантове и ръчно добавени.
        Schema::create('opportunities', function (Blueprint $table) {
            $table->id();
            $table->string('kind', 20)->default('other')->index();       // tender, partner, freelance, grant, other
            $table->string('source', 30)->index();                       // ted, contracts-finder, find-a-tender, search, manual
            $table->string('external_id', 120);
            $table->string('title', 500);
            $table->string('buyer', 300)->nullable();
            $table->string('country', 2)->nullable()->index();
            $table->string('language', 2)->nullable();
            $table->string('url', 1000)->nullable();
            $table->string('url_key', 64)->nullable()->index();          // sha1 на нормализирания адрес: срещу повторения
            $table->text('summary')->nullable();
            $table->json('cpv')->nullable();
            $table->decimal('value_min', 16, 2)->nullable();
            $table->decimal('value_max', 16, 2)->nullable();
            $table->string('currency', 3)->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamp('deadline_at')->nullable()->index();
            $table->unsignedTinyInteger('score')->default(0)->index();
            $table->json('score_reasons')->nullable();
            $table->string('status', 20)->default('new')->index();       // new, interesting, preparing, submitted, won, lost, skipped
            $table->string('skip_reason', 200)->nullable();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->text('note')->nullable();
            $table->timestamp('next_action_at')->nullable();
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->foreignId('outreach_prospect_id')->nullable()->constrained('outreach_prospects')->nullOnDelete();
            $table->foreignId('added_by')->nullable()->constrained('users')->nullOnDelete();
            $table->json('raw')->nullable();                             // подрязани данни от източника, без лични данни
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();

            $table->unique(['source', 'external_id']);
        });

        Schema::create('opportunity_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('opportunity_id')->constrained('opportunities')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('kind', 10)->default('note');                 // note, status
            $table->text('body');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('opportunity_notes');
        Schema::dropIfExists('opportunities');
    }
};
