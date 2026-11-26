<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Upsell след безплатната проверка на сайт (26.11.2026): избрана платена оферта, три писма след отчета и месечна проверка за абонатите.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->string('site_check_offer', 10)->nullable();
            $table->timestamp('site_check_offer_at')->nullable();
            $table->unsignedTinyInteger('followup_step')->default(0);
            $table->timestamp('followup_sent_at')->nullable();
            $table->timestamp('followup_stopped_at')->nullable();
            $table->unsignedTinyInteger('followup_failures')->default(0);
            $table->boolean('monthly_check')->default(false);
            $table->timestamp('monthly_check_at')->nullable();
        });

        // Хората, пуснали проверката преди новия текст за съгласие, не са се съгласявали с писма след отчета: за тях нищо не тръгва.
        DB::table('projects')->where('service', 'Проверка на сайт')->update(['followup_stopped_at' => now()]);
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['site_check_offer', 'site_check_offer_at', 'followup_step', 'followup_sent_at', 'followup_stopped_at', 'followup_failures', 'monthly_check', 'monthly_check_at']);
        });
    }
};
