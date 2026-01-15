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
                'name' => 'E-commerce Platform',
                'description' => 'A full-featured e-commerce platform with payment integration and inventory management.',
                'start_date' => '2024-01-15',
                'end_date' => '2024-06-30',
                'status' => 'Completed',
                'manager' => 'John Smith',
                'category_name' => 'Web Development',
                'technologies' => ['Laravel', 'React', 'MySQL', 'Docker']
            ],
            [
                'name' => 'Mobile Banking App',
                'description' => 'Secure mobile banking application with biometric authentication.',
                'start_date' => '2024-03-01',
                'end_date' => '2024-12-31',
                'status' => 'In Progress',
                'manager' => 'Sarah Johnson',
                'category_name' => 'Mobile Development',
                'technologies' => ['React', 'Node.js', 'PostgreSQL']
            ],
            [
                'name' => 'Data Analytics Dashboard',
                'description' => 'Real-time analytics dashboard for business intelligence.',
                'start_date' => '2024-02-01',
                'end_date' => '2024-08-15',
                'status' => 'In Progress',
                'manager' => 'Michael Brown',
                'category_name' => 'Data Science',
                'technologies' => ['Python', 'Django', 'MongoDB', 'Vue.js']
            ],
            [
                'name' => 'DevOps Pipeline',
                'description' => 'Automated CI/CD pipeline for microservices architecture.',
                'start_date' => '2024-01-01',
                'end_date' => '2024-04-30',
                'status' => 'Completed',
                'manager' => 'David Wilson',
                'category_name' => 'DevOps',
                'technologies' => ['Docker', 'Kubernetes', 'Node.js']
            ],
            [
                'name' => 'Desktop CRM System',
                'description' => 'Customer relationship management desktop application.',
                'start_date' => '2024-05-01',
                'end_date' => null,
                'status' => 'Planning',
                'manager' => 'Emily Davis',
                'category_name' => 'Desktop Applications',
                'technologies' => ['Python', 'PostgreSQL']
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
