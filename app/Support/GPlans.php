<?php

namespace App\Support;

/**
 * The packages of design G (fourth round, the owners' audit of November 2026): `plans.g_items` with a monthly and a yearly figure per plan.
 * Read only by the g views. The yearly figure is the one saved in `yearly`; when it is empty it is the monthly one times `plans.billing.yearly_months`
 * (10 = 2 months free, the same rule as the current site) until the owners confirm their own numbers.
 */
class GPlans
{
    /** The cards: name, number, the money strings per billing mode, the „Подходящо за“ text and the groups of „Какво включва“. */
    public static function cards(array $plans, ?array $billing = null): array
    {
        $billing ??= PlanBilling::config($plans);
        $months = (int) ($billing['yearly_months'] ?? 10);
        $cards = [];

        foreach (array_values((array) ($plans['g_items'] ?? [])) as $i => $plan) {
            if (! filled($plan['name'] ?? null)) {
                continue;
            }
            $monthly = self::number($plan['monthly'] ?? null);
            $yearly = self::number($plan['yearly'] ?? null) ?? ($monthly !== null ? $monthly * $months : null);

            $cards[] = [
                'index' => $i,
                'name' => $plan['name'],
                'from' => (string) ($plan['from'] ?? ''),
                'monthly' => $monthly !== null ? self::money($monthly) : null,
                'yearly' => $yearly !== null ? self::money($yearly) : null,
                'audience' => (string) ($plan['audience'] ?? ''),
                'groups' => collect((array) ($plan['groups'] ?? []))
                    ->filter(fn ($g) => filled($g['title'] ?? null))
                    ->map(fn ($g) => ['title' => $g['title'], 'points' => array_values(array_filter((array) ($g['points'] ?? []), fn ($p) => filled($p['title'] ?? null)))])
                    ->values()->all(),
            ];
        }

        return $cards;
    }

    /** The index of the plan (in `g_items`, same order as `items`) that fits a concept site's `package.name`; no name = the last, individual plan. */
    public static function indexFor(array $plans, ?string $packageName): ?int
    {
        $count = count((array) ($plans['g_items'] ?? []));
        if ($count === 0) {
            return null;
        }
        if (! filled($packageName)) {
            return $count - 1;
        }
        $wanted = Plans::normalize($packageName);
        foreach (array_values((array) ($plans['items'] ?? [])) as $i => $plan) {
            if (Plans::normalize($plan['name'] ?? null) === $wanted) {
                return min($i, $count - 1);
            }
        }

        return null;
    }

    /** „€1 200“ with a no-break space, which every font draws as a space (the thin one of PlanBilling::money() reads like „€1200“ in Geologica). */
    private static function money(int $amount): string
    {
        return str_replace(PlanBilling::THIN_SPACE, "\u{00A0}", PlanBilling::money($amount));
    }

    private static function number(mixed $value): ?int
    {
        $digits = preg_replace('/\D/', '', (string) $value);

        return $digits === '' ? null : (int) $digits;
    }
}
