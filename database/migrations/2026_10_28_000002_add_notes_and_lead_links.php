<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Бележки и история по запитване/проект: разговори, смяна на етап, ръчни бележки.
        Schema::create('project_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('kind', 20)->default('note');
            $table->text('body');
            $table->timestamps();
        });

        // Телефонът на бизнеса (от търсенето в картите) и връзка към запитването, ако сме го превърнали в такова.
        Schema::table('outreach_prospects', function (Blueprint $table) {
            $table->string('phone', 50)->nullable()->after('email');
            $table->foreignId('project_id')->nullable()->after('status')->constrained()->nullOnDelete();
        });
        Schema::table('outreach_calls', function (Blueprint $table) {
            $table->foreignId('project_id')->nullable()->after('status')->constrained()->nullOnDelete();
        });

        // Досегашните бизнеси получават телефона от търсенето, по същия сайт.
        DB::table('outreach_prospects')->whereNull('phone')->whereNotNull('website')->orderBy('id')->each(function ($prospect) {
            $phone = DB::table('outreach_businesses')->where('website', $prospect->website)->whereNotNull('phone')->value('phone');
            if ($phone) {
                DB::table('outreach_prospects')->where('id', $prospect->id)->update(['phone' => $phone]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('outreach_calls', fn (Blueprint $table) => $table->dropConstrainedForeignId('project_id'));
        Schema::table('outreach_prospects', function (Blueprint $table) {
            $table->dropConstrainedForeignId('project_id');
            $table->dropColumn('phone');
        });
        Schema::dropIfExists('project_notes');
    }
};
