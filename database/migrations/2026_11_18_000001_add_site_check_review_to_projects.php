<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Безплатната проверка на сайт е и автоматична, и личен преглед от специалист до два работни дни (18.11.2026):
// срокът за прегледа, кога е изпратен и основната цел, избрана във формата (leads, google, speed).
// Старите проверки остават без срок (при тях такова обещание няма).
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->timestamp('review_due_at')->nullable()->index()->after('site_check_report');
            $table->timestamp('reviewed_at')->nullable()->after('review_due_at');
            $table->string('check_goal', 20)->nullable()->after('reviewed_at');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['review_due_at', 'reviewed_at', 'check_goal']);
        });
    }
};
