<?php

namespace App\Models;

use App\Support\Outreach\Markets;
use App\Support\Phone;
use Illuminate\Database\Eloquent\Builder;
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
        'agency' => 'Дигитална или маркетинг агенция',
        'web_agency' => 'Уеб агенция',
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
        'cafe' => 'Кафене / бар',
        'fitness' => 'Фитнес',
        'accountant' => 'Счетоводител',
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

    /** Статуси, след които не звъним повече. */
    public const FINAL_STATUSES = ['not_interested', 'client'];

    /** След толкова обаждания без отговор бизнесът излиза от опашката. */
    public const MAX_NO_ANSWER = 2;

    protected $fillable = ['external_id', 'name', 'phone', 'industry', 'city', 'source_url', 'status', 'note', 'called_at', 'callback_on', 'project_id', 'country', 'language'];

    protected $casts = ['called_by' => 'integer', 'called_at' => 'datetime', 'callback_on' => 'date', 'is_mobile' => 'boolean', 'attempts' => 'integer'];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /** Броячът за деня: колко бизнеса са обадени днес (София) и с какъв резултат. */
    public static function todayStats(): array
    {
        $byStatus = self::where('called_at', '>=', now('Europe/Sofia')->startOfDay()->utc())
            ->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

        return [
            'total' => (int) $byStatus->sum(),
            'by_status' => $byStatus,
            'goal' => max(1, (int) config('creatium.outreach.daily_call_goal')),
        ];
    }

    /** Кой от екипа се обади последно (записва се при резултат от обаждането). */
    public function caller()
    {
        return $this->belongsTo(User::class, 'called_by');
    }

    protected static function booted(): void
    {
        static::saving(function (OutreachCall $call) {
            if ($call->isDirty('phone')) {
                $call->is_mobile = self::hasMobile($call->phone, $call->country);
            }
        });
    }

    /** Днешната дата в София, за „Обади се отново“ и брояча за деня. */
    public static function today(): string
    {
        return now('Europe/Sofia')->toDateString();
    }

    /**
     * Опашката за „Следващо обаждане“: първо уговорените за днес (или пропуснати по-рано), после новите,
     * после тези, които не вдигнаха веднъж (на друг ден). Мобилните са първи, защото там вдига собственикът.
     */
    public function scopeQueue(Builder $query): Builder
    {
        $today = self::today();
        $startOfDay = now('Europe/Sofia')->startOfDay()->utc();

        return $query->whereNotNull('phone')
            ->whereNotIn('status', self::FINAL_STATUSES)
            ->where(function ($q) use ($today, $startOfDay) {
                $q->whereDate('callback_on', '<=', $today)
                    ->orWhere(fn ($q) => $q->whereNull('callback_on')->where('status', 'new'))
                    ->orWhere(fn ($q) => $q->whereNull('callback_on')->where('status', 'no_answer')
                        ->where('attempts', '<', self::MAX_NO_ANSWER)
                        ->where(fn ($q) => $q->whereNull('called_at')->orWhere('called_at', '<', $startOfDay)));
            })
            ->orderByRaw('case when callback_on is not null then 0 when status = ? then 1 else 2 end', ['new'])
            ->orderBy('callback_on')
            ->orderByDesc('is_mobile')
            ->orderBy('city')
            ->orderBy('name')
            ->orderBy('id');
    }

    /** Обаждане днес ли е уговорено (или вече е минало). */
    public function callbackDue(): bool
    {
        return $this->callback_on && $this->callback_on->toDateString() <= self::today();
    }

    /**
     * Всички номера от записа (Harvey понякога записва няколко с „;“), с линк и дали са мобилни.
     *
     * @return list<array{label: string, href: ?string, mobile: bool}>
     */
    public function phones(): array
    {
        return collect(preg_split('/[;,\/]/', (string) $this->phone))
            ->map(fn ($p) => trim($p))
            ->filter()
            ->map(fn ($p) => ['label' => $p, 'href' => self::href($p, $this->country), 'mobile' => self::isMobileNumber($p, $this->country)])
            ->sortByDesc('mobile')
            ->values()
            ->all();
    }

    /**
     * Мобилен номер. За България: 087, 088, 089, 098, 099 (и същите с +359 / 00359). За другите пазари по техните
     * правила (Великобритания 07…, Ирландия 08x, Нидерландия 06…), с или без кода на държавата.
     */
    public static function isMobileNumber(?string $phone, ?string $market = null): bool
    {
        $market = Markets::normalize($market);
        $raw = trim((string) $phone);
        $digits = preg_replace('/\D/', '', $raw);

        if ($market === Markets::DEFAULT) {
            $digits = preg_replace('/^(00359|359)/', '0', $digits);

            return (bool) preg_match('/^0(8[789]|9[89])\d{7}$/', $digits);
        }

        $code = ltrim(Markets::phonePrefix($market), '+');
        if (str_starts_with($raw, '+') || str_starts_with($digits, '00')) {
            $digits = preg_replace('/^(00)?' . $code . '/', '0', $digits);
        }

        return (bool) match ($market) {
            'gb' => preg_match('/^07[1-9]\d{8}$/', $digits),
            'ie' => preg_match('/^08[35-9]\d{7}$/', $digits),
            'nl' => preg_match('/^06\d{8}$/', $digits),
            default => false,
        };
    }

    public static function hasMobile(?string $phone, ?string $market = null): bool
    {
        foreach (preg_split('/[;,\/]/', (string) $phone) as $part) {
            if (self::isMobileNumber($part, $market)) {
                return true;
            }
        }

        return false;
    }

    private static function href(string $phone, ?string $market = null): ?string
    {
        $digits = preg_replace('/(?!^\+)[^\d]/', '', trim($phone));
        if (strlen(ltrim($digits, '+')) < 6) {
            return null;
        }

        return Markets::normalize($market) === Markets::DEFAULT ? 'tel:' . $digits : Phone::tel($phone, $market);
    }

    public function industryLabel(): ?string
    {
        return self::INDUSTRIES[mb_strtolower((string) $this->industry)] ?? $this->industry;
    }

    /**
     * Търсенето по радиус хваща и бизнеси отвъд границата (около Русе: Гюргево, Румъния).
     * Чужд е номер с код, различен от този на пазара на търсенето ($market, по подразбиране България),
     * или (само за България) град на латиница, който не е от нашите. Бизнес във Великобритания не е чужд, защото не е с +359.
     */
    public static function isForeign(?string $phone, ?string $city, ?string $market = null): bool
    {
        $market = Markets::normalize($market);
        $phone = preg_replace('/[^\d+]/', '', (string) $phone);
        $code = ltrim(Markets::phonePrefix($market), '+');
        if (preg_match('/^(\+|00)(?!' . $code . ')\d/', $phone)) {
            return true;
        }

        return $market === Markets::DEFAULT && filled($city) && ! preg_match('/\p{Cyrillic}/u', $city) && ! in_array($city, self::CITIES, true);
    }

    public static function cityName(?string $location): ?string
    {
        $city = trim(explode(',', (string) $location)[0]);

        return $city === '' ? null : (self::CITIES[$city] ?? $city);
    }
}
