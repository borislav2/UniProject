<?php

namespace App\Support\Outreach;

use App\Models\OutreachBusiness;
use App\Models\OutreachCall;
use App\Models\OutreachProspect;
use App\Models\OutreachSearch;
use App\Models\OutreachSuppression;
use App\Support\SiteCheck\SiteChecker;
use Illuminate\Support\Facades\Cache;
use Throwable;

/**
 * Обработва търсене на части (за да не изтича времето на заявката):
 * 1) пита картата за бизнесите; 2) за всеки със сайт търси имейл и проверява сайта;
 * без имейл, но с телефон, бизнесът отива в списъка за обаждане.
 */
class ProspectFinder
{
    public function __construct(
        private BusinessSearch $search,
        private EmailFinder $emails,
        private EmailVerifier $verifier,
        private SiteChecker $checker,
    ) {
    }

    /** Работи до $seconds секунди по търсенето и връща докъде е стигнало. */
    public function work(OutreachSearch $search, float $seconds = 25): array
    {
        $lock = Cache::lock('outreach-search-' . $search->id, 180);
        if (! $lock->get()) {
            return $this->progress($search->fresh());
        }

        try {
            $started = microtime(true);
            if ($search->status === 'queued') {
                $this->findBusinesses($search);
            }
            while ($search->status === 'processing' && microtime(true) - $started < $seconds) {
                $business = $search->businesses()->whereNull('processed_at')->orderBy('id')->first();
                if (! $business) {
                    $search->update(['status' => 'done']);
                    break;
                }
                $this->process($search, $business);
            }
        } finally {
            $lock->release();
        }

        return $this->progress($search->fresh());
    }

    public function progress(OutreachSearch $search): array
    {
        return [
            'id' => $search->id,
            'status' => $search->status,
            'status_label' => OutreachSearch::STATUSES[$search->status] ?? $search->status,
            'found' => $search->found,
            'processed' => $search->businesses()->whereNotNull('processed_at')->count(),
            'emails' => $search->emails,
            'calls' => $search->calls,
            'error' => $search->error,
        ];
    }

    private function findBusinesses(OutreachSearch $search): void
    {
        try {
            $found = $this->search->find($search->city, $search->category);
        } catch (Throwable $e) {
            $search->update(['status' => 'failed', 'error' => mb_substr($e->getMessage(), 0, 250)]);

            return;
        }

        foreach ($found as $item) {
            // Бизнес от по-старо търсене не се проверява наново.
            OutreachBusiness::firstOrCreate(['external_id' => $item['external_id']], [
                'outreach_search_id' => $search->id,
                'name' => $item['name'],
                'category' => $search->category,
                'city' => $item['city'],
                'website' => $item['website'],
                'phone' => $item['phone'],
                'map_email' => $item['email'],
            ]);
        }

        $search->update(['status' => 'processing', 'found' => $search->businesses()->count()]);
    }

    private function process(OutreachSearch $search, OutreachBusiness $business): void
    {
        $found = null;
        try {
            if ($business->website) {
                $found = $this->emails->find($business->website);
            }
            if (! $found && $business->map_email && filter_var($business->map_email, FILTER_VALIDATE_EMAIL)) {
                $found = ['email' => mb_strtolower($business->map_email), 'page' => $business->website ?? ''];
            }

            if ($found && $this->addProspect($business, $found)) {
                $search->increment('emails');
            } elseif (config('creatium.outreach.search_call_list') && $this->addCall($business)) {
                $search->increment('calls');
            }
        } catch (Throwable $e) {
            report($e);
        }

        $business->update(['processed_at' => now(), 'email' => $found['email'] ?? null, 'email_page' => $found['page'] ?? null]);
    }

    private function addProspect(OutreachBusiness $business, array $found): bool
    {
        $email = $found['email'];
        if (OutreachSuppression::where('email', $email)->exists()) {
            return false;
        }
        $status = $this->verifier->status($email);
        if ($status === 'invalid') {
            return false;
        }

        $existing = OutreachProspect::where('email', $email)->first();
        if ($existing && $existing->status !== 'new') {
            return false;
        }

        $values = [
            'company' => $business->name,
            'website' => $business->website,
            'industry' => $business->category,
            'city' => $business->city,
            'email_status' => $status,
            'findings' => $business->website ? $this->findings($business->website) : [],
            'personalization' => $found['page'] ? 'Имейлът е публикуван на ' . $found['page'] : 'Имейлът е публикуван в картата (OpenStreetMap).',
        ];

        if ($existing) {
            $existing->update($values);

            return false;
        }
        OutreachProspect::create(['email' => $email, 'status' => 'new', 'score' => $status === 'verified' ? 60 : 50] + $values);

        return true;
    }

    private function addCall(OutreachBusiness $business): bool
    {
        if (! $business->phone || OutreachCall::isForeign($business->phone, $business->city)) {
            return false;
        }

        $call = OutreachCall::firstOrNew(['external_id' => $business->external_id]);
        if ($call->exists) {
            return false;
        }
        $call->fill([
            'name' => $business->name,
            'phone' => $business->phone,
            'industry' => $business->category,
            'city' => $business->city,
            'source_url' => 'https://www.openstreetmap.org/' . substr($business->external_id, 4),
        ])->save();

        return true;
    }

    /** До три конкретни неща от проверката на сайта: първо грешките, после предупрежденията. */
    private function findings(string $website): array
    {
        $domain = SiteChecker::normalizeDomain($website);
        if (! $domain) {
            return [];
        }
        $report = $this->checker->check($domain);

        return collect($report['checks'])
            ->filter(fn ($check) => $check['status'] !== 'ok')
            ->sortBy(fn ($check) => $check['status'] === 'fail' ? 0 : 1)
            ->take(3)
            ->map(fn ($check) => ['title' => $check['title'], 'detail' => $check['detail']])
            ->values()
            ->all();
    }
}
