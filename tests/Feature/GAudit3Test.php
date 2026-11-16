<?php

namespace Tests\Feature;

use App\Http\Controllers\PageController;
use App\Models\ContentBlock;
use App\Models\Project;
use App\Support\GAudit3_2026;
use App\Support\GPanel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/** Design G, third round (16.11.2026, the owners' third audit). */
class GAudit3Test extends TestCase
{
    use RefreshDatabase;

    private function g(): void
    {
        config(['creatium.design' => 'g']);
    }

    public function test_packages_have_a_tier_tag_above_the_bulgarian_name_in_g_only(): void
    {
        $this->g();
        $html = $this->get('/paketi')->assertOk()->getContent();
        foreach ([['CORE', 'Изграждане'], ['PRO', 'Ускорение'], ['ENTERPRISE', 'Партньорство']] as [$tag, $name]) {
            $this->assertMatchesRegularExpression('~<p class="g-tier g-mono">' . $tag . '</p>\s*<h3>' . $name . '</h3>~u', $html);
        }
        $this->assertStringNotContainsString('Дигитален старт', $html);
        $this->assertStringContainsString('<b class="g-ptag g-mono">CORE</b> Изграждане <small>(от €500/месец)</small>', $html);
        $this->assertStringContainsString('<b class="g-ptag g-mono">ENTERPRISE</b> Партньорство <small>(от €1', $html);

        config(['creatium.design' => 'current']);
        $this->get('/paketi')->assertOk()->assertSee('Стартов двигател')->assertDontSee('g-tier');
    }

    public function test_packages_subtitle_sits_under_the_heading_on_home(): void
    {
        $this->g();
        $html = $this->get('/')->assertOk()->getContent();
        $this->assertMatchesRegularExpression('~<h2 class="g-h">План за всеки етап от вашия бизнес</h2>\s*<p class="g-sh-text g-under">От стартиращата идея~u', $html);
        $this->assertStringNotContainsString('g-sh g-sh-split">' . "\n" . '            <h2 class="g-h">План', $html);
    }

    public function test_goal_text_is_the_owners_text_without_the_typo(): void
    {
        $this->g();
        $this->get('/')->assertSee('Работим като агенция, която вниква в детайла, говори ясно и остава до вас.')->assertDontSee('влиза вниква');
        $this->assertStringEndsNotWith('пускането на сайта.', config('creatium.home_trust.g_goal_text'));
    }

    public function test_every_g_cta_leads_to_the_nearest_single_form(): void
    {
        $this->g();
        // Home: the form of the page. Packages: its own #oferta with ?paket. Elsewhere: the contact page form.
        $home = $this->get('/')->assertOk()->getContent();
        $this->assertStringContainsString('class="g-btn g-sm g-offer" href="#kontakt"', $home);
        $this->assertStringContainsString('href="?paket=1#kontakt"', $home);
        $this->assertStringNotContainsString('/paketi#oferta', $home);
        $this->assertSame(1, preg_match_all('/<form [^>]*data-contact-form/', $home));

        $packs = $this->get('/paketi')->assertOk()->getContent();
        $this->assertStringContainsString('href="?paket=2#oferta"', $packs);
        $this->assertStringContainsString('id="oferta"', $packs);
        $this->assertStringContainsString('id="kontakt"', $packs, 'both anchors work on every page with the form');
        $this->assertSame(1, preg_match_all('/<form [^>]*data-contact-form/', $packs));
        $this->assertStringNotContainsString(url('/paketi') . '/oferta', $packs);

        foreach (['/uslugi', '/za-nas', '/proekti', '/vaprosi'] as $path) {
            $page = $this->get($path)->assertOk()->getContent();
            $this->assertStringContainsString('class="g-btn g-sm g-offer" href="' . url('/kontakti') . '#kontakt"', $page, $path);
            $this->assertStringNotContainsString('data-contact-form', $page, $path);
        }
        $contact = $this->get('/kontakti')->assertOk()->getContent();
        $this->assertSame(1, preg_match_all('/<form [^>]*data-contact-form/', $contact));
        $this->assertStringContainsString('<h1 class="g-h g-ct-h">Обсъдете проекта си с нас</h1>', $contact);
        $this->assertStringNotContainsString('quote-form', $contact);
        $this->assertStringNotContainsString('action="' . url('/paketi/oferta') . '"', $packs);
    }

