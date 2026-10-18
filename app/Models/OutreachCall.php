<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Бизнес без сайт, намерен от Harvey: няма имейл, затова е в списъка за обаждане („Потенциални клиенти“ → „Без сайт“). */
class OutreachCall extends Model
{
    public const STATUSES = [
        'new' => 'За обаждане',
        'no_answer' => 'Не вдигна',
        'interested' => 'Интерес',
        'not_interested' => 'Без интерес',
        'client' => 'Клиент',
    ];

    /** Видовете бизнес, както ги записва Harvey (OSM таг или търсената дума), на български. */
    public const INDUSTRIES = [
        'restaurant' => 'Ресторант',
        'beauty' => 'Козметичен салон',
        'beauty salon' => 'Козметичен салон',
        'hairdresser' => 'Фризьор',
        'dentist' => 'Зъболекар',
        'car_repair' => 'Автосервиз',
        'car repair' => 'Автосервиз',
        'hotel' => 'Хотел',
        'physiotherapy' => 'Физиотерапия',
        'veterinary' => 'Ветеринар',
        'roofer' => 'Покриви',
        'roofing' => 'Покриви',
        'roofing contractor' => 'Покриви',
        'plumber' => 'ВиК',
        'electrician' => 'Електротехник',
        'builder' => 'Строител',
        'lawyer' => 'Адвокат',
        'gardener' => 'Озеленяване',
    ];

    /** Градовете от harvey.local.yaml; Harvey пише или тях, или града от адреса в картата. */
    public const CITIES = [
        'Sofia' => 'София',
        'Plovdiv' => 'Пловдив',
        'Varna' => 'Варна',
        'Burgas' => 'Бургас',
        'Veliko Tarnovo' => 'Велико Търново',
        'Ruse' => 'Русе',
    ];

    protected $fillable = ['external_id', 'name', 'phone', 'industry', 'city', 'source_url', 'status', 'note', 'called_at'];

    protected $casts = ['called_at' => 'datetime'];

    public function industryLabel(): ?string
    {
        return self::INDUSTRIES[mb_strtolower((string) $this->industry)] ?? $this->industry;
    }

    /** Номерът за линк tel: (само цифри и водещ +). */
    public function telHref(): ?string
    {
        $first = trim(preg_split('/[;,\/]/', (string) $this->phone)[0] ?? '');
        $digits = preg_replace('/(?!^\+)[^\d]/', '', $first);

        return strlen(ltrim($digits, '+')) >= 6 ? 'tel:' . $digits : null;
    }

    /**
     * Търсенето по радиус хваща и бизнеси отвъд границата (около Русе: Гюргево, Румъния).
     * Чужд е номер с код, различен от +359, или град на латиница, който не е от нашите.
     */
    public static function isForeign(?string $phone, ?string $city): bool
    {
        $phone = preg_replace('/[^\d+]/', '', (string) $phone);
        if (preg_match('/^(\+|00)(?!359)\d/', $phone)) {
            return true;
        }

        return filled($city) && ! preg_match('/\p{Cyrillic}/u', $city) && ! in_array($city, self::CITIES, true);
    }

    public static function cityName(?string $location): ?string
    {
        $city = trim(explode(',', (string) $location)[0]);

        return $city === '' ? null : (self::CITIES[$city] ?? $city);
    }
}
