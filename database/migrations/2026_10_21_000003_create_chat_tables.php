<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Чатът на сайта: разговорите се пазят 30 дни (creatium.chat.retention_days), за да се виждат в админа.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_conversations', function (Blueprint $table) {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->string('locale', 5)->default('bg');
            $table->string('page', 255)->nullable();
            $table->string('ip_hash', 64)->nullable();
            // Историята точно във вида, в който се праща към Claude (с блоковете за мислене), само се допълва.
            $table->json('api_messages')->nullable();
            $table->unsignedSmallInteger('user_messages')->default(0);
            $table->foreignId('lead_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->timestamps();
            $table->index('updated_at');
        });

        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chat_conversation_id')->constrained()->cascadeOnDelete();
            $table->string('role', 10);
            $table->text('text');
            $table->string('source', 10)->nullable();
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_messages');
        Schema::dropIfExists('chat_conversations');
    }
};
