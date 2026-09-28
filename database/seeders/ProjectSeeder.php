<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Project;
use App\Models\Category;
use App\Models\Technology;
use Illuminate\Support\Facades\DB;

class ProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $projects = [
            [
                'name' => 'Сайт за ресторант "Вкусотия"',
                'description' => 'Бизнес сайт с меню, галерия и форма за резервации за семеен ресторант.',
                'start_date' => '2026-02-01',
                'end_date' => '2026-03-10',
                'status' => 'Completed',
                'manager' => 'Уеб екип',
                'category_name' => 'Ресторанти',
                'technologies' => ['Laravel', 'MySQL']
            ],
            [
                'name' => 'Онлайн магазин за козметика "Glow"',
                'description' => 'Онлайн магазин с плащане с карта и интеграция с куриерска фирма.',
                'start_date' => '2026-01-15',
                'end_date' => '2026-04-01',
                'status' => 'Completed',
                'manager' => 'Уеб екип',
                'category_name' => 'Онлайн магазини',
                'technologies' => ['Laravel', 'React', 'MySQL']
            ],
            [
                'name' => 'Маркетинг кампания за козметичен салон "Bella"',
                'description' => 'Месечна реклама във Facebook и Instagram, насочена към локални клиенти.',
                'start_date' => '2026-03-01',
                'end_date' => null,
                'status' => 'In Progress',
                'manager' => 'Маркетинг екип',
                'category_name' => 'Козметични салони',
                'technologies' => []
            ],
            [
                'name' => 'Сайт-визитка за дентален кабинет "Усмивка"',
                'description' => 'Едностраничен сайт с информация за услуги и контактна форма.',
                'start_date' => '2026-04-01',
                'end_date' => null,
                'status' => 'Planning',
                'manager' => 'Уеб екип',
                'category_name' => 'Медицински кабинети',
                'technologies' => []
            ],
            [
                'name' => 'SEO оптимизация за автосервиз "Турбо"',
                'description' => 'Локално SEO и профил в Google Business за по-добра видимост в търсенето.',
                'start_date' => '2026-02-15',
                'end_date' => '2026-05-15',
                'status' => 'In Progress',
                'manager' => 'Маркетинг екип',
                'category_name' => 'Автосервизи',
                'technologies' => []
            ],
        ];

        foreach ($projects as $projectData) {
            $category = Category::where('name', $projectData['category_name'])->first();
            
            $project = Project::create([
                'name' => $projectData['name'],
                'description' => $projectData['description'],
                'start_date' => $projectData['start_date'],
                'end_date' => $projectData['end_date'],
                'status' => $projectData['status'],
                'manager' => $projectData['manager'],
                'category_id' => $category->id,
            ]);

            // Attach technologies
            foreach ($projectData['technologies'] as $techName) {
                $technology = Technology::where('name', $techName)->first();
                if ($technology) {
                    $project->technologies()->attach($technology->id);
                }
            }
        }
    }
}
