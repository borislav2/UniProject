<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Клиенти на месечен абонамент и наблюдение на сайтовете им (работи ли, SSL, домейн).
        Schema::table('projects', function (Blueprint $table) {
            $table->string('package', 80)->nullable()->after('service');
            $table->decimal('monthly_fee', 8, 2)->nullable()->after('package');
            $table->unsignedTinyInteger('billing_day')->nullable()->after('monthly_fee');
            $table->string('monitor_status', 10)->nullable()->after('website_url');
            $table->string('monitor_error')->nullable()->after('monitor_status');
            $table->timestamp('monitor_checked_at')->nullable()->after('monitor_error');
            $table->timestamp('monitor_alerted_at')->nullable()->after('monitor_checked_at');
            $table->timestamp('ssl_expires_at')->nullable()->after('monitor_alerted_at');
            $table->timestamp('domain_expires_at')->nullable()->after('ssl_expires_at');
        });

        Schema::create('client_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->char('period', 7);
            $table->decimal('amount', 8, 2);
            $table->date('paid_on');
            $table->string('note')->nullable();
            $table->timestamps();
            $table->unique(['project_id', 'period']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_payments');
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['package', 'monthly_fee', 'billing_day', 'monitor_status', 'monitor_error', 'monitor_checked_at', 'monitor_alerted_at', 'ssl_expires_at', 'domain_expires_at']);
        });
    }
};
