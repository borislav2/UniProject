<?php

namespace App\Support\SiteCheck;

use DOMXPath;

/**
 * „Готовност за Google Shopping“ в безплатната проверка на сайт. Прилага се само когато сайтът прилича на онлайн магазин:
 * има Product данни (JSON-LD) или белези на количка (WooCommerce, Shopify, „Добави в количката“).
 *
 * Не прави нови заявки: SiteCrawler вика signals() за всяка страница, която така или иначе чете, а evaluate() събира
 * сигналите в списък „има / липсва“. Не влияе на общата оценка на проверката.
 * Какво проверява Google: Digital Community Day 2026 (Богдан Богомилов); изискването за снимки поне 500×500 px е от 31.01.2027.
 */
class ShoppingReadiness
{
    public const MIN_IMAGE_SIDE = 500;

    private const MAX_PRODUCTS_PER_PAGE = 5;

    private const CART = '/add[_-]to[_-]cart|woocommerce|cdn\.shopify\.com|shopify-section|добави в количката|добави в кошницата|в количката/iu';

    /** Ключ => шаблон за текста и адреса на връзките (кирилица и латиница, както са адресите на български сайтове). */
    private const LINKS = [
        'contact' => '/контакт|contact|kontakt/iu',
        'shipping' => '/достав|shipping|delivery|dostav/iu',
        'returns' => '/връщан|рекламац|return|refund|замян|vrasht|vrashtan|reklamac/iu',
        'terms' => '/условия|terms|плащан|payment|uslovi|plasht/iu',
    ];

    private const COMPANY = '/(ЕИК|БУЛСТАТ|EIK|BULSTAT)\D{0,12}\d{9,13}|\S+\s+(ЕООД|ООД|ЕАД|АД|ЕТ)(?![\p{L}])|\b(Ltd|LLC|OOD|EOOD)\b/u';

    /** Сигналите на една страница (малки стойности, нищо тежко не се пази). */
    public static function signals(DOMXPath $xpath, string $html, string $text, string $url): array
    {
        $links = array_fill_keys(array_keys(self::LINKS), false);
        $phone = $email = false;

        foreach ($xpath->query('//a') as $a) {
            $href = $a->getAttribute('href');
            $phone = $phone || stripos($href, 'tel:') === 0;
            $email = $email || stripos($href, 'mailto:') === 0;
            // За вътрешни връзки (и относителни) се гледа и адресът; външните се познават само по текста.
            $haystack = trim($a->textContent) . ' ' . (preg_match('#^[a-z][a-z0-9+.-]*:#i', $href) ? '' : $href);
            foreach (self::LINKS as $key => $pattern) {
                $links[$key] = $links[$key] || preg_match($pattern, $haystack) === 1;
            }
        }
        // Страницата сама по себе си: адресът ѝ е на доставка, връщане и т.н.
        $path = urldecode((string) parse_url($url, PHP_URL_PATH));
        foreach (self::LINKS as $key => $pattern) {
            $links[$key] = $links[$key] || preg_match($pattern, $path) === 1;
        }

        return [
            'cart' => preg_match(self::CART, $html) === 1,
            'products' => self::products($xpath, $url),
            'phone' => $phone || preg_match('/(?:\+|00)359[\s\d\-()]{8,}|\b0[2-9]\d{1,2}[\s\-]?\d{3}[\s\-]?\d{3,4}\b/', $text) === 1,
            'email' => $email || preg_match('/[\w.+-]+@[\w-]+\.[\w.-]+/u', $text) === 1,
            'company' => preg_match(self::COMPANY, $text) === 1,
            'links' => $links,
        ];
    }

    /**
     * Product данните от JSON-LD: има ли цена, валута, наличност, GTIN/MPN и размери на снимките.
     *
     * @return list<array{price: bool, currency: bool, availability: bool, id: bool, images: list<array{w: int, h: int}>}>
     */
    private static function products(DOMXPath $xpath, string $url): array
    {
        $found = [];
        foreach ($xpath->query('//script[@type="application/ld+json"]') as $script) {
            $data = json_decode(trim($script->textContent), true);
            if (is_array($data)) {
                self::collect($data, $found);
            }
        }

        // Размерите от <img width height>, ако снимката е една от тези в Product данните.
        $attrs = [];
        foreach ($xpath->query('//img[@width and @height]') as $img) {
            $w = self::number($img->getAttribute('width'));
            $h = self::number($img->getAttribute('height'));
            if ($w && $h) {
                foreach (['src', 'data-src'] as $name) {
                    if ($src = $img->getAttribute($name)) {
                        $attrs[self::imageKey($src)] = ['w' => $w, 'h' => $h];
                    }
                }
            }
        }

        $products = [];
        foreach (array_slice($found, 0, self::MAX_PRODUCTS_PER_PAGE) as $product) {
            $images = [];
            foreach (self::images($product['image'] ?? []) as $image) {
                if ($image['w'] && $image['h']) {
                    $images[] = ['w' => $image['w'], 'h' => $image['h']];
                } elseif ($image['url'] !== '' && isset($attrs[self::imageKey($image['url'])])) {
                    $images[] = $attrs[self::imageKey($image['url'])];
                }
            }
            $offer = self::offer($product['offers'] ?? []);
            $products[] = [
                'price' => $offer['price'],
                'currency' => $offer['currency'],
                'availability' => $offer['availability'],
                'id' => collect(['gtin', 'gtin8', 'gtin12', 'gtin13', 'gtin14', 'mpn'])->contains(fn ($k) => filled($product[$k] ?? null)),
                'images' => $images,
            ];
        }

        return $products;
    }

