<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            CategorySeeder::class,
            TechnologySeeder::class,
        ]);

        // Demo accounts and fake projects must never exist on a live site.
        if (app()->environment(['local', 'testing'])) {
            $this->call([
                UserSeeder::class,
                ProjectSeeder::class,
            ]);
        }
    }
}
