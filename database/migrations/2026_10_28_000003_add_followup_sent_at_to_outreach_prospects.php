<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Кога сме изпратили второто (последното) писмо-напомняне до бизнеса.
        Schema::table('outreach_prospects', function (Blueprint $table) {
            $table->timestamp('followup_sent_at')->nullable()->after('sent_at');
        });
    }

    public function down(): void
    {
        Schema::table('outreach_prospects', fn (Blueprint $table) => $table->dropColumn('followup_sent_at'));
    }
};