    /** Намира Product (и вариантите на ProductGroup) на всяко ниво, вкл. @graph. */
    private static function collect(array $node, array &$found): void
    {
        $type = $node['@type'] ?? null;
        $types = array_map('strval', is_array($type) ? $type : [$type]);
        if (in_array('Product', $types, true)) {
            $found[] = $node;
        } elseif (in_array('ProductGroup', $types, true)) {
            foreach ((array) ($node['hasVariant'] ?? []) as $variant) {
                if (is_array($variant)) {
                    $found[] = $variant + ['offers' => $node['offers'] ?? [], 'image' => $node['image'] ?? []];
                }
            }
            return;
        }
        foreach ($node as $key => $child) {
            if (is_array($child) && $key !== 'offers') {
                self::collect($child, $found);
            }
        }
    }

    /** @return array{price: bool, currency: bool, availability: bool} */
    private static function offer(mixed $offers): array
    {
        $offers = is_array($offers) && array_is_list($offers) ? $offers : [$offers];
        $best = ['price' => false, 'currency' => false, 'availability' => false];
        foreach ($offers as $offer) {
            if (! is_array($offer)) {
                continue;
            }
            $spec = (array) ($offer['priceSpecification'] ?? []);
            $spec = array_is_list($spec) ? (array) ($spec[0] ?? []) : $spec;
            $current = [
                'price' => filled($offer['price'] ?? null) || filled($offer['lowPrice'] ?? null) || filled($spec['price'] ?? null),
                'currency' => filled($offer['priceCurrency'] ?? null) || filled($spec['priceCurrency'] ?? null),
                'availability' => filled($offer['availability'] ?? null),
            ];
            if (array_sum($current) > array_sum($best)) {
                $best = $current;
            }
        }

        return $best;
    }

    /** @return list<array{url: string, w: ?int, h: ?int}> */
    private static function images(mixed $image): array
    {
        $image = is_array($image) && array_is_list($image) ? $image : [$image];
        $out = [];
        foreach ($image as $item) {
            if (is_string($item)) {
                $out[] = ['url' => $item, 'w' => null, 'h' => null];
            } elseif (is_array($item)) {
                $out[] = ['url' => (string) ($item['url'] ?? $item['contentUrl'] ?? ''), 'w' => self::number($item['width'] ?? null), 'h' => self::number($item['height'] ?? null)];
            }
        }

        return $out;
    }

    /** Число от "800", 800, "800px" или {"value": 800}; без число (или "100%") е null. */
    private static function number(mixed $value): ?int
    {
        if (is_array($value)) {
            $value = $value['value'] ?? null;
        }
        if (is_string($value) && str_contains($value, '%')) {
            return null;
        }

        if (is_string($value) && preg_match('/^\s*(\d+)\s*(px)?\s*$/i', $value, $m)) {
            $value = $m[1];
        }

        return is_numeric($value) && (int) round((float) $value) > 0 ? (int) round((float) $value) : null;
    }

    private static function imageKey(string $src): string
    {
        return strtolower(basename((string) parse_url($src, PHP_URL_PATH)));
    }

