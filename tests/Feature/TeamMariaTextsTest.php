<?php

namespace Tests\Feature;

use App\Models\ContentBlock;
use App\Support\Content;
use App\Support\TeamMariaTexts2026;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Новите текстове на Мария: пренасяне само на още непроменените стари стойности в запазените редакции. */
class TeamMariaTextsTest extends TestCase
{
    use RefreshDatabase;

    private function oldMaria(string $locale): array
    {
        $r = new \ReflectionClassConstant(TeamMariaTexts2026::class, 'OLD');
        $old = $r->getValue()[$locale];

        return [
            'name' => $locale === 'en' ? 'Maria Florova' : 'Мария Флорова',
            'role' => 'Role',
            'description' => $old['description'],
            'bio' => $old['bio'],
            'skills' => $old['skills'],
            'image' => 'images/team/maria-florova.webp',
            'linkedin' => '',
        ];
    }

    private function other(): array
    {
        return ['name' => 'Борислав Костадинов', 'role' => 'X', 'description' => 'Моето', 'bio' => ['a'], 'skills' => ['b']];
    }

    public function test_config_has_the_new_texts(): void
    {
        $bg = config('creatium.team')[2];
        $this->assertSame('Вярвам, че добрият маркетинг е повече от комуникация. Той създава бранд изживяване и превръща интереса в реален растеж.', $bg['description']);
        $this->assertStringContainsString('„Медии и комуникации“', $bg['bio'][0]);
        $this->assertSame(['Брандинг', 'Имейл маркетинг и CRM', 'Платена реклама', 'Съдържание и видео'], $bg['skills']);
        $en = config('creatium_en.team')[2];
        $this->assertSame(['Branding', 'Email marketing and CRM', 'Paid advertising', 'Content and video'], $en['skills']);
        $this->assertSame('', $bg['linkedin']);
    }

    public function test_replaces_old_defaults_backs_up_and_is_idempotent(): void
    {
        $other = $this->other();
        $bgOld = [$other, $this->oldMaria('bg')];
        ContentBlock::create(['key' => 'team', 'locale' => 'bg', 'value' => $bgOld]);
        ContentBlock::create(['key' => 'team', 'locale' => 'en', 'value' => [$this->oldMaria('en')]]);

        $this->assertSame(['team/bg', 'team/en'], TeamMariaTexts2026::apply());

        $bg = ContentBlock::where(['key' => 'team', 'locale' => 'bg'])->first()->value;
        $new = config('creatium.team')[2];
        $this->assertSame($other, $bg[0]);
        $this->assertSame($new['description'], $bg[1]['description']);
        $this->assertSame($new['bio'], $bg[1]['bio']);
        $this->assertSame($new['skills'], $bg[1]['skills']);
        $this->assertSame('Role', $bg[1]['role']);
        $en = ContentBlock::where(['key' => 'team', 'locale' => 'en'])->first()->value;
        $this->assertSame(config('creatium_en.team')[2]['skills'], $en[0]['skills']);
        $this->assertSame($bgOld, ContentBlock::where(['key' => 'team:backup-2026-11-13-b', 'locale' => 'bg'])->first()->value);
        $this->assertSame($new['skills'], app(Content::class)->get('team')[1]['skills']);

        $this->assertSame([], TeamMariaTexts2026::apply());
        $this->assertSame($bgOld, ContentBlock::where(['key' => 'team:backup-2026-11-13-b', 'locale' => 'bg'])->first()->value);
        $this->assertSame(2, ContentBlock::where('key', 'team:backup-2026-11-13-b')->count());
    }

    public function test_leaves_fields_the_admin_edited(): void
    {
        $maria = $this->oldMaria('bg');
        $maria['description'] = 'Редактирано от админа';
        $maria['bio'][1] = 'Моя редакция';
        ContentBlock::create(['key' => 'team', 'locale' => 'bg', 'value' => [$maria]]);

        $this->assertSame(['team/bg'], TeamMariaTexts2026::apply());

        $saved = ContentBlock::where(['key' => 'team', 'locale' => 'bg'])->first()->value[0];
        $this->assertSame('Редактирано от админа', $saved['description']);
        $this->assertSame($maria['bio'], $saved['bio']);
        $this->assertSame(config('creatium.team')[2]['skills'], $saved['skills']);
    }

    public function test_nothing_to_do_when_everything_was_edited_or_no_override(): void
    {
        $this->assertSame([], TeamMariaTexts2026::apply());

        $maria = $this->oldMaria('bg');
        $maria['description'] = 'A';
        $maria['bio'] = ['B'];
        $maria['skills'] = ['C'];
        ContentBlock::create(['key' => 'team', 'locale' => 'bg', 'value' => [$maria]]);

        $this->assertSame([], TeamMariaTexts2026::apply());
        $this->assertNull(ContentBlock::where('key', 'team:backup-2026-11-13-b')->first());
    }
}
