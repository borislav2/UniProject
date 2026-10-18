<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Чернови на отговори на отзиви в Google за клиентите („Отговори на отзиви“ в админа).
        Schema::create('review_replies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('business_name');
            $table->string('business_type')->nullable();
            $table->string('reviewer_name')->nullable();
            $table->unsignedTinyInteger('rating');
            $table->text('review_text');
            $table->string('tone', 20)->default('friendly');
            $table->text('notes')->nullable();
            $table->string('signature')->nullable();
            $table->text('reply_text');
            $table->string('source', 20); // ai или template
            $table->timestamps();

            $table->index('business_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('review_replies');
    }
};
