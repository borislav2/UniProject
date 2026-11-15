<?php

namespace App\Support\Outreach;

use App\Models\OutreachProspect;
use App\Models\OutreachSearch;
use App\Models\OutreachSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Автопилотът: сам намира бизнеси (пазар, град, вид), оценява ги (LeadScore) и подготвя чернови за „За одобрение“.
 * НИКОГА не праща писма и не вика Sender: пращането е само с клик от човек.
 *
 * Няма cron. Всяко посещение на сайта го побутва (Middleware\RunOutreachAutopilot, след отговора), а tick() прави най-много
 * една стъпка на 10 минути, само между 08:00 и 20:00 в София, под ключалка в кеша. Стъпка = продължаване на текущо търсене
 * с една порция работа (ProspectFinder::work) или започване на следващо. Спира за деня при daily_new нови бизнеса
 * и не започва повече от daily_searches търсения на ден (всяко търсене в Google Maps е до 3 кредита в Serper).
 *
 * Настройките (план, лимити, пауза) са в таблица outreach_settings; creatium.outreach.autopilot са стойностите по подразбиране
 * и главният ключ OUTREACH_AUTOPILOT.
 */
class Autopilot
{
    public const FROM_HOUR = 8;

    public const TO_HOUR = 20;

    public function __construct(private ProspectFinder $finder)
    {
    }

    /** Главният ключ (OUTREACH_AUTOPILOT) е включен и няма пауза от екрана „Автопилот“. */
    public static function enabled(): bool
    {
        return (bool) config('creatium.outreach.autopilot.enabled') && ! self::settings()['paused'];
    }

    /** Работно време за стъпки: 08:00 до 20:00 в София. */
    public static function inHours(): bool
    {
        $hour = now('Europe/Sofia')->hour;

        return $hour >= self::FROM_HOUR && $hour < self::TO_HOUR;
    }

    /** @return array{paused: bool, daily_new: int, daily_searches: int, plan: array<string, array{enabled: bool, cities: list<string>, categories: list<string>}>} */
    public static function settings(): array
    {
        $defaults = config('creatium.outreach.autopilot');
        $saved = OutreachSetting::get('autopilot', []);

        return [
            'paused' => (bool) ($saved['paused'] ?? false),
            'daily_new' => max(0, (int) ($saved['daily_new'] ?? $defaults['daily_new'])),
            'daily_searches' => max(0, (int) ($saved['daily_searches'] ?? $defaults['daily_searches'])),
            'plan' => $saved['plan'] ?? $defaults['plan'],
        ];
    }

    public static function saveSettings(array $values): void
    {
        OutreachSetting::put('autopilot', array_intersect_key($values + self::settings(), array_flip(['paused', 'daily_new', 'daily_searches', 'plan'])));
    }

    /** Последното състояние: кога, какво и последната грешка (за „Здраве на системата“). */
    public static function state(): array
    {
        return OutreachSetting::get('autopilot_state', []);
    }

    private static function remember(array $changes): void
    {
        OutreachSetting::put('autopilot_state', $changes + self::state());
    }

    private static function startOfDay()
    {
        return now('Europe/Sofia')->startOfDay()->utc();
    }

    /** Нови бизнеси днес от търсенията на автопилота. */
    public static function newToday(): int
    {
        return OutreachProspect::whereNotNull('outreach_search_id')->where('created_at', '>=', self::startOfDay())
            ->whereHas('search', fn ($q) => $q->whereNull('user_id'))->count();
    }

    /** Търсения днес: само на автопилота или всички. */
    public static function searchesToday(bool $autopilotOnly = true): int
    {
        return OutreachSearch::where('created_at', '>=', self::startOfDay())->when($autopilotOnly, fn ($q) => $q->whereNull('user_id'))->count();
    }

    /** Страници от Serper (кредити) днес, от всички търсения. */
    public static function pagesToday(): int
    {
        return (int) OutreachSearch::where('created_at', '>=', self::startOfDay())->sum('serper_pages');
    }

    /** Подготвени чернови, които чакат човек. */
    public static function waiting(): int
    {
        return OutreachProspect::awaitingApproval()->count();
    }

    /**
     * Една стъпка. $force пропуска работното време и паузата от 10 минути (за php artisan creatium:autopilot).
     *
     * @return array{status: string, search?: string, new_today?: int, drafts?: int, error?: ?string}
     */
    public function tick(bool $force = false): array
    {
        if (! self::enabled()) {
            return ['status' => 'off'];
        }
        if (! $force && ! self::inHours()) {
            return ['status' => 'hours'];
        }

        $lock = Cache::lock('outreach-autopilot', 300);
        if (! $lock->get()) {
            return ['status' => 'busy'];
        }

        try {
            if (! $force && ! Cache::add('outreach-autopilot:step', true, 600)) {
                return ['status' => 'wait'];
            }

            return $this->step();
        } catch (Throwable $e) {
            report($e);
            self::remember(['last_error' => mb_substr($e->getMessage(), 0, 250), 'last_error_at' => now()->toIso8601String()]);

            return ['status' => 'error', 'error' => $e->getMessage()];
        } finally {
            $lock->release();
        }
    }