    public function test_the_form_is_preselected_by_paket_and_napravleniya(): void
    {
        $this->g();
        $html = $this->get('/kontakti?paket=1&napravleniya=google,seo,nope#kontakt')->assertOk()->getContent();
        $this->assertMatchesRegularExpression('~name="g_plans\[\]" value="1" checked~', $html);
        $this->assertDoesNotMatchRegularExpression('~name="g_plans\[\]" value="0" checked~', $html);
        $this->assertMatchesRegularExpression('~name="g_directions\[\]" value="google" checked~', $html);
        $this->assertMatchesRegularExpression('~name="g_directions\[\]" value="seo" checked~', $html);
        $this->assertDoesNotMatchRegularExpression('~name="g_directions\[\]" value="ads" checked~', $html);
        $this->get('/kontakti?paket=99')->assertOk();
    }

    public function test_the_g_form_saves_plans_directions_budget_and_business_in_the_lead(): void
    {
        $this->g();
        Mail::fake();
        $this->post('/kontakti', [
            'form' => 'g', 'name' => 'Иван Петров', 'email' => 'Ivan@Example.com', 'business_site' => 'motorpro.bg',
            'g_plans' => ['1'], 'g_directions' => ['seo', 'software'], 'budget' => '800-1500', 'consent' => '1',
        ])->assertSessionHasNoErrors();

        $lead = Project::latest('id')->first();
        $this->assertNull($lead->client_phone, 'the phone is optional in the G form');
        $this->assertSame('ivan@example.com', $lead->client_email);
        $this->assertSame('Оферта за Pro Ускорение', $lead->service);
        $this->assertSame('https://motorpro.bg', $lead->website_url);
        $this->assertStringContainsString('Интересува се от пакет Pro Ускорение.', $lead->description);
        $this->assertStringContainsString('Интересува се от SEO и видимост в AI търсачки (ChatGPT, Gemini), Разработка на собствен софтуер и интеграции.', $lead->description);
        $this->assertStringContainsString('Бизнес или сайт motorpro.bg', $lead->description);
        $this->assertStringContainsString('Ориентировъчен месечен бюджет €800 до €1 500', $lead->description);
        $this->assertStringNotContainsString(':', $lead->description);
        Mail::assertSent(\App\Mail\NewLeadMail::class);

        // No package: the directions are the service. Nothing chosen: the generic label.
        $this->post('/kontakti', ['form' => 'g', 'name' => 'А', 'email' => 'a@b.bg', 'g_directions' => ['ads'], 'consent' => '1'])->assertSessionHasNoErrors();
        $this->assertSame('Реклама в Google и Meta', Project::latest('id')->first()->service);
        $this->post('/kontakti', ['form' => 'g', 'name' => 'Б', 'email' => 'b@b.bg', 'consent' => '1'])->assertSessionHasNoErrors();
        $this->assertSame('Запитване от сайта', Project::latest('id')->first()->service);
    }

    public function test_the_g_form_validation_and_the_old_form_still_needs_a_phone(): void
    {
        $this->g();
        $this->withoutMiddleware(\Illuminate\Routing\Middleware\ThrottleRequests::class);
        $ok = ['form' => 'g', 'name' => 'А', 'email' => 'a@b.bg', 'consent' => '1'];
        $this->post('/kontakti', array_diff_key($ok, ['email' => 1]))->assertSessionHasErrors('email');
        $this->post('/kontakti', array_diff_key($ok, ['name' => 1]))->assertSessionHasErrors('name');
        $this->post('/kontakti', array_diff_key($ok, ['consent' => 1]))->assertSessionHasErrors('consent');
        $this->post('/kontakti', $ok + ['g_plans' => ['9']])->assertSessionHasErrors('g_plans.0');
        $this->post('/kontakti', $ok + ['g_directions' => ['x']])->assertSessionHasErrors('g_directions.0');
        $this->post('/kontakti', $ok + ['phone' => 'abc'])->assertSessionHasErrors('phone');
        // A booked call needs a number.
        $slot = \App\Support\Booking::days()[0]['slots'][0]['value'];
        $this->post('/kontakti', $ok + ['consultation' => $slot])->assertSessionHasErrors('phone');
        $this->post('/kontakti', $ok + ['consultation' => $slot, 'phone' => '0888123456'])->assertSessionHasNoErrors();

        // Without form=g the old rules apply: the phone is required.
        $this->post('/kontakti', ['name' => 'А', 'email' => 'a@b.bg', 'consent' => '1'])->assertSessionHasErrors('phone');
        // The honeypot stores nothing.
        $before = Project::count();
        $this->post('/kontakti', $ok + ['website' => 'spam'])->assertSessionHasNoErrors();
        $this->assertSame($before, Project::count());
    }

