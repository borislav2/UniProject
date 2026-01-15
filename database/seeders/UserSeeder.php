<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get role IDs
        $adminRole = Role::where('slug', 'admin')->first();
        $projectManagerRole = Role::where('slug', 'project-manager')->first();
        $developerRole = Role::where('slug', 'developer')->first();
        
        // Create users with different roles
        $users = [
            [
                'name' => 'Admin User',
                'email' => 'admin@projectmanager.com',
                'password' => Hash::make('password'),
                'role_id' => $adminRole->id,
            ],
            [
                'name' => 'Project Manager',
                'email' => 'pm@projectmanager.com',
                'password' => Hash::make('password'),
                'role_id' => $projectManagerRole->id,
            ],
            [
                'name' => 'Developer',
                'email' => 'dev@projectmanager.com',
                'password' => Hash::make('password'),
                'role_id' => $developerRole->id,
            ],
        ];
        
        foreach ($users as $userData) {
            $user = User::create([
                'name' => $userData['name'],
                'email' => $userData['email'],
                'password' => $userData['password'],
            ]);
            
            // Attach role to user
            $user->roles()->attach($userData['role_id']);
        }
    }
}
