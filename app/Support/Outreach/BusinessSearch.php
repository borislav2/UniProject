<?php

namespace App\Support\Outreach;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Търси бизнеси по град и вид. С SERPER_API_KEY: в Google Maps през serper.dev (почти всички бизнеси, повечето със сайт).
 * Без ключ: в OpenStreetMap (Overpass API, безплатно, но малко бизнеси имат записан сайт).
 * И от двата източника се взимат само бизнеси със сайт.
 */
class BusinessSearch
{
    /** Център и радиус в км. Радиусът на Русе е малък, за да не хваща Гюргево. */
    public const CITIES = [
        'София' => [42.6977, 23.3219, 12],
        'Пловдив' => [42.1354, 24.7453, 8],
        'Варна' => [43.2141, 27.9147, 9],
        'Бургас' => [42.5048, 27.4626, 8],
        'Велико Търново' => [43.0757, 25.6172, 6],
        'Русе' => [43.8356, 25.9657, 5],
        'Стара Загора' => [42.4258, 25.6345, 6],
        'Плевен' => [43.4170, 24.6067, 6],
        'Благоевград' => [42.0209, 23.0943, 5],
        'Шумен' => [43.2706, 26.9361, 5],
    ];

    /** Вид бизнес => етикет, таговете в OpenStreetMap и думата за търсене в Google Maps. */
    public const CATEGORIES = [
        'restaurant' => ['label' => 'Ресторанти', 'tags' => ['amenity=restaurant'], 'query' => 'ресторант'],
        'cafe' => ['label' => 'Кафенета и барове', 'tags' => ['amenity=cafe', 'amenity=bar'], 'query' => 'кафе бар'],
        'hotel' => ['label' => 'Хотели и къщи за гости', 'tags' => ['tourism=hotel', 'tourism=guest_house'], 'query' => 'хотел'],
        'beauty' => ['label' => 'Козметични салони', 'tags' => ['shop=beauty'], 'query' => 'козметичен салон'],
        'hairdresser' => ['label' => 'Фризьори', 'tags' => ['shop=hairdresser'], 'query' => 'фризьорски салон'],
        'dentist' => ['label' => 'Зъболекари', 'tags' => ['amenity=dentist', 'healthcare=dentist'], 'query' => 'зъболекар'],
        'physiotherapy' => ['label' => 'Физиотерапия', 'tags' => ['healthcare=physiotherapist'], 'query' => 'физиотерапия'],
        'veterinary' => ['label' => 'Ветеринари', 'tags' => ['amenity=veterinary'], 'query' => 'ветеринарна клиника'],
        'car_repair' => ['label' => 'Автосервизи', 'tags' => ['shop=car_repair'], 'query' => 'автосервиз'],
        'fitness' => ['label' => 'Фитнес зали', 'tags' => ['leisure=fitness_centre'], 'query' => 'фитнес зала'],
        'builder' => ['label' => 'Строители и ремонти', 'tags' => ['craft=builder', 'office=construction_company', 'craft=roofer'], 'query' => 'строителна фирма'],
        'lawyer' => ['label' => 'Адвокати', 'tags' => ['office=lawyer'], 'query' => 'адвокат'],
        'accountant' => ['label' => 'Счетоводители', 'tags' => ['office=accountant'], 'query' => 'счетоводна кантора'],
    ];

    public const ENDPOINTS = [
        'https://overpass-api.de/api/interpreter',
        'https://overpass.kumi.systems/api/interpreter',
        'https://overpass.private.coffee/api/interpreter',
    ];

    private const LIMIT = 400;

    /** Страници от Google Maps на едно търсене (по около 20 бизнеса, всяка струва кредит в Serper). */
    private const SERPER_PAGES = 3;

    public static function usesGoogle(): bool
    {
        return filled(config('services.serper.key'));
    }