    public function test_the_panel_rules_and_the_three_presets(): void
    {
        $this->assertSame(['full', 'traffic', 'base', 'low', 'mixed'], GPanel::RULE_ORDER);
        // The owners' presets give the scenarios they name.
        $this->assertSame(['count' => 2, 'percent' => 28, 'message' => 'low'], array_intersect_key(GPanel::state(GPanel::PRESETS['start']), array_flip(['count', 'percent', 'message'])));
        $this->assertSame(['count' => 2, 'percent' => 28, 'message' => 'traffic'], array_intersect_key(GPanel::state(GPanel::PRESETS['ads']), array_flip(['count', 'percent', 'message'])));
        $this->assertSame(['count' => 7, 'percent' => 100, 'message' => 'full'], array_intersect_key(GPanel::state(GPanel::PRESETS['full']), array_flip(['count', 'percent', 'message'])));
        $this->assertSame('low', GPanel::state([])['message']);
        $this->assertSame('low', GPanel::state([5])['message']);
        $this->assertSame('traffic', GPanel::state([2, 5])['message']);
        $this->assertSame('base', GPanel::state([4, 5, 6])['message']);
        $this->assertSame('mixed', GPanel::state([1, 4, 5, 6])['message']);
        $this->assertSame('mixed', GPanel::state([2, 4, 5, 6])['message'], 'traffic with the whole base is mixed');
        $this->assertSame('full', GPanel::state([1, 2, 3, 4, 5, 6])['message'], '6 active win over the other rules');
        $this->assertSame([0, 14, 28, 42, 57, 71, 85, 100], array_map(fn ($n) => GPanel::state(range(1, 7) ? array_slice(range(1, 7), 0, $n) : [])['percent'], range(0, 7)));
        $this->assertSame('start', GPanel::state([1, 5])['preset']);
        $this->assertNull(GPanel::state([1, 4])['preset']);
    }

    public function test_the_panel_button_ticks_the_missing_directions(): void
    {
        $this->g();
        $this->assertSame(['website', 'seo', 'ads', 'email'], GPanel::state([1, 5])['directions']);
        $this->assertSame([], GPanel::state(range(1, 7))['directions']);
        $this->assertSame(url('/kontakti') . '#kontakt', GPanel::contactUrl([]));

        $html = $this->get('/uslugi')->assertOk()->getContent();
        $this->assertStringContainsString('Проверете как работи системата ви в момента', $html);
        $this->assertStringContainsString('Изберете активните си канали и инструменти от списъка.', $html);
        $this->assertSame(7, substr_count($html, 'role="switch"'));
        $this->assertSame(2, substr_count($html, 'aria-checked="true"'));
        $this->assertStringContainsString('<b data-count>2</b>', $html);
        $this->assertStringContainsString('(28%)', $html);
        $this->assertStringContainsString('В момента разчитате основно на препоръки.', $html);
        $this->assertSame(1, preg_match_all('/<p class="g-panel-msg"/', $html), 'only one message is rendered');
        $this->assertStringNotContainsString('>Плащате за трафик', $html);
        $this->assertStringContainsString('data-msg-traffic=', $html);
        $this->assertStringContainsString('href="' . url('/kontakti') . '?napravleniya=website,seo,ads,email#kontakt"', $html);
        foreach (['Стартиращ бизнес', 'Бизнес с реклама', 'Пълна система', 'Вижте как да допълним липсващото', 'Автоматични имейли и проследяване'] as $text) {
            $this->assertStringContainsString($text, $html);
        }
        $this->assertStringNotContainsString('data-funnel', $html);
        $this->assertStringNotContainsString('g-fn', $html);

        $js = file_get_contents(resource_path('js/g-bar.js'));
        $this->assertStringContainsString("if (on.length >= 6) return 'full';", $js);
        $this->assertStringContainsString("'aria-pressed', 'false'", $js);
    }

