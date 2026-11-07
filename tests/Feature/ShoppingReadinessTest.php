<?php

namespace Tests\Feature;

use App\Mail\SiteCheckReportMail;
use App\Models\Project;
use App\Models\Role;
use App\Models\User;
use App\Support\SiteCheck\HostResolver;
use App\Support\SiteCheck\ShoppingReadiness;
use App\Support\SiteCheck\SiteCrawler;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ShoppingReadinessTest extends TestCase
{
    use RefreshDatabase;

    private function html(string $title, string $body, string $head = ''): string
    {
        return '<!doctype html><html><head><meta name="viewport" content="width=device-width"><title>' . $title . '</title>'
            . '<meta name="description" content="Описание на страницата, което е достатъчно дълго, за да е смислено за хората в Google.">' . $head . '</head>'
            . '<body><h1>' . $title . '</h1>' . $body . '<p>' . str_repeat('дума ', 250) . '</p></body></html>';
    }

    private function productJson(array $overrides = []): string
    {
        return '<script type="application/ld+json">' . json_encode(array_merge([
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => 'Купичка за куче',
            'gtin13' => '3800000000001',
            'image' => [['@type' => 'ImageObject', 'url' => 'https://shop.bg/img/kupichka.jpg', 'width' => 800, 'height' => 800]],
            'offers' => ['@type' => 'Offer', 'price' => '24.90', 'priceCurrency' => 'EUR', 'availability' => 'https://schema.org/InStock'],
        ], $overrides), JSON_UNESCAPED_UNICODE) . '</script>';
    }

    private function fakeSite(string $home, array $pages): void
    {
        $this->withoutDefer();
        config(['services.pagespeed.key' => null]);
        $this->app->instance(HostResolver::class, new class extends HostResolver
        {
            public function publicIp(string $host): ?string
            {
                return '93.184.216.34';
            }
        });

        Http::fake(function (Request $request) use ($home, $pages) {
            $html = ['Content-Type' => 'text/html; charset=utf-8'];
            $url = $request->url();
            if ($url === 'https://shop.bg/') {
                return Http::response($home, 200, $html);
            }
            if ($url === 'http://shop.bg/') {
                return Http::response('', 301, ['Location' => 'https://shop.bg/']);
            }
            if (isset($pages[$url])) {
                return Http::response($pages[$url], 200, $html);
            }

            return Http::response('', 404);
        });
    }

    private function shop(string $productHead): void
    {
        $home = $this->html('Онлайн магазин за домашни любимци', '
            <a href="/produkt/kupichka">Купичка</a> <a href="/kontakti">Контакти</a> <a href="/dostavka">Доставка</a>
            <a href="/vrashtane">Връщане и рекламации</a> <a href="/obshti-usloviya">Общи условия</a>
            <footer>Петс Трейд ЕООД, ЕИК 123456789</footer>');
        $this->fakeSite($home, [
            'https://shop.bg/produkt/kupichka' => $this->html('Купичка за куче', '<button class="add_to_cart_button">Добави в количката</button>', $productHead),
            'https://shop.bg/kontakti' => $this->html('Контакти', '<a href="tel:+359888123456">0888 123 456</a> <a href="mailto:shop@shop.bg">shop@shop.bg</a>'),
            'https://shop.bg/dostavka' => $this->html('Доставка', 'Доставяме с куриер.'),
            'https://shop.bg/vrashtane' => $this->html('Връщане', 'Връщане до 14 дни.'),
            'https://shop.bg/obshti-usloviya' => $this->html('Общи условия', 'Плащане с карта или наложен платеж.'),
        ]);
    }

    private function checks(?array $shopping): array
    {
        return collect($shopping['checks'])->pluck('status', 'key')->all();
    }

    public function test_a_complete_shop_passes_every_check(): void
    {
        $this->shop($this->productJson());

        $result = app(SiteCrawler::class)->crawl('https://shop.bg/', 50, 60);
        $shopping = ShoppingReadiness::evaluate($result['shop'], true);

        $this->assertNotNull($shopping);
        $this->assertSame(1, $shopping['products']);
        $this->assertSame(9, $shopping['total']);
        $this->assertSame(9, $shopping['ok'], json_encode($shopping['checks']));
        $this->assertSame(['contacts', 'company', 'shipping', 'returns', 'terms', 'https', 'product_data', 'identifiers', 'images'], array_column($shopping['checks'], 'key'));
    }

    public function test_missing_policies_and_product_data_are_reported(): void
    {
        $this->fakeSite(
            $this->html('Магазин', '<a href="/produkt/x">Продукт</a>'),
            ['https://shop.bg/produkt/x' => $this->html('Продукт', '', $this->productJson([
                'gtin13' => null, 'image' => 'https://shop.bg/img/small.jpg',
                'offers' => ['@type' => 'Offer', 'price' => '10', 'priceCurrency' => 'EUR'],
            ]))],
        );

        $result = app(SiteCrawler::class)->crawl('https://shop.bg/', 50, 60);
        $shopping = ShoppingReadiness::evaluate($result['shop'], true);
        $checks = $this->checks($shopping);

        $this->assertSame('missing', $checks['contacts']);
        $this->assertSame('missing', $checks['company']);
        $this->assertSame('missing', $checks['shipping']);
        $this->assertSame('missing', $checks['returns']);
        $this->assertSame('missing', $checks['terms']);
        $this->assertSame('ok', $checks['https']);
        $this->assertSame('missing', $checks['product_data']); // няма наличност
        $this->assertSame('missing', $checks['identifiers']);
        // Размерът на снимката не е известен: проверката се пропуска.
        $this->assertArrayNotHasKey('images', $checks);
    }

    public function test_small_product_images_are_flagged_from_the_img_tag(): void
    {
        // Размерите са в <img>, не в Product данните.
        $this->fakeSite(
            $this->html('Магазин', '<a href="/p">P</a>'),
            ['https://shop.bg/p' => $this->html('Продукт', '<img src="/img/kupichka.jpg" width="300" height="300" alt="x">', $this->productJson(['image' => 'https://shop.bg/img/kupichka.jpg']))],
        );

        $shopping = ShoppingReadiness::evaluate(app(SiteCrawler::class)->crawl('https://shop.bg/', 50, 60)['shop'], true);

        $this->assertSame('missing', $this->checks($shopping)['images']);
    }

    public function test_cart_markers_alone_make_a_site_a_shop(): void
    {
        $this->fakeSite($this->html('Магазин', '<a class="add-to-cart" href="/x">Купи</a>'), []);

        $shopping = ShoppingReadiness::evaluate(app(SiteCrawler::class)->crawl('https://shop.bg/', 50, 60)['shop'], true);

        $this->assertNotNull($shopping);
        $this->assertSame(0, $shopping['products']);
        $this->assertSame('missing', $this->checks($shopping)['product_data']);
    }

    public function test_a_site_that_is_not_a_shop_gets_no_checklist(): void
    {
        $this->fakeSite($this->html('Фирма', '<a href="/kontakti">Контакти</a> <p>Услуги за бизнеса, доставка на документи.</p>'), [
            'https://shop.bg/kontakti' => $this->html('Контакти', 'Телефон 0888 123 456'),
        ]);

        $result = app(SiteCrawler::class)->crawl('https://shop.bg/', 50, 60);

        $this->assertNull(ShoppingReadiness::evaluate($result['shop'], true));
    }

    public function test_shop_report_is_saved_emailed_and_shown_in_the_admin_without_changing_the_score(): void
    {
        Mail::fake();
        $this->shop($this->productJson(['gtin13' => null]));

        $this->post('/proverka-na-sait', ['domain' => 'shop.bg', 'email' => 'ivan@shop.bg', 'consent' => '1'])->assertRedirect();

        Mail::assertSent(SiteCheckReportMail::class, function ($mail) {
            $this->assertNotNull($mail->report['shopping']);
            $html = $mail->render();
            $this->assertStringContainsString('Готовност за Google Shopping', $html);
            $this->assertStringContainsString('Страница за доставка', $html);
            $this->assertStringContainsString('GTIN или MPN на продуктите', $html);
            $this->assertStringContainsString('Липсва GTIN или MPN на 1 от 1 продукта.', $html);
            $this->assertStringContainsString('/uslugi/podgotovka-za-google-shopping', $html);

            return true;
        });

        $lead = Project::firstOrFail();
        $this->assertSame(8, $lead->site_check_report['shopping']['ok']);
        $summary = $lead->siteCheckSummary();
        $this->assertSame(['GTIN или MPN на продуктите'], $summary['shopping']['missing']);

        $this->seed(RoleSeeder::class);
        $user = User::factory()->create();
        $user->roles()->attach(Role::where('slug', 'developer')->value('id'));
        $this->actingAs($user)->get('/admin/proverki')->assertOk()
            ->assertSee('Готовност за Google Shopping: 8 от 9 наред')
            ->assertSee('GTIN или MPN на продуктите');
    }

    public function test_english_report_uses_english_texts(): void
    {
        Mail::fake();
        $this->shop($this->productJson());
        $this->post('/en/website-check', ['domain' => 'shop.bg', 'email' => 'ivan@shop.bg', 'consent' => '1'])->assertRedirect();

        Mail::assertSent(SiteCheckReportMail::class, function ($mail) {
            $html = $mail->render();
            $this->assertStringContainsString('Google Shopping readiness', $html);
            $this->assertStringContainsString('Shipping page', $html);
            $this->assertStringContainsString('/en/services/google-shopping-readiness', $html);
            $this->assertStringNotContainsString('Готовност за Google Shopping', $html);

            return true;
        });
    }

    public function test_a_non_shop_report_has_no_shopping_section(): void
    {
        Mail::fake();
        $this->fakeSite($this->html('Фирма', '<a href="/kontakti">Контакти</a>'), ['https://shop.bg/kontakti' => $this->html('Контакти', 'Здравейте')]);
        $this->post('/proverka-na-sait', ['domain' => 'shop.bg', 'email' => 'ana@shop.bg', 'consent' => '1'])->assertRedirect();
        Mail::assertSent(SiteCheckReportMail::class, function ($mail) {
            $this->assertNull($mail->report['shopping']);
            $this->assertStringNotContainsString('Google Shopping', $mail->render());

            return true;
        });
    }
}
