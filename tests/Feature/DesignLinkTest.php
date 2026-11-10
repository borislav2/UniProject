<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class DesignLinkTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_signed_link_shows_design_g_to_a_guest_until_they_leave(): void
    {
        $link = URL::temporarySignedRoute('design.link', now()->addDays(30));

        $this->get($link)->assertRedirect(route('home'));
        $this->get('/')->assertOk()
            ->assertSee('class="design-g"', false)
            ->assertSee('<meta name="robots" content="noindex">', false)
            ->assertSee('class="g-preview"', false);
        $this->get('/paketi')->assertOk()->assertSee('class="design-g"', false);

        $this->get('/?dizain=star')->assertOk()->assertDontSee('class="design-g"', false);
    }

    public function test_an_unsigned_or_expired_link_does_nothing(): void
    {
        $this->get('/nov-dizain')->assertForbidden();
        $expired = URL::temporarySignedRoute('design.link', now()->subMinute());
        $this->get($expired)->assertForbidden();
        $this->get('/')->assertOk()->assertDontSee('class="design-g"', false);
    }

    public function test_the_admin_header_has_the_new_design_button_and_the_copy_link(): void
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->create();
        $user->roles()->attach(Role::where('slug', 'admin')->value('id'));

        $this->actingAs($user)->get('/admin/dnes')->assertOk()
            ->assertSee('data-new-design', false)
            ->assertSee('?dizain=g', false)
            ->assertSee('/nov-dizain?expires=', false)
            ->assertSee('Копирай линк за преглед');
    }
}
