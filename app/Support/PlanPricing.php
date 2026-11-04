<?php

namespace App\Support;

/**
 * Плъзгачът „колко съдържание ви трябва“ на /paketi (`plans.pricing`, собствениците, ноември 2026).
 *
 * Всички числа и надписи се смятат тук, на сървъра: страницата е пълна и без JavaScript, а скриптът само сменя
 * готовите низове (payload()). Формули (X = месечната сума на стъпката за пакета, S = стартовата цена, M = yearly_months):
 *   месечно:  на месец X;                      първа година S + 11 × X
 *   годишно:  на месец round(X × M / 12);      първа година S + round(11 × X × M / 12);  веднъж годишно X × M вместо X × 12
 * Пакет без сума в стъпката (null) или извън първите два е „Индивидуална оферта“.
 */
class PlanPricing
{
    public const MONTHLY = 'monthly';

    public const YEARLY = 'yearly';

    public const THIN_SPACE = "\u{202F}";

    /** The pricing block when it has at least one valid step, else null (the slider is then hidden). */
    public static function config(array $plans): ?array
    {
        $pricing = $plans['pricing'] ?? null;
        if (! is_array($pricing) || empty($pricing['steps']) || ! is_array($pricing['steps'])) {
            return null;
        }

        $steps = [];
        foreach (array_values($pricing['steps']) as $step) {
            if (! is_array($step) || ! filled($step['value'] ?? null)) {
                continue;
            }
            $steps[] = [
                'value' => (string) $step['value'],
                'monthly' => array_values((array) ($step['monthly'] ?? [])),
                'recommended' => (int) ($step['recommended'] ?? 1),
            ];
        }
        if ($steps === []) {
            return null;
        }

        $pricing['steps'] = $steps;
        $pricing['default'] = max(0, min(count($steps) - 1, (int) ($pricing['default'] ?? 0)));
        $pricing['yearly_months'] = max(1, min(12, (int) ($pricing['yearly_months'] ?? 10)));

        return $pricing;
    }

    /** A step index from the query: only 0..n-1, anything else is the default. */
    public static function stepFrom(mixed $value, array $pricing): int
    {
        if (is_string($value) && ctype_digit($value) && (int) $value < count($pricing['steps'])) {
            return (int) $value;
        }

        return $pricing['default'];
    }

    public static function billingFrom(mixed $value): string
    {
        return $value === 'godishno' || $value === self::YEARLY ? self::YEARLY : self::MONTHLY;
    }

    /** The query value of a billing mode (?plashtane=). */
    public static function billingParam(string $billing): string
    {
        return $billing === self::YEARLY ? 'godishno' : 'mesechno';
    }

    /** The „-17%“ on the yearly button. */
    public static function savingPercent(array $pricing): int
    {
        return (int) round((1 - $pricing['yearly_months'] / 12) * 100);
    }

    /** The first euro amount in the plan's first price line (the fixed start price), or null. */
    public static function startPrice(array $plan): ?int
    {
        if (preg_match('/€\s?(\d[\d\s.,]*)/u', (string) (($plan['prices'] ?? [])[0] ?? ''), $m)) {
            return (int) preg_replace('/\D/', '', $m[1]);
        }

        return null;
    }

    public static function money(int|float $amount, ?string $locale = null): string
    {
        $locale ??= app()->getLocale();

        return '€' . number_format($amount, 0, '.', $locale === 'en' ? ',' : self::THIN_SPACE);
    }

    /** The recommended plan of a step, kept inside the plans that exist. */
    public static function recommended(array $pricing, int $step, int $planCount): int
    {
        return max(0, min((int) $pricing['steps'][$step]['recommended'], $planCount - 1));
    }

    /** What the volume line says: „4 публикации и статии на месец“. */
    public static function volumeLabel(array $pricing, int $step): string
    {
        return $pricing['steps'][$step]['value'] . ' ' . $pricing['unit'];
    }

    /**
     * The numbers of one plan at one step in one billing mode, or null for a custom offer.
     * Keys: monthly (the number the card shows per month), list (X × 12 and X × M for yearly), year (first year total).
     */
    public static function figures(array $pricing, array $plan, int $planIndex, int $step, string $billing): ?array
    {
        $x = $pricing['steps'][$step]['monthly'][$planIndex] ?? null;
        $start = self::startPrice($plan);
        if ($planIndex > 1 || ! is_numeric($x) || $start === null) {
            return null;
        }

        $m = $pricing['yearly_months'];
        if ($billing === self::YEARLY) {
            return [
                'monthly' => (int) round($x * $m / 12),
                'once' => (int) round($x * $m),
                'instead' => (int) round($x * 12),
                'year' => $start + (int) round(11 * $x * $m / 12),
            ];
        }

        return ['monthly' => (int) $x, 'once' => null, 'instead' => null, 'year' => $start + 11 * (int) $x];
    }

