<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Кога сме проверили сайта и имейла на бизнес, внесен без проверка (напр. от Harvey), и защо не е потвърден.
        Schema::table('outreach_prospects', function (Blueprint $table) {
            $table->timestamp('site_checked_at')->nullable()->after('findings');
            $table->string('check_note')->nullable()->after('site_checked_at');
        });
    }

    public function down(): void
    {
        Schema::table('outreach_prospects', function (Blueprint $table) {
            $table->dropColumn(['site_checked_at', 'check_note']);
        });
    }
};
