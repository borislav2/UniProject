<?php

namespace Tests\Feature;

use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientScreenshotsTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_migration_sets_screenshots_only_where_missing(): void
    {
        $category = \App\Models\Category::create(['name' => 'Уебсайт']);
        $make = fn (array $a) => Project::create($a + ['description' => 'Описание.', 'start_date' => now(), 'end_date' => now(), 'status' => 'Completed', 'manager' => 'Екип', 'category_id' => $category->id, 'is_public' => true]);
        $lasepro = $make(['name' => 'Lasepro', 'website_url' => 'https://lasepro.bg']);
        $roofs = $make(['name' => 'Pokrivi Burgas', 'website_url' => 'https://pokrivi-burgas.eu', 'screenshot' => 'uploads/portfolio/own.webp']);
        $other = $make(['name' => 'Друг', 'website_url' => 'https://example.bg']);

        $migration = require database_path('migrations/2026_11_10_000001_add_client_project_screenshots.php');
        $migration->up();

        $this->assertSame('images/projects/lasepro.webp', $lasepro->fresh()->screenshot);
        $this->assertSame('uploads/portfolio/own.webp', $roofs->fresh()->screenshot);
        $this->assertNull($other->fresh()->screenshot);
        $this->assertFileExists(public_path('images/projects/lasepro.webp'));
        $this->assertFileExists(public_path('images/projects/pokrivi-burgas.webp'));
    }
}
