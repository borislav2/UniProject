<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Бизнеси, намерени от Harvey (CSV от `harvey export`), до които пишем първо писмо от админа.
        Schema::create('outreach_prospects', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('company')->nullable();
            $table->string('title')->nullable();
            $table->string('website')->nullable();
            $table->string('industry')->nullable();
            $table->text('personalization')->nullable();
            $table->string('email_status', 20)->nullable();
            $table->unsignedSmallInteger('score')->default(0);
            $table->string('status', 20)->default('new'); // new, sent, replied, skipped, unsubscribed
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('unsubscribed_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'score']);
        });

        // Адреси, на които никога повече не пишем (отписали са се или сме ги изключили ръчно).
        Schema::create('outreach_suppressions', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->string('reason', 50);
            $table->timestamps();
        });

        Schema::table('client_emails', function (Blueprint $table) {
            $table->foreignId('outreach_prospect_id')->nullable()->after('project_id')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('client_emails', function (Blueprint $table) {
            $table->dropConstrainedForeignId('outreach_prospect_id');
        });
        Schema::dropIfExists('outreach_suppressions');
        Schema::dropIfExists('outreach_prospects');
    }
};