    /**
     * @param  list<array>  $pages  сигналите от signals() за всички обходени страници
     * @return ?array{products: int, ok: int, total: int, checks: list<array{key: string, status: string, found: int, total: int}>}  null, ако сайтът не прилича на магазин
     */
    public static function evaluate(array $pages, bool $https): ?array
    {
        $products = collect($pages)->flatMap(fn ($p) => $p['products'] ?? [])->values();
        $isShop = $products->isNotEmpty() || collect($pages)->contains(fn ($p) => ! empty($p['cart']));
        if (! $isShop) {
            return null;
        }

        $any = fn (string $key) => collect($pages)->contains(fn ($p) => ! empty($p[$key]));
        $link = fn (string $key) => collect($pages)->contains(fn ($p) => ! empty($p['links'][$key]));
        $status = fn (bool $ok) => $ok ? 'ok' : 'missing';
        $share = fn (string $key, ?callable $test = null) => [
            'found' => $products->filter($test ?? fn ($p) => $p[$key])->count(),
            'total' => $products->count(),
        ];

        $checks = [
            ['key' => 'contacts', 'status' => $status($link('contact') && ($any('phone') || $any('email'))), 'found' => 0, 'total' => 0],
            ['key' => 'company', 'status' => $status($any('company')), 'found' => 0, 'total' => 0],
            ['key' => 'shipping', 'status' => $status($link('shipping')), 'found' => 0, 'total' => 0],
            ['key' => 'returns', 'status' => $status($link('returns')), 'found' => 0, 'total' => 0],
            ['key' => 'terms', 'status' => $status($link('terms')), 'found' => 0, 'total' => 0],
            ['key' => 'https', 'status' => $status($https), 'found' => 0, 'total' => 0],
        ];

        $data = $share('', fn ($p) => $p['price'] && $p['currency'] && $p['availability']);
        $checks[] = ['key' => 'product_data', 'status' => $status($data['total'] > 0 && $data['found'] === $data['total'])] + $data;
        $ids = $share('id');
        $checks[] = ['key' => 'identifiers', 'status' => $status($ids['total'] > 0 && $ids['found'] === $ids['total'])] + $ids;

        // Снимките се оценяват само там, където размерите са известни; иначе проверката се пропуска.
        $sized = $products->filter(fn ($p) => $p['images'] !== []);
        if ($sized->isNotEmpty()) {
            $big = $sized->filter(fn ($p) => collect($p['images'])->contains(fn ($i) => min($i['w'], $i['h']) >= self::MIN_IMAGE_SIDE));
            $checks[] = ['key' => 'images', 'status' => $status($big->count() === $sized->count()), 'found' => $big->count(), 'total' => $sized->count()];
        }

        return [
            'products' => $products->count(),
            'ok' => collect($checks)->where('status', 'ok')->count(),
            'total' => count($checks),
            'checks' => $checks,
        ];
    }

    /** Заглавие и пояснение за една проверка, на текущия език. */
    public static function describe(array $check): array
    {
        $missing = $check['status'] !== 'ok';
        $count = ['found' => $check['found'] ?? 0, 'total' => $check['total'] ?? 0];

        $title = match ($check['key']) {
            'contacts' => __('Контакти: телефон или имейл и страница „Контакти“'),
            'company' => __('Данни за фирмата: ЕИК или име на фирмата'),
            'shipping' => __('Страница за доставка'),
            'returns' => __('Страница за връщане и рекламации'),
            'terms' => __('Общи условия и информация за плащането'),
            'https' => __('Сайтът работи по HTTPS'),
            'product_data' => __('Продуктови данни: цена, валута и наличност'),
            'identifiers' => __('GTIN или MPN на продуктите'),
            'images' => __('Снимки на продуктите: поне 500×500 px'),
            default => (string) $check['key'],
        };
        if (! $missing) {
            return ['title' => $title, 'detail' => ''];
        }

        $detail = match ($check['key']) {
            'contacts' => __('Google проверява сайта като купувач: трябват ясни контакти.'),
            'company' => __('Купувачът и Google трябва да виждат кой стои зад магазина.'),
            'shipping' => __('Не открихме връзка към страница за доставка.'),
            'returns' => __('Не открихме връзка към страница за връщане или рекламации.'),
            'terms' => __('Не открихме общи условия или информация за плащането.'),
            'https' => __('Без HTTPS Google може да не приеме магазина.'),
            'product_data' => $count['total'] === 0 ? __('Не открихме Product данни на продуктовите страници.') : __('Липсват данни на :n от :total продукта.', ['n' => $count['total'] - $count['found'], 'total' => $count['total']]),
            'identifiers' => $count['total'] === 0 ? __('Не открихме продукти с GTIN или MPN.') : __('Липсва GTIN или MPN на :n от :total продукта.', ['n' => $count['total'] - $count['found'], 'total' => $count['total']]),
            'images' => __('Под 500×500 px са снимките на :n от :total продукта.', ['n' => $count['total'] - $count['found'], 'total' => $count['total']]),
            default => '',
        };

        return ['title' => $title, 'detail' => $detail];
    }

    /** Кратко за админа: колко са наред и кои липсват. */
    public static function summary(?array $shopping): ?array
    {
        if (! $shopping || empty($shopping['checks'])) {
            return null;
        }

        return [
            'ok' => (int) $shopping['ok'],
            'total' => (int) $shopping['total'],
            'products' => (int) ($shopping['products'] ?? 0),
            'missing' => collect($shopping['checks'])->where('status', '!=', 'ok')->map(fn ($c) => self::describe($c)['title'])->values()->all(),
        ];
    }
}
