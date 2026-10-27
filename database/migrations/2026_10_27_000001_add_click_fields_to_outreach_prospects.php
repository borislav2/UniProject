<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Кликове по линковете в първото писмо (без пиксел за отваряне): кога за първи и последен път, колко пъти и кой линк.
        Schema::table('outreach_prospects', function (Blueprint $table) {
            $table->timestamp('clicked_at')->nullable()->after('sent_at');
            $table->timestamp('last_clicked_at')->nullable()->after('clicked_at');
            $table->unsignedInteger('click_count')->default(0)->after('last_clicked_at');
            $table->string('last_click_link', 20)->nullable()->after('click_count');
        });
    }

    public function down(): void
    {
        Schema::table('outreach_prospects', function (Blueprint $table) {
            $table->dropColumn(['clicked_at', 'last_clicked_at', 'click_count', 'last_click_link']);
        });
    }
};
