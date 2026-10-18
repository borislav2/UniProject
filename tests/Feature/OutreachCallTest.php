<?php

namespace Tests\Feature;

use App\Models\OutreachCall;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OutreachCallTest extends TestCase
{
    use RefreshDatabase;

    private const CSV = "external_id,name,phone,industry,location,source_url\n"
        . "osm:node/1,Механа Старата къща,+359 88 123 4567,restaurant,\"Sofia, Bulgaria\",https://www.openstreetmap.org/node/1\n"
        . "osm:way/2,Салон Ирис,,beauty,Пловдив,https://www.openstreetmap.org/way/2\n"
        . ",Без идентификатор,0888,restaurant,Варна,\n"
        . "osm:node/3,Автосервиз Мотор,032 555 666; 0899 111 222,car_repair,\"Plovdiv, Bulgaria\",javascript:alert(1)\n"
        . "osm:node/4,Glam by Lili,+40 762 942 283,beauty,\"Ruse, Bulgaria\",https://www.openstreetmap.org/node/4\n"
        . "osm:node/5,Pensiunea Dunarea,,hotel,Giurgiu,https://www.openstreetmap.org/node/5\n";

    private string $path;

    protected function setUp(): void
    {
        parent::setUp();
        $this->path = tempnam(sys_get_temp_dir(), 'nows');
        file_put_contents($this->path, self::CSV);
        config(['creatium.outreach.harvey_no_website_csv' => $this->path]);
    }

    protected function tearDown(): void
    {
        @unlink($this->path);
        parent::tearDown();
    }

    private function admin(): User
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->create();
        $user->roles()->attach(Role::where('slug', 'developer')->value('id'));

        return $user;
    }

    public function test_server_list_is_imported_and_shown_with_phone_links(): void
    {
        $user = $this->admin();

        $this->actingAs($user)->post('/admin/outreach-calls/import')
            ->assertRedirect('/admin/outreach-calls')
            ->assertSessionHas('success', 'От сървъра: 3 нови, 0 обновени, 1 пропуснати, 2 извън България.');

        $this->assertSame('София', OutreachCall::where('external_id', 'osm:node/1')->value('city'));
        $this->assertNull(OutreachCall::where('external_id', 'osm:node/3')->value('source_url'));

        $this->actingAs($user)->get('/admin/outreach-calls')
            ->assertOk()
            ->assertSee('Механа Старата къща')
            ->assertSee('Ресторант')
            ->assertSee('href="tel:+359881234567"', false)
            ->assertSee('href="tel:032555666"', false)
            ->assertSee('Без сайт (за обаждане)');

        $this->actingAs($user)->get('/admin/outreach-calls?phone=1&city=' . urlencode('Пловдив'))
            ->assertSee('Автосервиз Мотор')
            ->assertDontSee('Салон Ирис')
            ->assertDontSee('Механа Старата къща');
    }

    public function test_status_and_note_survive_a_new_import(): void
    {
        $user = $this->admin();
        $this->artisan('creatium:outreach-import', ['--calls' => true])->assertSuccessful();
        $call = OutreachCall::where('external_id', 'osm:node/1')->firstOrFail();

        $this->actingAs($user)->patch("/admin/outreach-calls/{$call->id}", ['status' => 'interested', 'note' => 'Звънни в понеделник'])
            ->assertSessionHas('success');
        $this->assertNotNull($call->fresh()->called_at);

        file_put_contents($this->path, "external_id,name,phone,industry,location,source_url\nosm:node/1,Механа Старата къща (ново име),0888 000 000,restaurant,София,\n");
        $this->actingAs($user)->post('/admin/outreach-calls/import')->assertSessionHas('success', 'От сървъра: 0 нови, 1 обновени, 0 пропуснати, 0 извън България.');

        $call->refresh();
        $this->assertSame('interested', $call->status);
        $this->assertSame('Звънни в понеделник', $call->note);
        $this->assertSame('Механа Старата къща (ново име)', $call->name);
        $this->assertSame('0888 000 000', $call->phone);
    }

    public function test_foreign_businesses_already_imported_are_removed_unless_worked_on(): void
    {
        $user = $this->admin();
        OutreachCall::create(['external_id' => 'osm:node/4', 'name' => 'Glam by Lili', 'phone' => '+40 762 942 283']);
        OutreachCall::create(['external_id' => 'osm:node/5', 'name' => 'Pensiunea Dunarea', 'status' => 'no_answer', 'note' => 'Румъния']);

        $this->actingAs($user)->post('/admin/outreach-calls/import')->assertSessionHas('success');

        $this->assertFalse(OutreachCall::where('external_id', 'osm:node/4')->exists());
        $this->assertTrue(OutreachCall::where('external_id', 'osm:node/5')->exists());
        $this->assertTrue(OutreachCall::isForeign('0040 21 123', null));
        $this->assertFalse(OutreachCall::isForeign('+359 2 123 456', 'Велико Търново'));
        $this->assertFalse(OutreachCall::isForeign('088 123', 'Русе'));
    }

    public function test_missing_server_file_is_explained(): void
    {
        config(['creatium.outreach.harvey_no_website_csv' => '/nonexistent/no-website.csv']);

        $this->actingAs($this->admin())->post('/admin/outreach-calls/import')
            ->assertRedirect('/admin/outreach-calls')
            ->assertSessionHas('send_error');
    }

    public function test_guests_cannot_see_the_call_list(): void
    {
        $this->get('/admin/outreach-calls')->assertRedirect('/login');
    }
}
