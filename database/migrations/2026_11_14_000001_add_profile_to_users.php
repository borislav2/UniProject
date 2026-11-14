<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Профил на човека в админа (all / dev / sales / brand): какво му е в менюто и в „Днес“.
 * Съществуващите потребители получават профил по името: Борислав = dev, Владимир/Влади = sales, Мария = brand,
 * всички останали (общият акаунт „Админ“ и т.н.) = all. Нищо не се създава и никоя парола не се пипа.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'profile')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('profile', 20)->nullable()->after('email');
            });
        }

        foreach (DB::table('users')->whereNull('profile')->get(['id', 'name']) as $user) {
            DB::table('users')->where('id', $user->id)->update(['profile' => self::profileFor((string) $user->name)]);
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('profile');
        });
    }

    public static function profileFor(string $name): string
    {
        $name = mb_strtolower($name);
        $has = fn (array $needles) => collect($needles)->contains(fn ($n) => str_contains($name, mb_strtolower($n)));

        return match (true) {
            $has(['Борислав', 'Borislav']) => 'dev',
            $has(['Владимир', 'Влади', 'Vladimir', 'Vladi']) => 'sales',
            $has(['Мария', 'Maria']) => 'brand',
            default => 'all',
        };
    }
};
