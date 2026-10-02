<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->string('lead_channel', 100)->nullable()->after('source');
            $table->string('utm_source', 100)->nullable()->after('lead_channel');
            $table->string('utm_medium', 100)->nullable()->after('utm_source');
            $table->string('utm_campaign', 100)->nullable()->after('utm_medium');
            $table->string('referrer', 190)->nullable()->after('utm_campaign');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['lead_channel', 'utm_source', 'utm_medium', 'utm_campaign', 'referrer']);
        });
    }
};
