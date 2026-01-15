<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Web Development', 'description' => 'Web applications and websites'],
            ['name' => 'Mobile Development', 'description' => 'iOS and Android applications'],
            ['name' => 'Desktop Applications', 'description' => 'Native desktop software'],
            ['name' => 'Data Science', 'description' => 'Data analysis and machine learning projects'],
            ['name' => 'DevOps', 'description' => 'Infrastructure and deployment projects'],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
