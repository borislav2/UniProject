<?php

namespace App\Support\Opportunities;

use App\Models\Opportunity;
use App\Models\User;
use Illuminate\Support\Carbon;

/** Ръчно добавяне на възможност (платформи за свободна практика, грантове, нещо чуто): с оценка и без повторения по адрес. */
class ManualEntry
{
    /**
     * @param  array{kind: string, title: string, url?: ?string, origin?: ?string, country?: ?string, budget?: mixed, currency?: ?string, deadline?: ?string, note?: ?string}  $data
     * @return array{opportunity: Opportunity, created: bool}
     */
    public static function add(array $data, ?User $user): array
    {
        $key = Opportunity::urlKey($data['url'] ?? null);
        if ($key && ($existing = Opportunity::where('url_key', $key)->first())) {
            return ['opportunity' => $existing, 'created' => false];
        }

        $deadline = filled($data['deadline'] ?? null) ? Carbon::parse($data['deadline'], 'Europe/Sofia')->endOfDay()->utc() : null;
        $budget = filled($data['budget'] ?? null) ? (float) $data['budget'] : null;
        $d = [
            'title' => $data['title'],
            'text' => $data['note'] ?? '',
            'country' => $data['country'] ?? null,
            'language' => null,
            'value_max' => $budget,
            'currency' => $budget ? ($data['currency'] ?: 'EUR') : null,
            'deadline_at' => $deadline && $deadline->isFuture() ? $deadline : null,
        ];
        $scored = OpportunityScore::forTender($d) ?? OpportunityScore::forTender(['deadline_at' => null] + $d);
        $reasons = $scored['reasons'];
        $score = $scored['score'];
        // Добавена на ръка значи, че човек я е преценил: без съвпадение на ключови думи тя не започва от нула.
        if ($scored['cpv'] === [] && ! $scored['candidate']) {
            $reasons = array_merge([['label' => 'Добавена на ръка от екипа', 'points' => 20]], $reasons);
            $score = min(100, $score + 20);
        }

        $opportunity = Opportunity::create([
            'kind' => $data['kind'],
            'source' => 'manual',
            'external_id' => 'manual:' . ($key ?: sha1(mb_strtolower($data['title']) . '|' . microtime(true) . '|' . random_int(0, 99999))),
            'title' => mb_substr($data['title'], 0, 500),
            'country' => filled($data['country'] ?? null) ? strtoupper($data['country']) : null,
            'url' => $data['url'] ?? null,
            'url_key' => $key,
            'summary' => Privacy::scrub($data['note'] ?? null, 500),
            'value_max' => $budget,
            'currency' => $budget ? ($data['currency'] ?: 'EUR') : null,
            'deadline_at' => $deadline,
            'score' => $score,
            'score_reasons' => $reasons,
            'status' => 'new',
            'note' => $data['note'] ?? null,
            'assigned_to' => $user?->id,
            'added_by' => $user?->id,
            'raw' => array_filter(['origin' => $data['origin'] ?? null, 'topic' => $scored['topic']]),
            'last_seen_at' => now(),
        ]);

        return ['opportunity' => $opportunity, 'created' => true];
    }
}
