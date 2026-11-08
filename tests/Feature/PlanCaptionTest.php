<?php

namespace Tests\Feature;

use App\Models\ContentBlock;
use App\Support\PlanCaption2026;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlanCaptionTest extends TestCase
{
    use RefreshDatabase;

    public function test_old_caption_in_admin_copy_is_replaced_once_with_a_backup(): void
    {
        ContentBlock::create(['key' => 'plans', 'locale' => 'bg', 'value' => ['items' => [
            ['name' => 'Активен', 'prices' => ['€800 еднократно за изграждането или поправката на сегашния ви сайт и първия месец', 'след това €650 / месец']],
        ]]]);

        $this->assertSame(['plans/bg'], PlanCaption2026::apply());
        $this->assertSame('€800 еднократно за нов или обновен сайт и първия месец', ContentBlock::where('key', 'plans')->first()->value['items'][0]['prices'][0]);
        $this->assertNotNull(ContentBlock::where('key', 'plans:backup-2026-11-09-a')->first());
        $this->assertSame([], PlanCaption2026::apply());
    }

    public function test_packages_page_has_the_new_caption(): void
    {
        $this->get('/paketi')->assertOk()->assertSee('нов или обновен сайт')->assertDontSee('поправката');
    }
}
