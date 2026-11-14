<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

// Акаунтите се създават само от админ в „Потребители“ (няма публична регистрация), но формата
// не им даваше роля и входът в админа ги връщаше към началната страница. Тук те получават достъп.
return new class extends Migration
{
    public function up(): void
    {
        $role = DB::table('roles')->where('slug', 'project-manager')->value('id');

        if (! $role) {
            return;
        }

        $without = DB::table('users')
            ->whereNotIn('id', DB::table('user_roles')->select('user_id'))
            ->pluck('id');

        foreach ($without as $id) {
            DB::table('user_roles')->insert(['user_id' => $id, 'role_id' => $role]);
        }
    }

    public function down(): void
    {
    }
};
