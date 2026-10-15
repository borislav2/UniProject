<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Ден и час за консултация, избрани във формата за контакт (UTC).
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->timestamp('consultation_at')->nullable()->after('read_at')->index();
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn('consultation_at');
        });
    }
};
