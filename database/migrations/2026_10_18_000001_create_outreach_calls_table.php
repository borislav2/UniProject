<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Бизнеси без сайт, намерени от Harvey (tools/harvey/export_no_website.py): списък за обаждане.
        Schema::create('outreach_calls', function (Blueprint $table) {
            $table->id();
            $table->string('external_id')->unique(); // напр. osm:node/123
            $table->string('name');
            $table->string('phone', 100)->nullable();
            $table->string('industry')->nullable();
            $table->string('city')->nullable();
            $table->string('source_url')->nullable();
            $table->string('status', 20)->default('new'); // new, no_answer, interested, not_interested, client
            $table->text('note')->nullable();
            $table->timestamp('called_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'city']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('outreach_calls');
    }
};