    public function test_software_development_is_a_seventh_service_everywhere_in_g_and_hidden_from_the_current_menus(): void
    {
        $this->assertTrue(config('creatium.service_pages.software.g_only'));
        $this->assertSame('razrabotka-na-softuer', config('creatium.service_pages.software.slug'));
        $this->assertSame('software-development', config('creatium_en.service_pages.software.slug'));
        $this->assertNull(config('creatium.service_pages.software.package'));

        $this->get('/uslugi/razrabotka-na-softuer')->assertOk()->assertSee('Софтуер по поръчка за вашия бизнес');
        $this->get('/en/services/software-development')->assertOk()->assertSee('Custom software for your business');
        $this->assertStringContainsString('/uslugi/razrabotka-na-softuer', $this->get('/sitemap.xml')->getContent());
        $this->assertStringContainsString('/en/services/software-development', $this->get('/sitemap.xml')->getContent());
        $this->assertStringContainsString('razrabotka-na-softuer', $this->get('/llms.txt')->getContent());
        $this->assertStringContainsString('Софтуер по поръчка за вашия бизнес', \App\Support\Chat\SiteKnowledge::text());

        // The current design: no menu entry, no „other services“ link, the same /uslugi.
        foreach (['/', '/uslugi', '/uslugi/izrabotka-na-sait'] as $path) {
            $this->assertStringNotContainsString('razrabotka-na-softuer', $this->get($path)->getContent(), $path);
        }

        $this->g();
        $services = $this->get('/uslugi')->assertOk()->getContent();
        $this->assertStringContainsString('data-g-service="software"', $services);
        $this->assertStringContainsString('Цена по оферта', $services);
        $page = $this->get('/uslugi/razrabotka-na-softuer')->assertOk()->getContent();
        $this->assertStringContainsString('application/ld+json', $page);
        $this->assertStringContainsString('Цена по оферта', $page);
        $this->assertStringContainsString('razrabotka-na-softuer', $this->get('/uslugi/izrabotka-na-sait')->getContent());
    }

    public function test_services_page_has_the_owners_heading_and_text_and_survives_an_older_admin_save(): void
    {
        $this->g();
        $this->get('/uslugi')->assertOk()
            ->assertSee('Спрете да купувате дигитални услуги. Време е да изградите дигитален актив.')
            ->assertSee('Затова обединяваме 7 ключови процеса в един общ механизъм, който движи бизнеса ви напред.');
        ContentBlock::create(['key' => 'services_page', 'locale' => 'bg', 'value' => ['title' => 'Старо', 'intro' => 'Старо въведение', 'cta_title' => 'Х', 'cta_button' => 'Ъ']]);
        $this->get('/uslugi')->assertOk()->assertSee('Спрете да купувате дигитални услуги.')->assertSee('data-g-panel', false);
        $this->get('/en/services')->assertOk()->assertSee('Check how your system works right now');
    }

    public function test_the_header_services_dropdown_lists_the_seven_services_and_the_mobile_menu_too(): void
    {
        $this->g();
        $html = $this->get('/')->assertOk()->getContent();
        $this->assertStringContainsString('data-g-dd', $html);
        $this->assertStringContainsString('class="g-dd-btn" aria-expanded="false" aria-controls="g-dd-services"', $html);
        $panel = substr($html, strpos($html, 'class="g-dd-panel"'), 1500);
        $this->assertSame(7, substr_count(substr($panel, 0, strpos($panel, '</div>')), '<a '));
        $this->assertStringContainsString('Разработка на софтуер', $panel);
        $this->assertSame(7, substr_count($html, 'class="g-sub g-mm-child"'));
        $this->assertStringContainsString('.design-g .g-dd:hover .g-dd-panel', file_get_contents(resource_path('css/g.css')));
        $this->assertStringContainsString("event.key !== 'Escape'", file_get_contents(resource_path('js/g-bar.js')));

        config(['creatium.design' => 'current']);
        $this->assertStringNotContainsString('data-g-dd', $this->get('/')->getContent());
    }

