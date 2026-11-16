<?php

namespace Tests\Feature;

use App\Models\ContentBlock;
use App\Support\ClientEmails;
use App\Support\PackageNames2026;
use App\Support\Plans;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Нови имена на пакетите навсякъде (17.11.2026): Core Изграждане, Pro Ускорение, Enterprise Партньорство (Core Build, Pro Accelerate, Enterprise Partnership). */
class PackageNamesTest extends TestCase
{
    use RefreshDatabase;

    private const OLD = ['Стартов двигател', 'Активен маркетинг и растеж', 'Индивидуален маркетинг', 'Starter Engine', 'Active Marketing and Growth', 'Custom Marketing'];

    private function urls(): array
    {
        preg_match_all('#<loc>([^<]+)</loc>#', $this->get('/sitemap.xml')->getContent(), $m);
        $paths = array_map(fn ($url) => parse_url($url, PHP_URL_PATH) ?: '/', $m[1]);

        return array_values(array_unique(array_merge($paths, [
            '/llms.txt', '/en/llms.txt', '/paketi', '/en/packages', '/usloviya', '/en/terms', '/vaprosi', '/en/faq', '/prezentatsiya', '/en/presentation',
        ])));
    }

    public function test_no_public_page_uses_an_old_package_name(): void
    {
        $failures = [];
        foreach (['current', 'g'] as $design) {
            config(['creatium.design' => $design]);
            foreach ($this->urls() as $path) {
                $response = $this->get($path);
                if ($response->status() === 404) {
                    continue;
                }
                $body = $response->getContent();
                foreach (self::OLD as $old) {
                    if (str_contains($body, $old)) {
                        $failures[] = "$design $path $old";
                    }
                }
            }
        }

        $this->assertSame([], $failures, implode("\n", $failures));
    }

    public function test_the_packages_page_shows_the_new_names_in_both_languages_and_designs(): void
    {
        $this->get('/paketi')->assertOk()->assertSee('Core Изграждане')->assertSee('Pro Ускорение')->assertSee('Enterprise Партньорство');
        $this->get('/en/packages')->assertOk()->assertSee('Core Build')->assertSee('Pro Accelerate')->assertSee('Enterprise Partnership');

        config(['creatium.design' => 'g']);
        $this->get('/paketi')->assertOk()->assertSee('CORE', false)->assertSee('Изграждане', false)->assertSee('Партньорство', false);
    }

    public function test_offer_emails_and_templates_use_the_new_names(): void
    {
        $failures = [];
        foreach (ClientEmails::all() as $key => $tpl) {
            $data = ['client_name' => 'Иван', 'business' => 'Автосервиз Иванов'];
            foreach ($tpl['fields'] as $name => $field) {
                $data[$name] ??= $field['default'] ?? match ($field['type'] ?? 'text') {
                    'date' => now()->addDays(7)->toDateString(),
                    'url' => 'https://example.com/demo',
                    default => 'Текст',
                };
            }
            $html = view($tpl['view'], ['data' => $data, 'package' => $tpl['package'] ?? null, 'subject' => ClientEmails::subject($tpl['subject'], $data)])->render()
                . ' ' . ($tpl['label'] ?? '') . ' ' . ($tpl['description'] ?? '') . ' ' . ClientEmails::subject($tpl['subject'], $data);
            foreach (self::OLD as $old) {
                if (str_contains($html, $old)) {
                    $failures[] = "$key $old";
                }
            }
        }

        $this->assertSame([], $failures, implode("\n", $failures));
        $this->assertSame('Оферта: Core Изграждане', ClientEmails::find('offer-start')['label']);
    }

    public function test_old_names_still_resolve_to_the_renamed_plans(): void
    {
        $bg = config('creatium.plans.items');
        $en = config('creatium_en.plans.items');
        $this->assertSame('Core Изграждане', Plans::find($bg, 'Стартов двигател')['name']);
        $this->assertSame('Pro Ускорение', Plans::find($bg, 'Активен маркетинг')['name']);
        $this->assertSame('Enterprise Партньорство', Plans::find($bg, 'Индивидуален маркетинг и мащабиране')['name']);
        $this->assertSame('Core Build', Plans::find($en, 'Starter Engine')['name']);
        $this->assertSame('Pro Accelerate', Plans::find($en, 'Active Marketing and Growth')['name']);
        $this->assertSame('Enterprise Partnership', Plans::find($en, 'Custom Marketing')['name']);
    }

