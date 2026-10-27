<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Кой от екипа води запитването/клиента (филтър „Моите“ в „Днес“ и „Проекти“).
        Schema::table('projects', function (Blueprint $table) {
            $table->foreignId('assigned_to')->nullable()->after('manager')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('projects', fn (Blueprint $table) => $table->dropConstrainedForeignId('assigned_to'));
    }
};
