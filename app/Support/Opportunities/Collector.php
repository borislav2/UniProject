<?php

namespace App\Support\Opportunities;

use App\Models\Opportunity;
use App\Models\OutreachSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Събира обществените поръчки от TED, Contracts Finder и Find a Tender, оценява ги и ги пази като възможности.
 * НИКОГА не подава оферти и не праща нищо.
 *
 * Няма cron. Всяко посещение на сайта го побутва (Middleware\CollectOpportunities, след отговора), а tick() опреснява
 * най-много един източник на повикване и всеки източник най-много веднъж на interval_hours (6), под ключалка в кеша.
 * След неуспех следващият опит е най-рано след час. Грешките се записват (Здраве на системата) и никога не чупят страница.
 * Същото ръчно: php artisan creatium:opportunities [--source=] [--days=].
 */
class Collector
{
    public const STATE_KEY = 'opportunities_state';

    /** Колко време се изчаква след грешка, преди нов опит. */
    private const RETRY_MINUTES = 60;

    public static function enabled(): bool
    {
        return (bool) config('creatium.opportunities.enabled');
    }

    /** @return array<string, Feed> ключ => източник */
    public static function feeds(): array
    {
        $feeds = [];
        foreach ([app(TedFeed::class), app(ContractsFinderFeed::class), app(FindATenderFeed::class)] as $feed) {
            $feeds[$feed->key()] = $feed;
        }

        return $feeds;
    }

    /** @return array<string, array<string, mixed>> последното състояние по източник */
    public static function state(): array
    {
        return OutreachSetting::get(self::STATE_KEY, []);
    }

    private static function remember(string $source, array $changes): void
    {
        $all = self::state();
        $all[$source] = $changes + ($all[$source] ?? []);
        OutreachSetting::put(self::STATE_KEY, $all);
    }

    /** Дошло ли е времето за този източник. */
    public static function due(string $source): bool
    {
        $state = self::state()[$source] ?? [];
        if (empty($state['last_run_at'])) {
            return true;
        }
        $last = \Illuminate\Support\Carbon::parse($state['last_run_at']);
        $wait = ! empty($state['last_error']) ? self::RETRY_MINUTES : (int) config('creatium.opportunities.interval_hours') * 60;

        return $last->copy()->addMinutes($wait)->lessThanOrEqualTo(now());
    }

    /**
     * Една стъпка: най-закъснелият източник, на когото му е дошло времето (или $force за всички по ред на ключа).
     *
     * @return array{status: string, source?: string, result?: array}
     */
    public function tick(): array
    {
        if (! self::enabled()) {
            return ['status' => 'off'];
        }
        self::expireOld();

        $state = self::state();
        $candidates = collect(self::feeds())->filter(fn ($feed, $key) => self::due($key))
            ->sortBy(fn ($feed, $key) => $state[$key]['last_run_at'] ?? '0');
        foreach ($candidates as $key => $feed) {
            $lock = Cache::lock('opportunities:' . $key, 300);
            if (! $lock->get()) {
                continue;
            }
            try {
                if (! self::due($key)) {
                    continue;
                }

                return ['status' => 'ran', 'source' => $key, 'result' => $this->run($feed)];
            } finally {
                $lock->release();
            }
        }

        return ['status' => 'wait'];
    }

    /**
     * Опреснява всички източници веднага (бутонът „Опресни сега“). Източник, който в момента се опреснява, се пропуска.
     *
     * @return array<string, array|null> ключ => резултат (null = вече се опреснява)
     */
    public function refreshNow(): array
    {
        $results = [];
        foreach (self::feeds() as $key => $feed) {
            $lock = Cache::lock('opportunities:' . $key, 300);
            if (! $lock->get()) {
                $results[$key] = null;

                continue;
            }
            try {
                $results[$key] = $this->run($feed);
            } finally {
                $lock->release();
            }
        }

        return $results;
    }

