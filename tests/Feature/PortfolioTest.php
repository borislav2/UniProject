<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Project;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\CategorySeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class PortfolioTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        File::deleteDirectory(public_path('uploads/portfolio'));
        parent::tearDown();
    }

    public function test_a_finished_public_project_shows_with_its_screenshot_and_link(): void
    {
        $this->seed(RoleSeeder::class);
        $this->seed(CategorySeeder::class);
        $admin = User::factory()->create();
        $admin->roles()->attach(Role::where('slug', 'admin')->value('id'));

        $this->actingAs($admin)->post('/admin/projects', [
            'name' => 'Покриви Бургас', 'description' => 'Сайт за покривна фирма с галерия от обекти и форма за оглед.',
            'start_date' => '2026-09-01', 'end_date' => '2026-10-01', 'status' => 'Completed', 'manager' => 'Борислав',
            'category_id' => Category::first()->id, 'is_public' => '1', 'website_url' => 'https://pokrivi-burgas.eu',
            'screenshot' => UploadedFile::fake()->image('site.png', 1600, 1000),
        ])->assertRedirect();

        $project = Project::where('name', 'Покриви Бургас')->sole();
        $this->assertMatchesRegularExpression('#^uploads/portfolio/[\w.-]+\.png$#', $project->screenshot);
        $this->assertFileExists(public_path($project->screenshot));

        $this->get('/proekti')->assertOk()
            ->assertSee('Покриви Бургас')
            ->assertSee('src="' . asset($project->screenshot) . '"', false)
            ->assertSee('href="https://pokrivi-burgas.eu"', false);

        $this->actingAs($admin)->get("/admin/projects/{$project->id}/edit")->assertSee('Махни снимката');
        $this->actingAs($admin)->put("/admin/projects/{$project->id}", [
            'name' => 'Покриви Бургас', 'description' => 'Сайт за покривна фирма.', 'start_date' => '2026-09-01', 'status' => 'Completed',
            'manager' => 'Борислав', 'category_id' => Category::first()->id, 'is_public' => '1', 'remove_screenshot' => '1',
        ])->assertRedirect();
        $this->assertNull($project->fresh()->screenshot);
        $this->assertFileDoesNotExist(public_path($project->screenshot));
    }
}