    public function test_the_steps_have_the_hover_visualisation_and_work_without_javascript(): void
    {
        $this->g();
        $html = $this->get('/paketi')->assertOk()->getContent();
        $this->assertSame(5, substr_count($html, '<li tabindex="0"><span class="g-n g-mono">'));
        $css = file_get_contents(resource_path('css/g-pages.css'));
        $this->assertStringContainsString('.g-steps[data-active] li.is-reached::before', $css);
        $this->assertStringContainsString('.g-steps[data-active] li > * { opacity: .5; }', $css);
        $this->assertStringNotContainsString('data-active', $html, 'the state is set by JavaScript only');
        $this->assertStringContainsString("addEventListener('focus'", file_get_contents(resource_path('js/g-bar.js')));
    }

    public function test_about_has_the_owners_intro_standards_and_team_texts_with_a_fallback(): void
    {
        $this->g();
        $html = $this->get('/za-nas')->assertOk()->getContent();
        $this->assertStringContainsString('Създадохме Creatium Lab с мисията да подпомагаме компании в България и чужбина', $html);
        $this->assertStringContainsString('на всеки етап от развитието на бизнеса ви.', $html);
        $this->assertStringContainsString('Защо го правим по този начин', $html);
        foreach (['Не губим време в излишни приказки', 'Играем по новите правила на интернет', 'Гледаме на вашия бизнес като на свой собствен', 'Всичко, което изградим, остава за вас'] as $title) {
            $this->assertStringContainsString('<dt>' . $title . '</dt>', $html);
        }
        $this->assertStringNotContainsString('Нашите стандарти', $html);
        foreach (['Full-Stack Developer и системна архитектура', 'Head of Growth и дигитален маркетинг', 'Бранд стратегия, съдържание и имейл маркетинг', 'Привличам точните клиенти от Google, Meta и AI търсачките', 'Изграждам разпознаваемия облик и гласа на бранда', '<li>Оптимизация на продажби и рекламни бюджети</li>', '<li>Бизнес комуникация и социални мрежи</li>'] as $text) {
            $this->assertStringContainsString($text, $html);
        }

        // A saved team without g_* keys keeps working with the normal fields.
        ContentBlock::create(['key' => 'team', 'locale' => 'bg', 'value' => [['name' => 'Борислав Костадинов', 'role' => 'Програмист', 'description' => 'Стар текст.', 'skills' => ['Стар навик']]]]);
        app(\App\Support\Content::class)->flush();
        $old = $this->get('/za-nas')->assertOk()->getContent();
        $this->assertStringContainsString('Програмист', $old);
        $this->assertStringContainsString('<li>Стар навик</li>', $old);

        // The current design keeps the current team texts.
        config(['creatium.design' => 'current']);
        ContentBlock::query()->delete();
        app(\App\Support\Content::class)->flush();
        $cur = $this->get('/za-nas')->assertOk()->getContent();
        $this->assertStringContainsString('Правя сайта ви така, че да се зарежда бързо', $cur);
        $this->assertStringNotContainsString('Превръщам бизнес целите', $cur);
    }

