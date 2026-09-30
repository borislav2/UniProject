<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => 'Administrator',
                'slug' => 'admin',
                'description' => 'Пълен достъп до всички функции на системата',
                'color' => '#DC2626',
            ],
            [
                'name' => 'Project Manager',
                'slug' => 'project-manager',
                'description' => 'Управление на проекти и ресурси',
                'color' => '#059669',
            ],
            [
                'name' => 'Developer',
                'slug' => 'developer',
                'description' => 'Достъп до проекти и технологии',
                'color' => '#7C3AED',
            ],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['slug' => $role['slug']], $role);
        }
    }
}