    /**
     * @return list<array{external_id: string, name: string, website: ?string, phone: ?string, email: ?string, city: ?string}>
     */
    public function find(string $city, string $category): array
    {
        if (self::usesGoogle()) {
            return $this->findOnGoogle($city, $category);
        }

        [$lat, $lng, $km] = self::CITIES[$city] ?? throw new RuntimeException("Непознат град: $city");
        $tags = self::CATEGORIES[$category]['tags'] ?? throw new RuntimeException("Непознат вид бизнес: $category");

        $around = 'around:' . ($km * 1000) . ",$lat,$lng";
        $clauses = implode('', array_map(function ($tag) use ($around) {
            [$key, $value] = explode('=', $tag);

            // Само бизнеси със записан сайт: без сайт няма откъде да се намери имейл.
            return "nwr[\"$key\"=\"$value\"][~\"^(website|contact:website|url)$\"~\".\"]($around);";
        }, $tags));
        $query = "[out:json][timeout:50];($clauses);out center tags " . self::LIMIT . ';';

        $elements = $this->query($query);

        $businesses = [];
        foreach ($elements as $element) {
            $t = $element['tags'] ?? [];
            $name = trim((string) ($t['name'] ?? $t['name:bg'] ?? ''));
            $website = self::website($t['website'] ?? $t['contact:website'] ?? $t['url'] ?? null);
            if ($name === '' || ! $website || ! isset($element['type'], $element['id'])) {
                continue;
            }
            $businesses[] = [
                'external_id' => 'osm:' . $element['type'] . '/' . $element['id'],
                'name' => mb_substr($name, 0, 255),
                'website' => $website,
                'phone' => self::first($t['phone'] ?? $t['contact:phone'] ?? $t['contact:mobile'] ?? null, 100),
                'email' => self::first($t['email'] ?? $t['contact:email'] ?? null, 255),
                'city' => self::first($t['addr:city'] ?? null, 255) ?: $city,
            ];
        }

        return $businesses;
    }

    /** Google Maps през serper.dev: „ресторант Пловдив“, до три страници резултати. */
    private function findOnGoogle(string $city, string $category): array
    {
        $query = (self::CATEGORIES[$category]['query'] ?? throw new RuntimeException("Непознат вид бизнес: $category")) . ' ' . $city;
        isset(self::CITIES[$city]) || throw new RuntimeException("Непознат град: $city");

        $businesses = [];
        for ($page = 1; $page <= self::SERPER_PAGES; $page++) {
            try {
                $response = Http::withHeaders(['X-API-KEY' => config('services.serper.key')])
                    ->acceptJson()
                    ->connectTimeout(10)
                    ->timeout(30)
                    ->post('https://google.serper.dev/places', ['q' => $query, 'gl' => 'bg', 'hl' => 'bg', 'page' => $page]);
            } catch (ConnectionException) {
                throw new RuntimeException('Google Maps (Serper) не отговаря в момента. Опитайте пак след малко.');
            }
            if (in_array($response->status(), [401, 403], true)) {
                throw new RuntimeException('Ключът за Serper (SERPER_API_KEY) не е валиден или кредитите са свършили.');
            }
            if (! $response->successful()) {
                throw new RuntimeException('Google Maps (Serper) върна грешка (код ' . $response->status() . ').');
            }

            $places = $response->json('places');
            if (! is_array($places) || $places === []) {
                break;
            }
            foreach ($places as $place) {
                $name = trim((string) ($place['title'] ?? ''));
                $id = $place['cid'] ?? $place['placeId'] ?? null;
                $website = self::website($place['website'] ?? null);
                if ($name === '' || ! $id || ! $website) {
                    continue;
                }
                $businesses['g:' . $id] = [
                    'external_id' => 'g:' . $id,
                    'name' => mb_substr($name, 0, 255),
                    'website' => $website,
                    'phone' => self::first($place['phoneNumber'] ?? null, 100),
                    'email' => null,
                    'city' => $city,
                ];
            }
            if (count($places) < 10) {
                break;
            }
        }

        return array_values($businesses);
    }

    /** Пита публичните Overpass сървъри един след друг, защото често са претоварени. */
    private function query(string $query): array
    {
        $last = 'няма отговор';
        foreach (self::ENDPOINTS as $endpoint) {
            try {
                $response = Http::asForm()
                    ->withUserAgent('CreatiumLab/1.0 (+https://creatiumlab.com)')
                    ->connectTimeout(10)
                    ->timeout(55)
                    ->post($endpoint, ['data' => $query]);
            } catch (ConnectionException $e) {
                $last = 'няма връзка';
                continue;
            }
            if ($response->successful() && is_array($response->json('elements'))) {
                return $response->json('elements');
            }
            $last = 'код ' . $response->status();
        }

        throw new RuntimeException("Картата (OpenStreetMap) не отговаря в момента ($last). Опитайте пак след няколко минути.");
    }

    private static function website(?string $value): ?string
    {
        $value = self::first($value, 255);
        if (! $value) {
            return null;
        }
        if (! preg_match('#^https?://#i', $value)) {
            $value = 'https://' . ltrim($value, '/');
        }

        return filter_var($value, FILTER_VALIDATE_URL) ? $value : null;
    }

    /** Първата стойност от „a; b“ (в картата понякога има няколко). */
    private static function first(?string $value, int $max): ?string
    {
        $value = trim(explode(';', (string) $value)[0]);

        return $value === '' ? null : mb_substr($value, 0, $max);
    }
}
