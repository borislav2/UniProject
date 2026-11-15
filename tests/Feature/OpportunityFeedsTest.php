<?php

namespace Tests\Feature;

use App\Models\Opportunity;
use App\Support\Opportunities\Collector;
use App\Support\Opportunities\ContractsFinderFeed;
use App\Support\Opportunities\FindATenderFeed;
use App\Support\Opportunities\Keywords;
use App\Support\Opportunities\OpportunityScore;
use App\Support\Opportunities\Privacy;
use App\Support\Opportunities\TedFeed;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Събиране на обществени поръчки: парсерите срещу подрязани истински отговори (tests/Fixtures/opportunities, взети на 15.11.2026 от
 * TED, Contracts Finder и Find a Tender), оценката, повторенията, изтеклите срокове, паузата и ключалката, и грешките.
 */
class OpportunityFeedsTest extends TestCase
{
    use RefreshDatabase;

    private function fixture(string $name): string
    {
        return file_get_contents(base_path('tests/Fixtures/opportunities/' . $name . '.json'));
    }

    /** Сряда 18.11.2026 не е нужна: фиксираме деня на снимките. */
    private function now(string $when = '2026-11-15 12:00:00'): void
    {
        Carbon::setTestNow(Carbon::parse($when, 'UTC'));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function fakeAll(array $override = []): void
    {
        Http::fake($override + [
            'api.ted.europa.eu/*' => Http::response($this->fixture('ted'), 200, ['Content-Type' => 'application/json']),
            'www.contractsfinder.service.gov.uk/*' => Http::response($this->fixture('contracts-finder'), 200, ['Content-Type' => 'application/json']),
            'www.find-tender.service.gov.uk/*' => Http::response($this->fixture('find-a-tender'), 200, ['Content-Type' => 'application/json']),
        ]);
    }

    // ---- Парсери ----

    public function test_ted_parser_reads_the_real_notices(): void
    {
        $this->now();
        $this->fakeAll();

        $result = app(TedFeed::class)->fetch(now()->subDays(21), 2);
        $this->assertSame(10, $result['returned']);
        $byId = collect($result['records'])->keyBy('external_id');

        $bg = $byId['763766-2026'];
        $this->assertSame('BG', $bg['country']);
        $this->assertSame('bg', $bg['language']);
        $this->assertSame(240000.0, $bg['value_max']);
        $this->assertSame('EUR', $bg['currency']);
        $this->assertContains('72413000', $bg['cpv']);
        $this->assertSame('https://ted.europa.eu/en/notice/-/detail/763766-2026', $bg['url']);
        // Датата без час е до края на деня в зоната на купувача: 2026-12-02 в +02:00 е 21:59:59 UTC.
        $this->assertSame('2026-12-02 21:59:59', $bg['deadline_at']->format('Y-m-d H:i:s'));

        $ie = $byId['755937-2026'];
        $this->assertSame('IE', $ie['country']);
        $this->assertSame('en', $ie['language']);
        $this->assertStringContainsString('Heritage Ireland Website', $ie['title']);
        $this->assertSame(292500.0, $ie['value_max']);
        // Срокът е най-ранният, който още не е минал (часът от полето deadline, не датата на лота).
        $this->assertSame('2026-11-19 14:00:00', $ie['deadline_at']->format('Y-m-d H:i:s'));

        // Няколко лота без обща стойност: сборът по лотове; NOK и срок само като дата.
        $fi = $byId['749863-2026'];
        $this->assertSame(900000.0, $fi['value_max']);
        $this->assertSame('fi', $fi['language']);

        $no = $byId['762336-2026'];
        $this->assertSame('NO', $no['country']);
        $this->assertNull($no['value_max']);

        $de = $byId['738840-2026'];
        $this->assertSame('DE', $de['country']);
        $this->assertSame('de', $de['language']);
        $this->assertNotNull($de['deadline_at']);
    }

    public function test_ted_request_asks_only_for_our_cpv_codes_open_competition_notices_and_identifies_itself(): void
    {
        $this->now();
        $this->fakeAll();
        app(TedFeed::class)->fetch(now()->subDays(14), 2);

        Http::assertSentCount(1);
        Http::assertSent(function (Request $request) {
            $body = $request->data();

            return $request->method() === 'POST'
                && $request->url() === 'https://api.ted.europa.eu/v3/notices/search'
                && $request->hasHeader('User-Agent', 'CreatiumLab-OpportunityFeed/1.0')
                && str_contains($body['query'], 'classification-cpv IN (72413000 72212224 72420000')
                && ! str_contains($body['query'], '72000000')
                && str_contains($body['query'], 'publication-date>=20261101')
                && str_contains($body['query'], 'notice-type IN (cn-standard cn-social cn-desg pin-cfc-standard pin-cfc-social)')
                && str_contains($body['query'], 'deadline-receipt-tender-date-lot>=20261115')
                && $body['limit'] === 250 && $body['page'] === 1
                && in_array('publication-number', $body['fields'], true)
                && ! in_array('links', $body['fields'], true);
        });
    }

    public function test_ted_pages_until_a_short_page_and_stops_at_the_page_limit(): void
    {
        $this->now();
        $notice = json_decode($this->fixture('ted'), true)['notices'][0];
        $full = array_fill(0, 250, $notice);
        Http::fake(['api.ted.europa.eu/*' => Http::sequence()
            ->push(['notices' => $full], 200)->push(['notices' => $full], 200)->push(['notices' => $full], 200)]);

        $result = app(TedFeed::class)->fetch(now()->subDays(21), 2);
        Http::assertSentCount(2);
        $this->assertSame(500, $result['returned']);
        $this->assertCount(1, $result['records']);
    }

    public function test_contracts_finder_parser_reads_ocds_releases_and_keeps_no_contact_data(): void
    {
        $this->now('2026-09-20 10:00:00');
        $this->fakeAll();

        $result = app(ContractsFinderFeed::class)->fetch(now()->subDays(21), 4);
        $this->assertSame(3, $result['returned']);
        $byTitle = collect($result['records'])->keyBy('title');

        $web = $byTitle->first(fn ($r) => str_contains($r['title'], 'Website Development Services'));
        $this->assertSame('GB', $web['country']);
        $this->assertSame('en', $web['language']);
        $this->assertSame(25000, (int) $web['value_max']);
        $this->assertSame('GBP', $web['currency']);
        $this->assertContains('72413000', $web['cpv']);
        $this->assertStringStartsWith('https://www.contractsfinder.service.gov.uk/Notice/', $web['url']);
        $this->assertSame('2026-09-29 11:00:00', $web['deadline_at']->format('Y-m-d H:i:s'));
        $this->assertSame('DN Colleges Group', $web['buyer']);

        $encoded = json_encode($result['records']);
        foreach (['example.org', 'Test Person', 'contactPoint', 'procurement@'] as $personal) {
            $this->assertStringNotContainsString($personal, $encoded);
        }
        Http::assertSent(fn (Request $r) => str_contains($r->url(), 'stages=tender') && str_contains($r->url(), 'limit=100') && str_contains($r->url(), 'publishedFrom=2026-08-30'));
    }

    public function test_contracts_finder_follows_the_cursor_link_and_keeps_the_newest_amendment(): void
    {
        $this->now('2026-09-20 10:00:00');
        $release = json_decode($this->fixture('contracts-finder'), true)['releases'][2];
        $old = $release;
        $old['date'] = '2026-09-01T10:00:00Z';
        $old['tender']['title'] .= ' (old)';
        $new = $release;
        $new['date'] = '2026-09-10T10:00:00Z';
        Http::fake(['www.contractsfinder.service.gov.uk/*' => Http::sequence()
            ->push(['releases' => [$new], 'links' => ['next' => 'https://www.contractsfinder.service.gov.uk/Published/Notices/OCDS/Search?cursor=abc']], 200)
            ->push(['releases' => [$old], 'links' => []], 200)]);

        $result = app(ContractsFinderFeed::class)->fetch(now()->subDays(21), 4);
        Http::assertSentCount(2);
        Http::assertSent(fn (Request $r) => str_contains($r->url(), 'cursor=abc'));
        $this->assertSame(2, $result['returned']);
        $this->assertCount(1, $result['records']);
        $this->assertStringNotContainsString('(old)', $result['records'][0]['title']);
    }

    public function test_find_a_tender_parser_survives_the_invalid_infinity_value_in_the_json(): void
    {
        $this->now('2026-11-10 10:00:00');
        $this->assertStringContainsString('Infinity', $this->fixture('find-a-tender'));
        $this->assertNull(json_decode($this->fixture('find-a-tender'), true), 'the real response is not valid JSON');
        $this->fakeAll();

        $result = app(FindATenderFeed::class)->fetch(now()->subDays(21), 4);
        $this->assertSame(4, $result['returned']);
        $byId = collect($result['records'])->keyBy('title');

        $media = $byId->first(fn ($r) => str_contains($r['title'], 'Thistle'));
        $this->assertSame('GB', $media['country']);
        $this->assertContains('79341000', $media['cpv']);
        $this->assertSame(200000, (int) $media['value_max']);
        $this->assertSame('https://www.find-tender.service.gov.uk/Notice/102961-2026', $media['url']);
        $this->assertSame('VisitScotland', $media['buyer']);
        $this->assertStringContainsString('find-tender.service.gov.uk/Notice/', $media['url']);

        $encoded = json_encode($result['records']);
        foreach (['example.org', 'Test Person', 'Another Person', 'parties'] as $personal) {
            $this->assertStringNotContainsString($personal, $encoded);
        }
        Http::assertSent(fn (Request $r) => str_contains($r->url(), 'ocdsReleasePackages') && str_contains($r->url(), 'stages=tender') && str_contains($r->url(), 'updatedFrom=2026-10-20T10%3A00%3A00'));
    }

    // ---- Ключови думи ----

    public function test_keywords_match_our_services_in_english_bulgarian_german_and_dutch(): void
    {
        $hit = fn (string $title, ?string $text = null) => Keywords::match($title, $text);

        $this->assertNotEmpty($hit('Design and build of a new website')['strong_title']);
        $this->assertNotEmpty($hit('Изработка на уебсайт за общината')['strong_title']);
        $this->assertNotEmpty($hit('Erneuerung des Internetauftritts')['strong_title']);
        $this->assertNotEmpty($hit('Neue Webseiten für die Stadt')['strong_title']);
        $this->assertNotEmpty($hit('Websitebeheer en onderhoud')['strong_title']);
        $this->assertNotEmpty($hit('Zoekmachine optimalisatie voor de gemeente')['strong_title']);
        $this->assertNotEmpty($hit('Дигитален маркетинг и социални медии')['strong_title']);
        $this->assertNotEmpty($hit('Social media management')['strong_title']);
        $this->assertNotEmpty($hit('Branding and visual identity')['strong_title']);
        $this->assertNotEmpty($hit('Supply of paper', 'The supplier will also provide search engine optimisation for the site')['strong_text']);

        // SEO е съкращение: само главни букви и само като дума.
        $this->assertNotEmpty($hit('SEO services')['strong_title']);
        $this->assertEmpty($hit('Seoul office furniture')['strong_title']);
        $this->assertEmpty($hit('seoul')['strong_title']);

        // Общите думи не са силно съвпадение (маркетинг е слабо, дизайн изобщо не е: хваща архитектурен дизайн).
        $this->assertSame([], $hit('Town marketing and promotion')['strong_title']);
        $this->assertNotEmpty($hit('Town marketing and promotion')['weak_title']);
        $this->assertEmpty($hit('Supply of paper', 'marketing and communications')['weak_title']);
        $this->assertSame([], $hit('Architect led multidisciplinary design team')['strong_title']);
        $this->assertSame([], $hit('Architect led multidisciplinary design team')['weak_title']);
        // „Сайт“ в описание е навсякъде („вижте на сайта“): брои се само до дума за работа.
        $this->assertSame([], $hit('Landscaping works', 'Documents are available on our website, see the website for details')['strong_text']);
        $this->assertNotEmpty($hit('Consultant', 'The supplier will redesign the council website and keep it up to date')['strong_text']);
        $this->assertNotEmpty($hit('Beratung', 'Gegenstand ist die Erstellung einer neuen Webseite für die Stadt')['strong_text']);
        $this->assertNotEmpty($hit('Консултант', 'Предмет е изработка на нов уебсайт на общината')['strong_text']);

        // Не са наши.
        $this->assertSame([], $hit('Technology disconnections and street lighting diversions', 'Roads, M25, A13')['strong_title']);
        $this->assertSame([], $hit('Laser tunable continuous wave', 'laboratory equipment')['strong_title']);
    }

    // ---- Оценка ----

    public function test_score_prefers_our_markets_a_realistic_value_and_enough_time(): void
    {
        $this->now();
        $base = ['title' => 'New website', 'text' => '', 'cpv' => ['72413000'], 'country' => 'BG', 'language' => 'bg', 'value_max' => 60000, 'currency' => 'EUR', 'deadline_at' => now()->addDays(20), 'sme' => true];
        $best = OpportunityScore::forTender($base);
        $this->assertGreaterThanOrEqual(95, $best['score']);
        $this->assertSame('web', $best['topic']);
        $this->assertNotEmpty(array_filter($best['reasons'], fn ($r) => str_contains($r['label'], 'CPV 72413000')));

        $score = fn (array $changes) => OpportunityScore::forTender($changes + $base)['score'];
        $this->assertGreaterThan($score(['country' => 'DE']), $score(['country' => 'IE']));
        $this->assertGreaterThan($score(['country' => 'FR']), $score(['country' => 'DE']));
        $this->assertGreaterThan($score(['country' => 'US']), $score(['country' => 'FR']));
        $this->assertGreaterThan($score(['language' => 'fi']), $score(['language' => 'en']));
        // Рамково споразумение за милиони е най-ниско, обявената подходяща стойност най-високо.
        $this->assertGreaterThan($score(['value_max' => 5000000]), $score(['value_max' => 60000]));
        $this->assertGreaterThan($score(['value_max' => 300000]), $score(['value_max' => 60000]));
        $this->assertGreaterThan($score(['value_max' => 500]), $score(['value_max' => 60000]));
        $this->assertGreaterThan($score(['value_max' => null]), $score(['value_max' => 60000]));
        // Валутите се сравняват в евро.
        $this->assertSame($score(['value_max' => 60000, 'currency' => 'EUR']), $score(['value_max' => 52000, 'currency' => 'GBP']));
        // Много кратък срок е слаб, пет дни по-добре, три седмици най-добре.
        $this->assertLessThan($score(['deadline_at' => now()->addDays(5)]), $score(['deadline_at' => now()->addDays(1)]));
        $this->assertLessThan($score(['deadline_at' => now()->addDays(20)]), $score(['deadline_at' => now()->addDays(5)]));
        // Само ключова дума в заглавието е по-слабо от CPV код.
        $this->assertLessThan($best['score'], $score(['cpv' => [], 'title' => 'Website redesign']));
    }

    public function test_expired_notices_are_not_scored_and_generic_words_are_weak_candidates(): void
    {
        $this->now();
        $this->assertNull(OpportunityScore::forTender(['title' => 'New website', 'cpv' => ['72413000'], 'deadline_at' => now()->subHour()]));

        $none = OpportunityScore::forTender(['title' => 'Street lighting', 'text' => 'M25 works', 'cpv' => ['34928500'], 'deadline_at' => now()->addDays(20)]);
        $this->assertFalse($none['candidate']);
        $generic = OpportunityScore::forTender(['title' => 'Town marketing and promotions', 'cpv' => [], 'country' => 'GB', 'language' => 'en', 'deadline_at' => now()->addDays(20)]);
        $this->assertTrue($generic['candidate']);
        $this->assertLessThan(OpportunityScore::forTender(['title' => 'Website redesign', 'cpv' => [], 'country' => 'GB', 'language' => 'en', 'deadline_at' => now()->addDays(20)])['score'], $generic['score']);
        $this->assertFalse(OpportunityScore::forTender(['title' => 'Architect led design team', 'cpv' => [], 'deadline_at' => now()->addDays(20)])['candidate']);
        $this->assertTrue(OpportunityScore::forTender(['title' => 'Town marketing', 'text' => 'including social media management', 'cpv' => [], 'deadline_at' => now()->addDays(20)])['candidate']);
    }

    public function test_scrub_removes_emails_and_phone_numbers_but_not_dates_or_cpv_codes(): void
    {
        $text = 'Contact jane.doe@council.gov.uk or +44 141 287 6410 or 0141 287 6410 by 2026-11-23 about CPV 72413000 and 79340000.';
        $clean = Privacy::scrub($text);
        $this->assertStringNotContainsString('jane.doe', $clean);
        $this->assertStringNotContainsString('287 6410', $clean);
        $this->assertStringContainsString('2026-11-23', $clean);
        $this->assertStringContainsString('72413000', $clean);
        $this->assertStringContainsString('79340000', $clean);
        $this->assertNull(Privacy::scrub('   '));
        $this->assertLessThanOrEqual(30, mb_strlen(Privacy::scrub(str_repeat('word ', 100), 30)));
    }

    // ---- Събиране ----

    public function test_collector_stores_matching_open_tenders_from_all_three_sources_and_skips_the_rest(): void
    {
        $this->now('2026-11-10 10:00:00');
        $this->fakeAll();
        $collector = app(Collector::class);

        $ted = $collector->run(app(TedFeed::class));
        $this->assertNull($ted['error']);
        $this->assertSame(10, $ted['returned']);
        $this->assertGreaterThan(0, $ted['new']);
        $this->assertSame($ted['matched'], $ted['new']);
        $this->assertSame(10, $ted['matched'] + $ted['rejected'] + $ted['expired']);

        $cf = $collector->run(app(ContractsFinderFeed::class));
        $this->assertSame(3, $cf['returned']);
        // Улично осветление не е наше; сайтът на колежа е със срок 29.09 (изтекъл на 10.11), кампанията е със срок 13.11.
        $this->assertSame(['new' => 1, 'expired' => 1, 'rejected' => 1], ['new' => $cf['new'], 'expired' => $cf['expired'], 'rejected' => $cf['rejected']]);

        $fts = $collector->run(app(FindATenderFeed::class));
        $this->assertSame(4, $fts['returned']);

        $this->assertSame('tender', Opportunity::first()->kind);
        $this->assertSame(0, Opportunity::where('status', '!=', 'new')->count());
        $this->assertSame(0, Opportunity::where('score', '<', (int) config('creatium.opportunities.min_score'))->count());

        $bg = Opportunity::where('external_id', '763766-2026')->firstOrFail();
        $this->assertSame('ted', $bg->source);
        $this->assertSame('BG', $bg->country);
        $this->assertGreaterThanOrEqual(75, $bg->score);
        $this->assertSame('web', $bg->raw['topic']);
        $this->assertStringContainsString('Поръчка за уебсайт и уеб разработка', $bg->summaryLine());
        $this->assertStringContainsString('България', $bg->summaryLine());
        $this->assertStringContainsString('€240 000', $bg->summaryLine());
        $this->assertStringContainsString('срок 02.12.2026', $bg->summaryLine());

        $state = Collector::state();
        $this->assertSame(10, $state['ted']['returned']);
        $this->assertNull($state['ted']['last_error']);
        $this->assertNotEmpty($state['find-a-tender']['last_ok_at']);
    }

    public function test_stored_data_has_no_personal_contact_details(): void
    {
        $this->now('2026-11-10 10:00:00');
        $this->fakeAll();
        $collector = app(Collector::class);
        foreach ([app(ContractsFinderFeed::class), app(FindATenderFeed::class), app(TedFeed::class)] as $feed) {
            $collector->run($feed);
        }

        $this->assertGreaterThan(0, Opportunity::where('source', 'find-a-tender')->count());
        $dump = Opportunity::all()->map(fn ($o) => json_encode($o->toArray(), JSON_UNESCAPED_UNICODE))->implode("\n");
        foreach (['example.org', 'Test Person', 'Another Person', 'contactPoint', '@'] as $personal) {
            $this->assertStringNotContainsString($personal, $dump);
        }
    }

    public function test_refetch_updates_deadline_and_value_but_never_touches_status_notes_or_owner(): void
    {
        $this->now();
        $this->fakeAll();
        $collector = app(Collector::class);
        $collector->run(app(TedFeed::class));
        $count = Opportunity::count();

        $o = Opportunity::where('external_id', '755937-2026')->firstOrFail();
        $user = \App\Models\User::factory()->create();
        $o->update(['status' => 'preparing', 'note' => 'Говорих с възложителя', 'assigned_to' => $user->id]);

        $changed = json_decode($this->fixture('ted'), true);
        foreach ($changed['notices'] as &$notice) {
            if ($notice['publication-number'] === '755937-2026') {
                $notice['estimated-value-proc'] = '310000';
                $notice['deadline'] = ['2026-12-01T12:00:00Z'];
            }
        }
        unset($notice);
        Http::swap(new \Illuminate\Http\Client\Factory);
        Http::fake(['api.ted.europa.eu/*' => Http::response($changed, 200)]);
        $result = $collector->run(app(TedFeed::class));

        $this->assertSame(0, $result['new']);
        $this->assertGreaterThanOrEqual(1, $result['updated']);
        $this->assertSame($count, Opportunity::count());
        $o->refresh();
        $this->assertSame(310000.0, $o->value_max);
        $this->assertSame('2026-12-01 12:00:00', $o->deadline_at->format('Y-m-d H:i:s'));
        $this->assertSame('preparing', $o->status);
        $this->assertSame('Говорих с възложителя', $o->note);
        $this->assertSame($user->id, $o->assigned_to);
    }

    public function test_notices_already_expired_at_fetch_time_are_not_stored(): void
    {
        $this->now('2026-12-20 10:00:00');
        $this->fakeAll();
        $result = app(Collector::class)->run(app(TedFeed::class));

        $this->assertSame(10, $result['returned']);
        $this->assertGreaterThanOrEqual(8, $result['expired']);
        $this->assertSame(0, Opportunity::where('deadline_at', '<', now())->count());
    }

    public function test_expired_new_and_interesting_become_skipped_but_work_in_progress_is_left_alone(): void
    {
        $this->now();
        $this->fakeAll();
        app(Collector::class)->run(app(TedFeed::class));
        $ids = Opportunity::whereNotNull('deadline_at')->orderBy('id')->pluck('id');
        $this->assertGreaterThanOrEqual(4, $ids->count());
        [$new, $interesting, $preparing, $submitted] = $ids->all();
        Opportunity::whereKey($new)->update(['status' => 'new', 'deadline_at' => now()->subDay()]);
        Opportunity::whereKey($interesting)->update(['status' => 'interesting', 'deadline_at' => now()->subDay()]);
        Opportunity::whereKey($preparing)->update(['status' => 'preparing', 'deadline_at' => now()->subDay()]);
        Opportunity::whereKey($submitted)->update(['status' => 'submitted', 'deadline_at' => now()->subDay()]);

        $this->assertSame(2, Collector::expireOld());

        foreach ([$new, $interesting] as $id) {
            $o = Opportunity::find($id);
            $this->assertSame('skipped', $o->status);
            $this->assertSame('изтекъл срок', $o->skip_reason);
            $this->assertSame('status', $o->notes->first()->kind);
        }
        $this->assertSame('preparing', Opportunity::find($preparing)->status);
        $this->assertSame('submitted', Opportunity::find($submitted)->status);
        $this->assertSame(0, Collector::expireOld());
    }

    public function test_tick_does_one_source_per_call_and_each_source_at_most_once_per_interval(): void
    {
        config(['creatium.opportunities.enabled' => true]);
        $this->now();
        $this->fakeAll();
        $collector = app(Collector::class);

        $first = $collector->tick();
        $this->assertSame('ran', $first['status']);
        Http::assertSentCount(1);
        $ran = [$first['source']];
        $ran[] = $collector->tick()['source'];
        $ran[] = $collector->tick()['source'];
        $this->assertEqualsCanonicalizing(['ted', 'contracts-finder', 'find-a-tender'], $ran);
        Http::assertSentCount(3);

        // Нищо не е дошло време: нула нови заявки.
        $this->assertSame('wait', $collector->tick()['status']);
        $this->travel(5)->hours();
        $this->assertSame('wait', $collector->tick()['status']);
        Http::assertSentCount(3);

        // След 6 часа е ред пак, пак един източник на повикване.
        $this->travel(61)->minutes();
        $this->assertSame('ran', $collector->tick()['status']);
        Http::assertSentCount(4);
    }

    public function test_tick_does_nothing_when_switched_off_and_skips_a_source_that_is_locked(): void
    {
        $this->now();
        $this->fakeAll();
        $collector = app(Collector::class);

        config(['creatium.opportunities.enabled' => false]);
        $this->assertSame('off', $collector->tick()['status']);
        Http::assertNothingSent();

        config(['creatium.opportunities.enabled' => true]);
        $lock = Cache::lock('opportunities:ted', 300);
        $this->assertTrue($lock->get());
        $seen = [];
        for ($i = 0; $i < 3; $i++) {
            $seen[] = $collector->tick();
        }
        $this->assertNotContains('ted', array_column($seen, 'source'));
        Http::assertNotSent(fn (Request $r) => str_contains($r->url(), 'api.ted.europa.eu'));
        $lock->release();
    }

    public function test_one_failing_source_is_recorded_and_does_not_stop_the_others_or_a_page(): void
    {
        config(['creatium.opportunities.enabled' => true]);
        $this->now();
        $this->fakeAll(['api.ted.europa.eu/*' => Http::response(['message' => 'boom'], 500)]);
        $collector = app(Collector::class);

        $failed = $collector->run(app(TedFeed::class));
        $this->assertStringContainsString('500', $failed['error']);
        $ok = $collector->run(app(ContractsFinderFeed::class));
        $this->assertNull($ok['error']);

        $state = Collector::state();
        $this->assertStringContainsString('TED', $state['ted']['last_error']);
        $this->assertNull($state['contracts-finder']['last_error']);

        // След грешка повторният опит е най-рано след час, не при всяко посещение.
        $this->assertFalse(Collector::due('ted'));
        $this->travel(61)->minutes();
        $this->assertTrue(Collector::due('ted'));

        // Страница на сайта работи и когато всички източници са зле (събирането е след отговора).
        Http::swap(new \Illuminate\Http\Client\Factory);
        Http::fake(['*' => Http::response('nope', 503)]);
        \App\Models\OutreachSetting::put(Collector::STATE_KEY, []);
        Cache::forget('opportunities:poke');
        $this->get('/')->assertOk();
        $this->assertStringContainsString('503', json_encode(Collector::state()));
    }

    public function test_invalid_json_is_an_error_not_an_exception(): void
    {
        $this->now();
        $this->fakeAll(['www.contractsfinder.service.gov.uk/*' => Http::response('<html>maintenance</html>', 200)]);

        $result = app(Collector::class)->run(app(ContractsFinderFeed::class));
        $this->assertStringContainsString('не е валиден JSON', $result['error']);
        $this->assertSame(0, Opportunity::count());
    }
}
