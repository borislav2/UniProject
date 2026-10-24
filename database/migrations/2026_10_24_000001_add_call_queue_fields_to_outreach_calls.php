<?php

use App\Models\OutreachCall;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Списъкът за обаждане като опашка: мобилните първо, „Обади се отново“ на дата и брой опити (до 2 без отговор).
        Schema::table('outreach_calls', function (Blueprint $table) {
            $table->boolean('is_mobile')->default(false)->after('phone');
            $table->date('callback_on')->nullable()->after('called_at');
            $table->unsignedTinyInteger('attempts')->default(0)->after('callback_on');

            $table->index(['callback_on']);
            $table->index(['is_mobile']);
        });

        OutreachCall::query()->whereNotNull('phone')->select(['id', 'phone'])->chunkById(500, function ($calls) {
            foreach ($calls as $call) {
                if (OutreachCall::hasMobile($call->phone)) {
                    OutreachCall::whereKey($call->id)->update(['is_mobile' => true]);
                }
            }
        });
        // Обажданията отпреди това се броят за един опит.
        OutreachCall::whereNotNull('called_at')->update(['attempts' => 1]);
    }

    public function down(): void
    {
        Schema::table('outreach_calls', function (Blueprint $table) {
            $table->dropIndex(['callback_on']);
            $table->dropIndex(['is_mobile']);
            $table->dropColumn(['is_mobile', 'callback_on', 'attempts']);
        });
    }
};
