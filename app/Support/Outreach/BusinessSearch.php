<?php

namespace App\Support\Outreach;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Търси бизнеси по град и вид в OpenStreetMap (Overpass API, безплатно и без регистрация).
 * Връща име, сайт, телефон и имейл, ако са в картата.
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

    /** Вид бизнес => етикет и таговете в картата, които го описват. */
    public const CATEGORIES = [
        'restaurant' => ['label' => 'Ресторанти', 'tags' => ['amenity=restaurant']],
        'cafe' => ['label' => 'Кафенета и барове', 'tags' => ['amenity=cafe', 'amenity=bar']],
        'hotel' => ['label' => 'Хотели и къщи за гости', 'tags' => ['tourism=hotel', 'tourism=guest_house']],
        'beauty' => ['label' => 'Козметични салони', 'tags' => ['shop=beauty']],
        'hairdresser' => ['label' => 'Фризьори', 'tags' => ['shop=hairdresser']],
        'dentist' => ['label' => 'Зъболекари', 'tags' => ['amenity=dentist', 'healthcare=dentist']],
        'physiotherapy' => ['label' => 'Физиотерапия', 'tags' => ['healthcare=physiotherapist']],
        'veterinary' => ['label' => 'Ветеринари', 'tags' => ['amenity=veterinary']],
        'car_repair' => ['label' => 'Автосервизи', 'tags' => ['shop=car_repair']],
        'fitness' => ['label' => 'Фитнес зали', 'tags' => ['leisure=fitness_centre']],
        'builder' => ['label' => 'Строители и ремонти', 'tags' => ['craft=builder', 'office=construction_company', 'craft=roofer']],
        'lawyer' => ['label' => 'Адвокати', 'tags' => ['office=lawyer']],
        'accountant' => ['label' => 'Счетоводители', 'tags' => ['office=accountant']],
    ];

    public const ENDPOINTS = [
        'https://overpass-api.de/api/interpreter',
        'https://overpass.kumi.systems/api/interpreter',
        'https://overpass.private.coffee/api/interpreter',
    ];

    private const LIMIT = 400;

    /**
     * @return list<array{external_id: string, name: string, website: ?string, phone: ?string, email: ?string, city: ?string}>
     */
    public function find(string $city, string $category): array
    {
        [$lat, $lng, $km] = self::CITIES[$city] ?? throw new RuntimeException("Непознат град: $city");
        $tags = self::CATEGORIES[$category]['tags'] ?? throw new RuntimeException("Непознат вид бизнес: $category");

        $around = 'around:' . ($km * 1000) . ",$lat,$lng";
        $clauses = implode('', array_map(function ($tag) use ($around) {
            [$key, $value] = explode('=', $tag);

            return "nwr[\"$key\"=\"$value\"]($around);";
        }, $tags));
        $query = "[out:json][timeout:50];($clauses);out center tags " . self::LIMIT . ';';

        $elements = $this->query($query);

        $businesses = [];
        foreach ($elements as $element) {
            $t = $element['tags'] ?? [];
            $name = trim((string) ($t['name'] ?? $t['name:bg'] ?? ''));
            if ($name === '' || ! isset($element['type'], $element['id'])) {
                continue;
            }
            $businesses[] = [
                'external_id' => 'osm:' . $element['type'] . '/' . $element['id'],
                'name' => mb_substr($name, 0, 255),
                'website' => self::website($t['website'] ?? $t['contact:website'] ?? $t['url'] ?? null),
                'phone' => self::first($t['phone'] ?? $t['contact:phone'] ?? $t['contact:mobile'] ?? null, 100),
                'email' => self::first($t['email'] ?? $t['contact:email'] ?? null, 255),
                'city' => self::first($t['addr:city'] ?? null, 255) ?: $city,
            ];
        }

        return $businesses;
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