    /**
     * Опреснява един източник и записва резултата. Не хвърля изключения.
     *
     * @return array{returned: int, matched: int, new: int, updated: int, rejected: int, expired: int, error: ?string}
     */
    public function run(Feed $feed, ?int $days = null): array
    {
        $result = ['returned' => 0, 'matched' => 0, 'new' => 0, 'updated' => 0, 'rejected' => 0, 'expired' => 0, 'error' => null];
        $days ??= (int) config('creatium.opportunities.lookback_days');
        $pages = (int) (config('creatium.opportunities.max_pages')[$feed->key()] ?? 2);

        try {
            $fetched = $feed->fetch(now()->subDays($days), $pages);
            $result['returned'] = $fetched['returned'];
            foreach ($fetched['records'] as $record) {
                $this->store($feed->key(), $record, $result);
            }
            self::expireOld();
            self::remember($feed->key(), ['last_run_at' => now()->toIso8601String(), 'last_ok_at' => now()->toIso8601String(), 'last_error' => null, 'last_error_at' => null] + array_diff_key($result, ['error' => 1]));
        } catch (Throwable $e) {
            $result['error'] = mb_substr($e->getMessage(), 0, 250);
            Log::warning('Opportunities feed failed', ['source' => $feed->key(), 'error' => $result['error']]);
            self::remember($feed->key(), ['last_run_at' => now()->toIso8601String(), 'last_error' => $result['error'], 'last_error_at' => now()->toIso8601String()]);
        }

        return $result;
    }

    /** @param array<string, mixed> $record  @param array<string, int|string|null> $result */
    private function store(string $source, array $record, array &$result): void
    {
        $scored = OpportunityScore::forTender($record);
        $existing = Opportunity::where('source', $source)->where('external_id', $record['external_id'])->first();

        if (! $existing) {
            if ($scored === null) {
                $result['expired']++;

                return;
            }
            if (! $scored['candidate'] || $scored['score'] < (int) config('creatium.opportunities.min_score')) {
                $result['rejected']++;

                return;
            }
        }

        // Само общото; състоянието на работата (статус, бележка, човек, стъпка, проект) никога не се пипа при повторно взимане.
        $values = [
            'title' => mb_substr($record['title'], 0, 500),
            'buyer' => $record['buyer'] ? mb_substr($record['buyer'], 0, 300) : null,
            'country' => $record['country'],
            'language' => $record['language'],
            'url' => $record['url'],
            'url_key' => Opportunity::urlKey($record['url']),
            'summary' => Privacy::scrub($record['text'] ?? null),
            'cpv' => array_slice($record['cpv'] ?? [], 0, 20),
            'value_min' => $record['value_min'],
            'value_max' => $record['value_max'],
            'currency' => $record['currency'],
            'published_at' => $record['published_at'],
            'deadline_at' => $record['deadline_at'],
            'last_seen_at' => now(),
        ];

        if ($scored) {
            $values += [
                'score' => $scored['score'],
                'score_reasons' => $scored['reasons'],
                'raw' => ($record['raw'] ?? []) + ['topic' => $scored['topic']],
            ];
        }

        if ($existing) {
            $existing->update($scored ? $values : ['deadline_at' => $record['deadline_at'], 'last_seen_at' => now()]);
            $result['updated']++;
            $result['matched']++;

            return;
        }

        Opportunity::create($values + ['kind' => 'tender', 'source' => $source, 'external_id' => $record['external_id'], 'status' => 'new']);
        $result['new']++;
        $result['matched']++;
    }

    /** Нови и интересни със срок, който е минал, стават „Пропусната: изтекъл срок“. Подготвяните и подадените не се пипат. */
    public static function expireOld(): int
    {
        $expired = Opportunity::whereIn('status', ['new', 'interesting'])->whereNotNull('deadline_at')->where('deadline_at', '<', now())->get();
        $expired->each->expire();

        return $expired->count();
    }
}
