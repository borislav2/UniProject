<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Technology;

class TechnologySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $technologies = [
            ['name' => 'Laravel', 'version' => '11.x'],
            ['name' => 'React', 'version' => '18.x'],
            ['name' => 'Vue.js', 'version' => '3.x'],
            ['name' => 'Angular', 'version' => '17.x'],
            ['name' => 'Node.js', 'version' => '20.x'],
            ['name' => 'Python', 'version' => '3.12'],
            ['name' => 'Django', 'version' => '4.2'],
            ['name' => 'Docker', 'version' => '24.x'],
            ['name' => 'Kubernetes', 'version' => '1.29'],
            ['name' => 'PostgreSQL', 'version' => '16'],
            ['name' => 'MySQL', 'version' => '8.0'],
            ['name' => 'MongoDB', 'version' => '7.0'],
        ];

        foreach ($technologies as $technology) {
            Technology::firstOrCreate(['name' => $technology['name']], $technology);
        }
    }
}
