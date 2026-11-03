<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// LinkedIn потенциални клиенти (ноември 2026): търсят се на ръка в LinkedIn, админът само ги следи (App\Models\LinkedinProspect).
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('linkedin_prospects', function (Blueprint $table) {
            $table->id();
            $table->string('profile_url')->unique();
            $table->string('kind', 10)->default('person');
            $table->string('name');
            $table->string('title')->nullable();
            $table->string('company')->nullable();
            $table->string('city')->nullable();
            $table->string('industry')->nullable();
            $table->string('website')->nullable();
            $table->string('email')->nullable();
            $table->string('phone', 60)->nullable();
            $table->string('status', 20)->default('found')->index();
            $table->text('note')->nullable();
            $table->date('next_action_at')->nullable()->index();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('added_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('invited_at')->nullable();
            $table->foreignId('invited_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('connected_at')->nullable();
            $table->timestamp('messaged_at')->nullable();
            $table->foreignId('messaged_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('replied_at')->nullable();
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('linkedin_prospects');
    }
};
