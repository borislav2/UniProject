<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Админ по хора (ноември 2026): кой се е обадил на бизнеса от списъка и пълният резултат от безплатната проверка на сайт.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('outreach_calls', function (Blueprint $table) {
            $table->foreignId('called_by')->nullable()->after('called_at')->constrained('users')->nullOnDelete();
        });
        Schema::table('projects', function (Blueprint $table) {
            $table->json('site_check_report')->nullable()->after('website_url');
        });
    }

    public function down(): void
    {
        Schema::table('outreach_calls', function (Blueprint $table) {
            $table->dropConstrainedForeignId('called_by');
        });
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn('site_check_report');
        });
    }
};
