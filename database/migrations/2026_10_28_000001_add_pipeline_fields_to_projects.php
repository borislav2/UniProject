<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Етап на продажбата за запитванията и следващата стъпка (какво и кога), за да не изпуснем нито едно.
        Schema::table('projects', function (Blueprint $table) {
            $table->string('stage', 20)->nullable()->after('status');
            $table->timestamp('stage_changed_at')->nullable()->after('stage');
            $table->string('next_action')->nullable()->after('stage_changed_at');
            $table->timestamp('next_action_at')->nullable()->after('next_action');
            $table->string('lost_reason')->nullable()->after('next_action_at');
        });

        // Досегашните запитвания получават етап от статуса си.
        DB::table('projects')->where('source', 'website')->whereIn('status', ['In Progress', 'Completed', 'On Hold'])->update(['stage' => 'won']);
        DB::table('projects')->where('source', 'website')->where('status', 'Cancelled')->update(['stage' => 'lost']);
        DB::table('projects')->where('source', 'website')->whereNull('stage')->whereNotNull('read_at')->update(['stage' => 'contacted']);
        DB::table('projects')->where('source', 'website')->whereNull('stage')->update(['stage' => 'new']);
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['stage', 'stage_changed_at', 'next_action', 'next_action_at', 'lost_reason']);
        });
    }
};
