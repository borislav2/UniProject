<?php

namespace Tests\Feature;

use App\Models\OutreachCall;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
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

    private function business(string $name, array $values = []): OutreachCall
    {
        $call = new OutreachCall;
        $call->forceFill($values + ['external_id' => 'x:' . $name, 'name' => $name, 'status' => 'new', 'city' => 'Пловдив', 'industry' => 'dentist', 'phone' => '032 111 222'])->save();

        return $call;
    }

    public function test_mobile_numbers_are_recognised_and_filtered(): void
    {
        $this->assertTrue(OutreachCall::isMobileNumber('0888 123 456'));
        $this->assertTrue(OutreachCall::isMobileNumber('+359 89 912 3456'));
        $this->assertTrue(OutreachCall::isMobileNumber('00359988123456'));
        $this->assertFalse(OutreachCall::isMobileNumber('032 555 666'));
        $this->assertFalse(OutreachCall::isMobileNumber('02 981 1234'));

        $user = $this->admin();
        $mixed = $this->business('Автосервиз Мотор', ['phone' => '032 555 666; 0899 111 222']);
        $this->business('Стационарен', ['phone' => '032 444 555']);
        $this->assertTrue($mixed->is_mobile);
        $this->assertSame(['0899 111 222', '032 555 666'], array_column($mixed->phones(), 'label'), 'The mobile number comes first');

        $this->actingAs($user)->get('/admin/outreach-calls?mobile=mobile')->assertSee('Автосервиз Мотор')->assertDontSee('Стационарен');
        $this->actingAs($user)->get('/admin/outreach-calls?mobile=landline')->assertSee('Стационарен')->assertDontSee('Автосервиз Мотор');

        $mixed->update(['phone' => '032 555 666']);
        $this->assertFalse($mixed->fresh()->is_mobile);
    }

    public function test_next_call_follows_the_queue_and_records_the_result(): void
    {
        Carbon::setTestNow('2026-10-27 08:00:00'); // вторник, 10:00 в София
        $user = $this->admin();
        $landline = $this->business('Нов стационарен');
        $mobile = $this->business('Нов мобилен', ['phone' => '0888 111 222']);
        $due = $this->business('Уговорен за днес', ['status' => 'interested', 'callback_on' => '2026-10-27', 'called_at' => '2026-10-20 09:00:00']);
        $this->business('Уговорен за после', ['status' => 'interested', 'callback_on' => '2026-11-03']);
        $this->business('Не вдигна вчера', ['status' => 'no_answer', 'called_at' => '2026-10-26 09:00:00']);
        $this->business('Не вдигна днес', ['status' => 'no_answer', 'called_at' => '2026-10-27 07:00:00']);
        $this->business('Не вдигна два пъти', ['status' => 'no_answer', 'called_at' => '2026-10-20 09:00:00'])->forceFill(['attempts' => 2])->save();
        $this->business('Без интерес', ['status' => 'not_interested', 'callback_on' => '2026-10-27']);
        $this->business('Друг град', ['city' => 'Варна']);

        $this->assertSame(['Уговорен за днес', 'Нов мобилен', 'Нов стационарен', 'Не вдигна вчера'],
            OutreachCall::where('city', 'Пловдив')->queue()->pluck('name')->all());

        $this->actingAs($user)->get('/admin/outreach-calls?city=' . urlencode('Пловдив'))
            ->assertSee('Обади се отново днес (1)')
            ->assertSee('4 в опашката');

        $this->actingAs($user)->get('/admin/outreach-calls/next?city=' . urlencode('Пловдив'))
            ->assertOk()->assertSee('Уговорен за днес')->assertSee('Уговорено обаждане за 27.10.2026')
            ->assertSee('Скрипт за разговора')->assertSee('остават 4');

        // Резултатът се записва, уговорената дата (днес) пада, и следва следващият.
        $this->actingAs($user)->patch("/admin/outreach-calls/{$due->id}", ['status' => 'interested', 'note' => 'имейл: x@y.bg', 'callback_on' => '2026-10-27', 'next' => 1, 'city' => 'Пловдив'])
            ->assertRedirect('/admin/outreach-calls/next?city=' . urlencode('Пловдив'));
        $due->refresh();
        $this->assertNull($due->callback_on);
        $this->assertSame(1, $due->attempts);

        $this->actingAs($user)->get('/admin/outreach-calls/next?city=' . urlencode('Пловдив'))->assertSee('Нов мобилен');

        // „Не вдигна“ с ново обаждане след седмица излиза от опашката до тогава.
        $this->actingAs($user)->patch("/admin/outreach-calls/{$mobile->id}", ['status' => 'no_answer', 'callback_on' => '2026-11-03', 'next' => 1, 'city' => 'Пловдив']);
        $this->assertSame('2026-11-03', $mobile->fresh()->callback_on->toDateString());

        // „Пропусни“ скрива бизнеса до края на сесията.
        $this->actingAs($user)->post("/admin/outreach-calls/{$landline->id}/skip", ['city' => 'Пловдив'])->assertRedirect('/admin/outreach-calls/next?city=' . urlencode('Пловдив'));
        $this->actingAs($user)->get('/admin/outreach-calls/next?city=' . urlencode('Пловдив'))->assertSee('Не вдигна вчера')->assertSee('остават 1');

        // Броячът за деня: две обаждания днес (плюс „Не вдигна днес“ от сутринта).
        $this->actingAs($user)->get('/admin/outreach-calls')->assertSee('Днес: 3')->assertSee('от 40 обаждания');

        // Бележка, запазена пак същия ден, не е второ обаждане.
        $this->actingAs($user)->patch("/admin/outreach-calls/{$due->id}", ['status' => 'interested', 'note' => 'ново']);
        $this->assertSame(1, $due->fresh()->attempts);

        Carbon::setTestNow('2026-11-03 08:00:00');
        $this->assertContains('Нов мобилен', OutreachCall::queue()->pluck('name')->all());
        Carbon::setTestNow();
    }
}