    /** Ready-to-show strings for one plan / step / billing mode. */
    public static function cell(array $pricing, array $plan, int $planIndex, int $step, string $billing): array
    {
        $f = self::figures($pricing, $plan, $planIndex, $step, $billing);
        $custom = $f === null;
        $volume = $planIndex > 1 ? __('по мярка') : $pricing['steps'][$step]['value'] . ' ' . $pricing['unit_short'] . ' ' . __('на месец');

        return [
            'custom' => $custom,
            'price' => $custom ? '' : self::money($f['monthly']),
            'suffix' => $custom ? '' : ($billing === self::YEARLY ? __('/ месец при годишно плащане') : __('/ месец')),
            'sub' => $custom || $f['once'] === null ? '' : __(':once веднъж годишно вместо :instead', ['once' => self::money($f['once']), 'instead' => self::money($f['instead'])]),
            'year' => $custom ? '' : __(':total за първата година', ['total' => self::money($f['year'])]),
            'volume' => __('Включени') . ': ' . $volume,
            'cell' => $planIndex > 1 ? __('по мярка') : ($custom ? $pricing['steps'][$step]['value'] . ' (' . __('по оферта') . ')' : $pricing['steps'][$step]['value']),
        ];
    }

    /**
     * The steps as plain text lines for Kreo, /llms.txt and the FAQ answer: what each package costs per month at every volume
     * (monthly and with yearly payment) and the first-year totals. Empty when there is no slider.
     */
    public static function textLines(array $plans): array
    {
        $pricing = self::config($plans);
        if (! $pricing) {
            return [];
        }

        $items = array_values($plans['items'] ?? []);
        $lines = [];
        foreach ($pricing['steps'] as $s => $step) {
            $parts = [];
            foreach (array_slice($items, 0, 2) as $i => $plan) {
                $monthly = self::figures($pricing, $plan, $i, $s, self::MONTHLY);
                $yearly = self::figures($pricing, $plan, $i, $s, self::YEARLY);
                $parts[] = $monthly === null
                    ? $plan['name'] . ': ' . __('Индивидуална оферта')
                    : $plan['name'] . ': ' . self::money($monthly['monthly']) . ' ' . __('/ месец') . ' (' . __('първа година') . ' ' . self::money($monthly['year']) . '); '
                        . __('при годишно плащане') . ' ' . self::money($yearly['monthly']) . ' ' . __('/ месец') . ' (' . __('първа година') . ' ' . self::money($yearly['year']) . ')';
            }
            if (isset($items[2])) {
                $parts[] = $items[2]['name'] . ': ' . __('по мярка');
            }
            $lines[] = '- ' . self::volumeLabel($pricing, $s) . ' → ' . implode(' | ', $parts);
        }

        return $lines;
    }

    /** One sentence on how the slider works, for the places that print the table. */
    public static function textIntro(array $plans): string
    {
        $pricing = self::config($plans);
        if (! $pricing) {
            return '';
        }

        return $pricing['title'] . '. ' . ($pricing['hint'] ?? '') . ' '
            . __('Стартовата цена остава фиксирана; месечната такса зависи от обема. Годишното плащане е :m месеца от 12.', ['m' => $pricing['yearly_months']]);
    }

    /** Everything the script needs: per step the label, the recommended plan and every plan's strings for both billing modes. */
    public static function payload(array $pricing, array $plans): array
    {
        $items = $plans['items'] ?? [];
        $steps = [];
        foreach ($pricing['steps'] as $s => $step) {
            $cells = [];
            foreach (array_values($items) as $i => $plan) {
                $cells[] = [
                    self::MONTHLY => self::cell($pricing, $plan, $i, $s, self::MONTHLY),
                    self::YEARLY => self::cell($pricing, $plan, $i, $s, self::YEARLY),
                ];
            }
            $steps[] = [
                'value' => $step['value'],
                'label' => self::volumeLabel($pricing, $s),
                'recommended' => self::recommended($pricing, $s, count($items)),
                'plans' => $cells,
            ];
        }

        return ['steps' => $steps, 'params' => ['monthly' => self::billingParam(self::MONTHLY), 'yearly' => self::billingParam(self::YEARLY)]];
    }
}