    public function test_migration_replaces_only_exact_old_defaults_and_backs_up_once(): void
    {
        $oldQuestion = array_key_first(array_filter(PackageNames2026::REPLACEMENTS['bg'], fn ($new, $old) => str_starts_with($old, 'Цената е фиксирана и я знаете'), ARRAY_FILTER_USE_BOTH));
        $newQuestion = PackageNames2026::REPLACEMENTS['bg'][$oldQuestion];
        $this->assertStringContainsString('Core Изграждане е €400', $newQuestion);

        $plans = [
            'billing' => ['note' => 'Моя бележка за Стартов двигател'],
            'items' => [
                ['name' => 'Стартов двигател', 'card_items' => ['Всичко от Стартов двигател']],
                ['name' => 'Активен маркетинг и растеж', 'build_title' => 'Включва всичко от Стартов двигател, плюс'],
                ['name' => 'Индивидуален маркетинг (редактирано)', 'compare_note' => 'Какво точно влиза в Индивидуален маркетинг, уточняваме в индивидуалната оферта според нуждите ви.'],
            ],
        ];
        ContentBlock::create(['key' => 'plans', 'locale' => 'bg', 'value' => $plans]);
        ContentBlock::create(['key' => 'questions', 'locale' => 'bg', 'value' => [['q' => 'Колко струва?', 'a' => $oldQuestion]]]);
        ContentBlock::create(['key' => 'plans', 'locale' => 'en', 'value' => ['items' => [['name' => 'Starter Engine'], ['name' => 'Custom Marketing']]]]);
        ContentBlock::create(['key' => 'seo:packages', 'locale' => 'bg', 'value' => ['description' => 'Цени от €400 старт и €200/месец или €800 старт и €650/месец. Индивидуален маркетинг от €1 200/месец. Фиксирана цена и 2 месеца безплатно при годишен план.']]);
        ContentBlock::create(['key' => 'home_hero', 'locale' => 'bg', 'value' => ['title' => 'Нищо за сменяне']]);

        $changed = PackageNames2026::apply();
        $this->assertEqualsCanonicalizing(['plans/bg', 'questions/bg', 'plans/en', 'seo:packages/bg'], $changed);

        $row = ContentBlock::where(['key' => 'plans', 'locale' => 'bg'])->first()->value;
        $this->assertSame('Core Изграждане', $row['items'][0]['name']);
        $this->assertSame(['Всичко от Core Изграждане'], $row['items'][0]['card_items']);
        $this->assertSame('Pro Ускорение', $row['items'][1]['name']);
        $this->assertSame('Включва всичко от Core Изграждане, плюс', $row['items'][1]['build_title']);
        // Редактирани текстове (не са точно старият) остават.
        $this->assertSame('Моя бележка за Стартов двигател', $row['billing']['note']);
        $this->assertSame('Индивидуален маркетинг (редактирано)', $row['items'][2]['name']);
        $this->assertStringContainsString('Enterprise Партньорство, уточняваме', $row['items'][2]['compare_note']);

        $this->assertSame($newQuestion, ContentBlock::where('key', 'questions')->first()->value[0]['a']);
        $this->assertSame('Core Build', ContentBlock::where(['key' => 'plans', 'locale' => 'en'])->first()->value['items'][0]['name']);
        $this->assertSame('Enterprise Partnership', ContentBlock::where(['key' => 'plans', 'locale' => 'en'])->first()->value['items'][1]['name']);
        $this->assertStringContainsString('Enterprise Партньорство от €1 200/месец', ContentBlock::where('key', 'seo:packages')->first()->value['description']);
        $this->assertSame(['title' => 'Нищо за сменяне'], ContentBlock::where('key', 'home_hero')->first()->value);

        // Копие преди промяната, веднъж.
        $backup = ContentBlock::where(['key' => 'plans:' . PackageNames2026::BACKUP, 'locale' => 'bg'])->first();
        $this->assertSame($plans, $backup->value);
        $this->assertNull(ContentBlock::where('key', 'home_hero:' . PackageNames2026::BACKUP)->first());

        // Втори пуск не променя нищо и не презаписва копието.
        $this->assertSame([], PackageNames2026::apply());
        $this->assertSame($plans, ContentBlock::where(['key' => 'plans:' . PackageNames2026::BACKUP, 'locale' => 'bg'])->first()->value);
    }

    public function test_existing_backup_is_never_overwritten(): void
    {
        ContentBlock::create(['key' => 'plans:' . PackageNames2026::BACKUP, 'locale' => 'bg', 'value' => ['keep' => 'me']]);
        ContentBlock::create(['key' => 'plans', 'locale' => 'bg', 'value' => ['items' => [['name' => 'Стартов двигател']]]]);

        PackageNames2026::apply();

        $this->assertSame(['keep' => 'me'], ContentBlock::where(['key' => 'plans:' . PackageNames2026::BACKUP, 'locale' => 'bg'])->first()->value);
        $this->assertSame('Core Изграждане', ContentBlock::where(['key' => 'plans', 'locale' => 'bg'])->first()->value['items'][0]['name']);
    }
}