    public function test_the_new_keys_exist_in_both_languages_and_every_new_text_follows_the_copy_rule(): void
    {
        foreach (['creatium', 'creatium_en'] as $file) {
            $c = config($file);
            foreach (['CORE', 'PRO', 'ENTERPRISE'] as $i => $tag) {
                $this->assertSame($tag, $c['plans']['g_items'][$i]['tag'], $file);
            }
            $this->assertNotEmpty($c['services_page']['g_title'], $file);
            $this->assertCount(7, $c['services_page']['g_panel']['switches'], $file);
            $this->assertCount(5, $c['contact_budgets_g'], $file);
            $this->assertCount(6, $c['contact_directions_g'], $file);
            $this->assertCount(4, $c['about']['g_standards'], $file);
            $this->assertCount(2, $c['about']['g_intro'], $file);
            foreach ($c['team'] as $member) {
                foreach (['g_role', 'g_description', 'g_bio', 'g_skills'] as $field) {
                    $this->assertNotEmpty($member[$field], "$file {$member['name']} $field");
                }
            }
            $text = json_encode([$c['services_page'], $c['contact_budgets_g'], $c['contact_directions_g'], $c['service_pages']['software'], $c['about'], $c['team'], $c['home_trust']['g_goal_text']], JSON_UNESCAPED_UNICODE);
            $strings = [];
            $sp = $c['services_page'];
            array_walk_recursive($sp, function ($v) use (&$strings) { $strings[] = (string) $v; });
            foreach ([$c['contact_budgets_g'], $c['contact_directions_g'], $c['service_pages']['software'], $c['about'], $c['team']] as $part) {
                array_walk_recursive($part, function ($v) use (&$strings) { $strings[] = (string) $v; });
            }
            foreach ($strings as $string) {
                if (str_starts_with($string, 'images/') || str_starts_with($string, 'http')) {
                    continue;
                }
                $this->assertSame([], \Tests\Feature\PlainCopyTest::offences($string), "$file: $string");
            }
            $this->assertNotSame('', $text);
        }
        $this->assertSame(['Изграждане', 'Ускорение', 'Партньорство'], array_column(config('creatium.plans.g_items'), 'name'));
        // The old design keeps its plan names.
        $this->assertSame('Стартов двигател', config('creatium.plans.items.0.name'));
        // Every direction key a panel switch points to exists in the form.
        foreach (array_filter(GPanel::DIRECTIONS) as $key) {
            $this->assertArrayHasKey($key, config('creatium.contact_directions_g'));
        }
    }

    public function test_g_pages_have_no_dashes_or_colons_and_english_has_no_bulgarian(): void
    {
        $this->g();
        foreach (['/', '/paketi', '/kontakti', '/uslugi', '/za-nas', '/proekti', '/uslugi/razrabotka-na-softuer', '/en', '/en/packages', '/en/contact', '/en/services', '/en/about', '/en/services/software-development'] as $path) {
            $text = PlainCopyTest::visibleText($this->get($path)->assertOk()->getContent());
            $this->assertSame([], PlainCopyTest::offences($text), $path);
            if (str_starts_with($path, '/en')) {
                $this->assertDoesNotMatchRegularExpression('/\b[А-Яа-я]{4,}\b/u', preg_replace('/Борислав|Костадинов|Владимир|Цончев|Мария|Флорова/u', '', $text), $path);
            }
        }
    }

