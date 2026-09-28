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
            ['name' => 'Ресторанти', 'description' => 'Сайтове и маркетинг за ресторанти и заведения'],
            ['name' => 'Козметични салони', 'description' => 'Сайтове и маркетинг за козметични и бюти салони'],
            ['name' => 'Онлайн магазини', 'description' => 'Онлайн магазини и e-commerce решения'],
            ['name' => 'Занаятчии', 'description' => 'Сайтове и маркетинг за занаятчии и малки производители'],
            ['name' => 'Медицински кабинети', 'description' => 'Сайтове и маркетинг за медицински и дентални кабинети'],
            ['name' => 'Строителни фирми', 'description' => 'Сайтове и маркетинг за строителни и ремонтни фирми'],
            ['name' => 'Автосервизи', 'description' => 'Сайтове и маркетинг за автосервизи'],
            ['name' => 'Ново запитване', 'description' => 'Автоматично създадени запитвания от сайта, изчакващи категоризация'],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