    private function step(): array
    {
        $settings = self::settings();
        if (self::newToday() >= $settings['daily_new']) {
            self::remember(['last_tick_at' => now()->toIso8601String(), 'last_status' => 'daily_new']);

            return ['status' => 'daily_new', 'new_today' => self::newToday()];
        }

        $search = OutreachSearch::whereNull('user_id')->whereIn('status', ['queued', 'processing'])->oldest('id')->first();
        if (! $search) {
            if (self::searchesToday() >= $settings['daily_searches']) {
                self::remember(['last_tick_at' => now()->toIso8601String(), 'last_status' => 'daily_searches']);

                return ['status' => 'daily_searches'];
            }
            $next = $this->nextSearch($settings);
            if (! $next) {
                self::remember(['last_tick_at' => now()->toIso8601String(), 'last_status' => 'nothing_to_search']);

                return ['status' => 'nothing_to_search'];
            }
            $search = OutreachSearch::create($next + ['language' => Markets::language($next['country']), 'status' => 'queued']);
        }

        // Една порция работа (около 15 секунди). Търсенето продължава със следващата стъпка, ако не е приключило.
        $progress = $this->finder->work($search, 15);
        $drafts = $this->prepareDrafts();

        $changes = [
            'last_tick_at' => now()->toIso8601String(),
            'last_status' => $progress['status'],
            'last_search' => $search->fresh()->label(),
        ];
        if ($progress['status'] === 'failed') {
            $changes += ['last_error' => $progress['error'], 'last_error_at' => now()->toIso8601String()];
        } else {
            $changes += ['last_error' => null];
        }
        self::remember($changes);

        Log::info('Outreach autopilot step', [
            'search' => $search->label(), 'status' => $progress['status'], 'new_today' => self::newToday(), 'drafts' => $drafts,
            'searches_today' => self::searchesToday(false), 'serper_pages_today' => self::pagesToday(),
        ]);

        return ['status' => $progress['status'], 'search' => $search->label(), 'new_today' => self::newToday(), 'drafts' => $drafts, 'error' => $progress['error']];
    }

    /** Чернови за най-добрите нови бизнеси (поне min_score точки), най-много десет на стъпка. */
    public function prepareDrafts(): int
    {
        $prepared = 0;
        $candidates = OutreachProspect::where('status', 'new')->whereNull('draft_at')->whereNotNull('outreach_search_id')
            ->whereNotNull('site_checked_at')->where('score', '>=', (int) config('creatium.outreach.autopilot.min_score'))
            ->orderByDesc('score')->limit(10)->get();
        foreach ($candidates as $prospect) {
            if (Drafts::prepare($prospect)) {
                $prepared++;
            }
        }

        return $prepared;
    }

    /**
     * Следващата комбинация пазар, град и вид, която не е търсена през последните repeat_days дни.
     * Пазарите се редуват (първо този, по който най-отдавна е търсил автопилотът), градовете и видовете по реда в плана.
     *
     * @return array{country: string, city: string, category: string}|null
     */
    public function nextSearch(array $settings): ?array
    {
        $recent = OutreachSearch::where('created_at', '>=', now()->subDays((int) config('creatium.outreach.autopilot.repeat_days')))
            ->where('status', '!=', 'failed')->get(['country', 'city', 'category'])
            ->map(fn ($s) => $s->country . '|' . $s->city . '|' . $s->category)->flip();

        $markets = collect($settings['plan'])
            ->filter(fn ($plan, $code) => Markets::exists($code) && ! empty($plan['enabled']))
            ->map(fn ($plan, $code) => [
                'code' => $code,
                'last' => OutreachSearch::whereNull('user_id')->where('country', $code)->max('created_at') ?? '0',
                'plan' => $plan,
            ])->sortBy('last')->values();

        foreach ($markets as $market) {
            $cities = array_keys(Markets::cities($market['code']));
            if (! empty($market['plan']['cities'])) {
                $cities = array_values(array_intersect($cities, $market['plan']['cities']));
            }
            $categories = array_values(array_filter($market['plan']['categories'] ?? [], fn ($c) => Markets::query($market['code'], $c) !== null));

            foreach ($cities as $city) {
                foreach ($categories as $category) {
                    if (! $recent->has($market['code'] . '|' . $city . '|' . $category)) {
                        return ['country' => $market['code'], 'city' => $city, 'category' => $category];
                    }
                }
            }
        }

        return null;
    }
}