    public function test_the_migration_replaces_only_exact_old_defaults_adds_tags_and_team_fields_and_backs_up(): void
    {
        $oldGoal = 'Искаме да подкрепим всеки малък, среден и голям бизнес в България и в чужбина и истински да помогнем на всеки, който има нужда от дигитално присъствие. Работим като агенция, която влиза в детайла, говори ясно и остава до вас и след пускането на сайта. Започваме с разговор и с ясен план за следващите стъпки.';
        ContentBlock::create(['key' => 'home_trust', 'locale' => 'bg', 'value' => ['g_goal_text' => $oldGoal, 'g_goal_title' => 'Мой заглавие']]);
        ContentBlock::create(['key' => 'home_trust', 'locale' => 'en', 'value' => ['g_goal_text' => 'My own English text.']]);
        ContentBlock::create(['key' => 'plans', 'locale' => 'bg', 'value' => ['intro' => 'Моето въведение', 'g_items' => [
            ['name' => 'Дигитален старт', 'monthly' => 500], ['name' => 'Мой пакет', 'monthly' => 800], ['name' => 'Стратегически маркетинг', 'monthly' => 1200],
        ]]]);
        ContentBlock::create(['key' => 'about', 'locale' => 'bg', 'value' => [
            'g_intro' => [
                'Creatium Lab е дигитална агенция за бизнеси в България. Свързваме бранда, сайта, видимостта в Google и AI асистентите, рекламите и имейлите в една система, която води до запитвания.',
                'Работим по ясен метод и с фиксирана цена, записана в офертата. Всеки месец получавате отчет какво е свършено и какво следва.',
            ],
            'g_standards_title' => 'Нашите стандарти',
            'g_standards' => [['title' => 'Фиксирана цена', 'text' => 'Редактирано от админа.']],
        ]]);
        ContentBlock::create(['key' => 'team', 'locale' => 'bg', 'value' => [
            ['name' => 'Борислав Костадинов', 'role' => 'Мой роля', 'description' => 'Моето описание.', 'skills' => ['а']],
            ['name' => 'Непознат Човек', 'role' => 'Х'],
        ]]);

        $changed = GAudit3_2026::apply();
        $this->assertEqualsCanonicalizing(['home_trust/bg', 'plans/bg', 'about/bg', 'team/bg'], $changed);

        $this->assertSame(config('creatium.home_trust.g_goal_text'), ContentBlock::where('key', 'home_trust')->where('locale', 'bg')->first()->value['g_goal_text']);
        $this->assertSame('Мой заглавие', ContentBlock::where('key', 'home_trust')->where('locale', 'bg')->first()->value['g_goal_title']);
        $this->assertSame('My own English text.', ContentBlock::where('key', 'home_trust')->where('locale', 'en')->first()->value['g_goal_text']);
        $this->assertSame($oldGoal, ContentBlock::where('key', 'home_trust:backup-2026-11-16')->where('locale', 'bg')->first()->value['g_goal_text']);

        $items = ContentBlock::where('key', 'plans')->first()->value['g_items'];
        $this->assertSame(['Изграждане', 'CORE'], [$items[0]['name'], $items[0]['tag']]);
        $this->assertSame('Мой пакет', $items[1]['name']);
        $this->assertArrayNotHasKey('tag', $items[1], 'a renamed plan gets no tag');
        $this->assertSame(['Партньорство', 'ENTERPRISE'], [$items[2]['name'], $items[2]['tag']]);
        $this->assertSame(1200, $items[2]['monthly']);

        $about = ContentBlock::where('key', 'about')->first()->value;
        $this->assertSame(config('creatium.about.g_intro'), $about['g_intro']);
        $this->assertSame('Защо го правим по този начин', $about['g_standards_title']);
        $this->assertSame([['title' => 'Фиксирана цена', 'text' => 'Редактирано от админа.']], $about['g_standards'], 'an edited list stays');

        $team = ContentBlock::where('key', 'team')->first()->value;
        $this->assertSame('Мой роля', $team[0]['role']);
        $this->assertSame('Моето описание.', $team[0]['description']);
        $this->assertSame(config('creatium.team.0.g_role'), $team[0]['g_role']);
        $this->assertSame(config('creatium.team.0.g_skills'), $team[0]['g_skills']);
        $this->assertArrayNotHasKey('g_role', $team[1]);
        $this->assertSame('Мой роля', ContentBlock::where('key', 'team:backup-2026-11-16')->first()->value[0]['role']);

        // A second run changes nothing and keeps the first backup.
        $this->assertSame([], GAudit3_2026::apply());
        $this->assertArrayNotHasKey('g_role', ContentBlock::where('key', 'team:backup-2026-11-16')->first()->value[0]);

        // Every new text is the current config default.
        foreach (GAudit3_2026::CHANGES as $key => $paths) {
            foreach ($paths as $path => $map) {
                foreach ($map as $new) {
                    $this->assertTrue(in_array($new, [data_get(config('creatium.' . $key), $path), data_get(config('creatium_en.' . $key), $path)], true), "$key.$path");
                }
            }
        }
        foreach (GAudit3_2026::LISTS as $key => $paths) {
            foreach ($paths as $path => $pairs) {
                foreach ($pairs as [, $new]) {
                    $this->assertTrue($new === data_get(config('creatium.' . $key), $path) || $new === data_get(config('creatium_en.' . $key), $path), "$key.$path");
                }
            }
        }
    }

    public function test_the_team_fields_are_added_in_english_by_first_name(): void
    {
        ContentBlock::create(['key' => 'team', 'locale' => 'en', 'value' => [['name' => 'Maria F.', 'role' => 'Mine'], ['name' => 'Vladimir T.', 'role' => 'Mine', 'g_role' => 'Already there']]]);
        GAudit3_2026::apply();
        $team = ContentBlock::where('key', 'team')->where('locale', 'en')->first()->value;
        $this->assertSame(config('creatium_en.team.2.g_role'), $team[0]['g_role']);
        $this->assertSame('Already there', $team[1]['g_role']);
        $this->assertSame(config('creatium_en.team.1.g_skills'), $team[1]['g_skills']);
        $this->assertSame('Mine', $team[0]['role']);
    }
}
