<?php

namespace App\Support\Opportunities;

use App\Models\Opportunity;
use App\Models\OutreachProspect;
use App\Support\Outreach\BusinessSearch;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Агенциите, намерени от търсенето на бизнеси („Дигитални и маркетинг агенции“, „Уеб агенции“), стават възможности
 * от вид „партньор“ и сочат към потенциалния клиент в „Потенциални клиенти“. Нищо не се праща автоматично.
 */
class PartnerOpportunities
{
    public static function isPartner(?OutreachProspect $prospect): bool
    {
        return $prospect && in_array($prospect->industry, BusinessSearch::partnerCategories(), true);
    }

    /** Създава или опреснява възможността на агенцията (статусът и бележките не се пипат). Никога не хвърля изключение. */
    public static function sync(OutreachProspect $prospect): ?Opportunity
    {
        if (! self::isPartner($prospect)) {
            return null;
        }

        try {
            $score = OpportunityScore::partner($prospect);
            $host = $prospect->website ? preg_replace('/^www\./', '', (string) parse_url($prospect->website, PHP_URL_HOST)) : null;
            $opportunity = Opportunity::firstOrNew(['source' => 'search', 'external_id' => 'prospect:' . $prospect->id]);
            $values = [
                'kind' => 'partner',
                'title' => $prospect->displayName(),
                'buyer' => null,
                'country' => strtoupper((string) ($prospect->country ?: 'BG')),
                'language' => $prospect->language ?: null,
                'url' => $prospect->website,
                'url_key' => Opportunity::urlKey($prospect->website),
                'summary' => collect([BusinessSearch::CATEGORIES[$prospect->industry]['label'] ?? null, $prospect->city, $host])->filter()->join(', '),
                'score' => $score['score'],
                'score_reasons' => $score['reasons'],
                'outreach_prospect_id' => $prospect->id,
                'raw' => ['city' => $prospect->city, 'industry' => $prospect->industry, 'host' => $host],
                'last_seen_at' => now(),
            ];
            if (! $opportunity->exists) {
                $values += ['status' => 'new'];
            }
            $opportunity->fill($values)->save();

            return $opportunity;
        } catch (Throwable $e) {
            Log::warning('Partner opportunity failed', ['prospect' => $prospect->id, 'error' => $e->getMessage()]);

            return null;
        }
    }
}
