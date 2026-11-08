<?php

namespace App\Support;

/**
 * Месечно / годишно плащане на абонамента на /paketi (`plans.billing`, собствениците, ноември 2026).
 *
 * Цените са фиксирани и се четат от текста на пакета: първото число в първия ред на `prices` е стартовата цена,
 * първото число във втория ред е месечният абонамент (само за първите два пакета; третият е по индивидуална оферта).
 * Годишно плащане: абонаментът за 12 месеца се плаща като `yearly_months` (10) месеца, т.е. 2 месеца безплатно.
 *   месечно:  на месец X;   първа година S + 11 × X   (първият месец е в стартовата цена)
 *   годишно:  веднъж X × M вместо X × 12;   общо S + X × M   (абонаментът за 12 месеца започва от втория месец)
 * Всички низове се правят тук, на сървъра: страницата е пълна и без JavaScript, а скриптът само показва единия или другия вариант.
 */
class PlanBilling
{
    public const MONTHLY = 'monthly';

    public const YEARLY = 'yearly';

    public const THIN_SPACE = "\u{202F}";

    /** Settings of the billing toggle, with safe defaults; the toggle is off when yearly billing would give no discount. */
    public static function config(array $plans): array
    {
        $billing = (array) ($plans['billing'] ?? []);
        $billing['yearly_months'] = max(1, min(12, (int) ($billing['yearly_months'] ?? 10)));
        $billing['enabled'] = $billing['yearly_months'] < 12;

        return $billing;
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
    public static function savingPercent(array $billing): int
    {
        return (int) round((1 - $billing['yearly_months'] / 12) * 100);
    }

    /** The first euro amount in a price line („€400 еднократно …“, „след това €200 / месец“, „от €1 200 / месец“), or null. */
    public static function amount(?string $line): ?int
    {
        if (preg_match('/€\s?(\d[\d\s.,]*)/u', (string) $line, $m) && ($digits = preg_replace('/\D/', '', $m[1])) !== '') {
            return (int) $digits;
        }

        return null;
    }

    public static function startPrice(array $plan): ?int
    {
        return self::amount(($plan['prices'] ?? [])[0] ?? null);
    }

    public static function monthlyPrice(array $plan): ?int
    {
        return self::amount(($plan['prices'] ?? [])[1] ?? null);
    }

    public static function money(int|float $amount, ?string $locale = null): string
    {
        $locale ??= app()->getLocale();

        return '€' . number_format($amount, 0, '.', $locale === 'en' ? ',' : self::THIN_SPACE);
    }

    /**
     * The numbers of one plan in one billing mode, or null when the plan has none (a custom offer: only the first two plans are priced).
     * Keys: monthly (what the card shows per period), once (the yearly sum), instead (12 × the month), year (total for the first year).
     */
    public static function figures(array $billing, array $plan, int $planIndex, string $mode): ?array
    {
        $start = self::startPrice($plan);
        $x = self::monthlyPrice($plan);
        if ($planIndex > 1 || $start === null || $x === null) {
            return null;
        }

        $m = $billing['yearly_months'];
        if ($mode === self::YEARLY) {
            return ['monthly' => (int) round($x * $m / 12), 'once' => $x * $m, 'instead' => $x * 12, 'year' => $start + $x * $m];
        }

        return ['monthly' => $x, 'once' => null, 'instead' => null, 'year' => $start + 11 * $x];
    }

    /** Ready-to-show strings for one plan in one billing mode. */
    public static function cell(array $billing, array $plan, int $planIndex, string $mode): array
    {
        $f = self::figures($billing, $plan, $planIndex, $mode);
        if ($f === null) {
            return ['priced' => false, 'price' => '', 'suffix' => '', 'sub' => '', 'year' => ''];
        }

        $start = self::startPrice($plan);
        if ($mode === self::YEARLY) {
            return [
                'priced' => true,
                'price' => self::money($f['once']),
                'suffix' => __('/ година'),
                'sub' => __('вместо :instead при месечно плащане', ['instead' => self::money($f['instead'])]),
                'year' => __(':total общо, от които :start старт и :sub абонамент за 12 месеца', ['total' => self::money($f['year']), 'start' => self::money($start), 'sub' => self::money($f['once'])]),
            ];
        }

        return [
            'priced' => true,
            'price' => self::money($f['monthly']),
            'suffix' => __('/ месец'),
            'sub' => '',
            // Текстът от пакета (ако собствениците са го написали) има предимство пред изчисления.
            'year' => filled($plan['year_total'] ?? null)
                ? $plan['year_total']
                : __(':total за първата година (:start + 11 × :monthly)', ['total' => self::money($f['year']), 'start' => self::money($start), 'monthly' => self::money($f['monthly'])]),
        ];
    }

    /**
     * What a package card on /paketi shows (owners, November 2026): a big „от €400“, a short caption, and only the yearly
     * subscription („€2 000 на година“, with „абонамент, 2 месеца безплатно“) or, for a custom plan, its text („Индивидуална оферта“).
     * The monthly price is not shown on the cards. Keys: from, caption, second, note.
     */
    public static function cardPrice(array $billing, array $plan, int $planIndex): array
    {
        $prices = array_values((array) ($plan['prices'] ?? []));
        $start = self::startPrice($plan);
        $monthly = self::monthlyPrice($plan);
        $out = ['from' => '', 'caption' => '', 'second' => '', 'note' => ''];

        $amount = $start ?? $monthly;
        if ($amount !== null) {
            $out['from'] = __('от') . ' ' . self::money($amount);
        }
        if ($start !== null && preg_match('/^€\s?\d[\d\s.,]*?\s*(?:\/\s*(?:месец|month))?\s+(.+)$/u', (string) ($prices[0] ?? ''), $m)) {
            $out['caption'] = $m[1];
        } elseif ($start === null && $monthly !== null) {
            $out['caption'] = __('на месец');
        }

        if ($billing['enabled'] && ($f = self::figures($billing, $plan, $planIndex, self::YEARLY))) {
            $out['second'] = self::money($f['once']) . ' ' . __('на година');
            $free = 12 - $billing['yearly_months'];
            $out['note'] = $free === 1 ? __('абонамент, 1 месец безплатно') : __('абонамент, :n месеца безплатно', ['n' => $free]);
        } elseif ($start === null && filled($prices[0] ?? null)) {
            $out['second'] = $prices[0];
        }

        return $out;
    }

    /** The short list of a card: `card_items` when set, otherwise the heads (text before „: “) of the first five included lines. */
    public static function cardItems(array $plan): array
    {
        $items = array_values(array_filter((array) ($plan['card_items'] ?? []), 'filled'));
        if (! $items) {
            $items = array_map(fn ($line) => str_contains($line, ': ') ? explode(': ', $line, 2)[0] : $line, array_merge((array) ($plan['build_items'] ?? []), (array) ($plan['monthly_items'] ?? [])));
        }

        return array_slice($items, 0, 5);
    }

    /**
     * Prices as plain text lines for Kreo, /llms.txt and the FAQ answer: per package the start price, how the start is paid,
     * the subscription and the yearly option.
     */
    public static function textLines(array $plans, bool $withAudience = false): array
    {
        $billing = self::config($plans);
        $lines = [];
        foreach (array_values($plans['items'] ?? []) as $i => $plan) {
            $parts = [implode(', ', array_filter((array) ($plan['prices'] ?? []), 'filled'))];
            if (filled($plan['deposit'] ?? null)) {
                $parts[] = \Illuminate\Support\Str::ucfirst(rtrim($plan['deposit'], '.'));
            }
            if ($billing['enabled'] && ($f = self::figures($billing, $plan, $i, self::YEARLY))) {
                $parts[] = __('При годишно плащане абонаментът е :once вместо :instead при месечно плащане', ['once' => self::money($f['once']), 'instead' => self::money($f['instead'])]);
            }
            $lines[] = '- ' . $plan['name'] . '. ' . implode('. ', $parts) . ($withAudience && filled($plan['audience'] ?? null) ? '. ' . $plan['audience'] : '');
        }

        return $lines;
    }
}
