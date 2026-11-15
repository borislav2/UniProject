<?php

namespace App\Support\Opportunities;

use App\Models\OutreachProspect;
use Carbon\CarbonInterface;

/**
 * Оценка 0 до 100 на възможност и причините за нея на български (виж creatium.opportunities за тежестите):
 *   съвпадение с услугите ни (CPV код или ключови думи) до 40, пазар до 20, език до 10, стойност до 15, срок до 10, възложител до 5.
 * Изтекъл срок = изключена (score null от forTender). Партньорите (агенции) имат своя оценка (partner()).
 */
class OpportunityScore
{
    /**
     * Какво от услугите ни е това: CPV и ключови думи. candidate = има смисъл да се пази.
     *
     * @param  array{title: string, text?: ?string, cpv?: array}  $d
     * @return array{candidate: bool, points: int, reasons: list<array{label: string, points: int}>, topic: ?string, cpv: list<string>}
     */
    public static function relevance(array $d): array
    {
        $cpvNames = config('creatium.opportunities.cpv');
        $weights = config('creatium.opportunities.cpv_weight');
        $matched = array_values(array_filter((array) ($d['cpv'] ?? []), fn ($code) => isset($cpvNames[$code])));
        $kw = Keywords::match($d['title'] ?? '', $d['text'] ?? '');

        $cpvPoints = 0;
        $best = null;
        foreach ($matched as $code) {
            if (($weights[$code] ?? 20) > $cpvPoints) {
                $cpvPoints = (int) ($weights[$code] ?? 20);
                $best = $code;
            }
        }
        $kwPoints = $kw['strong_title'] ? 35 : ($kw['strong_text'] ? 22 : ($kw['weak_title'] ? 12 : 0));

        $reasons = [];
        if ($best) {
            $reasons[] = ['label' => 'CPV ' . $best . ' (' . $cpvNames[$best] . '): ' . ($cpvPoints >= 36 ? 'точно наша услуга' : 'близо до услугите ни'), 'points' => $cpvPoints];
        }
        if ($kw['strong_title']) {
            $reasons[] = ['label' => 'Заглавието съдържа „' . $kw['strong_title'][0] . '“', 'points' => $best ? 5 : 35];
        } elseif ($kw['strong_text']) {
            $reasons[] = ['label' => 'В описанието има „' . $kw['strong_text'][0] . '“', 'points' => $best ? 3 : 22];
        } elseif ($kw['weak_title'] && ! $best) {
            $reasons[] = ['label' => 'Заглавието е общо („' . $kw['weak_title'][0] . '“), проверете описанието', 'points' => 12];
        }

        $points = min(40, $best ? $cpvPoints + ($kwPoints >= 22 ? 5 : 0) : $kwPoints);
        $topic = self::topic($best, $kw['topics']);

        return [
            'candidate' => (bool) $best || $kwPoints > 0,
            'points' => $points,
            'reasons' => $reasons,
            'topic' => $topic,
            'cpv' => $matched,
        ];
    }

    /** web, seo, marketing, social, design, pr или null. */
    public static function topic(?string $cpv, array $keywordTopics = []): ?string
    {
        if ($cpv) {
            return match (true) {
                str_starts_with($cpv, '724'), str_starts_with($cpv, '722') => 'web',
                str_starts_with($cpv, '79416') => 'pr',
                str_starts_with($cpv, '79822') => 'design',
                str_starts_with($cpv, '793'), str_starts_with($cpv, '79413') => 'marketing',
                default => $keywordTopics[0] ?? null,
            };
        }

        return $keywordTopics[0] ?? null;
    }

    /**
     * Оценка на обществена поръчка (или ръчно добавена възможност със същите полета).
     * Ключове: title, text, cpv, country, language, value_min, value_max, currency, deadline_at, sme, framework, buyer_type.
     * Връща null, ако срокът е изтекъл (не се пази).
     *
     * @return array{score: int, reasons: list<array{label: string, points: int}>, topic: ?string, cpv: list<string>, candidate: bool}|null
     */
    public static function forTender(array $d, ?CarbonInterface $now = null): ?array
    {
        $now ??= now();
        $deadline = $d['deadline_at'] ?? null;
        if ($deadline && $deadline->lessThanOrEqualTo($now)) {
            return null;
        }

        $relevance = self::relevance($d);
        $reasons = $relevance['reasons'];
        $total = $relevance['points'];

        [$market, $marketLabel] = self::market($d['country'] ?? null);
        $total += $market;
        $reasons[] = ['label' => 'Пазар: ' . $marketLabel, 'points' => $market];

        [$lang, $langLabel] = self::language($d['language'] ?? null);
        $total += $lang;
        $reasons[] = ['label' => $langLabel, 'points' => $lang];

        [$value, $valueLabel] = self::value($d);
        $total += $value;
        $reasons[] = ['label' => $valueLabel, 'points' => $value];

        [$time, $timeLabel] = self::deadline($deadline, $now);
        $total += $time;
        $reasons[] = ['label' => $timeLabel, 'points' => $time];

        [$buyer, $buyerLabel] = self::buyer($d);
        $total += $buyer;
        $reasons[] = ['label' => $buyerLabel, 'points' => $buyer];

        return [
            'score' => max(0, min(100, $total)),
            'reasons' => array_values(array_filter($reasons, fn ($r) => $r['label'] !== '')),
            'topic' => $relevance['topic'],
            'cpv' => $relevance['cpv'],
            'candidate' => $relevance['candidate'],
        ];
    }

    /** @return array{0: int, 1: string} */
    public static function market(?string $country): array
    {
        $country = strtoupper((string) $country);
        $markets = config('creatium.opportunities.markets');
        $name = Countries::name($country ?: null);

        return match (true) {
            $country === '' => [0, 'държавата не е ясна'],
            in_array($country, $markets['high'], true) => [20, $name . ', основен пазар'],
            in_array($country, $markets['medium'], true) => [13, $name . ', немскоезичен пазар, Владимир работи на немски'],
            in_array($country, $markets['eu'], true) => [6, $name . ', друга държава от ЕС'],
            default => [2, $name . ', извън основните пазари'],
        };
    }

    /** @return array{0: int, 1: string} */
    public static function language(?string $language): array
    {
        $language = strtolower((string) $language);
        if ($language === '') {
            return [5, 'Езикът не е ясен'];
        }
        $ours = config('creatium.opportunities.languages');

        return in_array($language, $ours, true)
            ? [10, 'Езикът е ' . Countries::languageName($language) . ', работим на него']
            : [2, 'Езикът е ' . Countries::languageName($language) . ': ще трябва превод или партньор'];
    }

    /** Стойността в евро (най-горната известна, иначе най-долната) или null. */
    public static function euros(array $d): ?float
    {
        $amount = $d['value_max'] ?? $d['value_min'] ?? null;
        if ($amount === null || (float) $amount <= 0) {
            return null;
        }
        $rate = config('creatium.opportunities.rates')[strtoupper((string) ($d['currency'] ?: 'EUR'))] ?? null;

        return $rate ? (float) $amount * $rate : null;
    }

    /** @return array{0: int, 1: string} */
    public static function value(array $d): array
    {
        $eur = self::euros($d);
        $band = config('creatium.opportunities.value_band');
        if ($eur === null) {
            return [6, 'Стойността не е обявена'];
        }
        $shown = '€' . number_format($eur, 0, ',', ' ');

        return match (true) {
            $eur < $band['low'] => [4, "Стойност около {$shown}: твърде дребна"],
            $eur <= $band['high'] => [15, "Стойност около {$shown}: подходяща за малка агенция"],
            $eur <= $band['big'] => [9, "Стойност около {$shown}: голяма, но възможна"],
            $eur <= $band['huge'] => [4, "Стойност около {$shown}: голяма за трима души"],
            default => [1, "Стойност около {$shown}: най-вероятно голямо рамково споразумение"],
        };
    }

    /** @return array{0: int, 1: string} */
    public static function deadline(?CarbonInterface $deadline, CarbonInterface $now): array
    {
        if (! $deadline) {
            return [5, 'Срокът не е обявен'];
        }
        $days = (int) floor($now->floatDiffInDays($deadline));

        return match (true) {
            $days < 3 => [1, 'Срок след ' . max(0, $days) . ' дни: твърде кратко за оферта'],
            $days < 7 => [4, "Срок след {$days} дни: тесен"],
            $days < 14 => [8, "Срок след {$days} дни"],
            default => [10, "Срок след {$days} дни: има време за подготовка"],
        };
    }

    /** @return array{0: int, 1: string} */
    public static function buyer(array $d): array
    {
        if (($d['sme'] ?? null) === true) {
            return [5, 'Обявена като подходяща за малки и средни предприятия'];
        }
        if (in_array($d['buyer_type'] ?? null, ['la', 'ra', 'LOCAL_AUTHORITY', 'REGIONAL_AUTHORITY', 'body-pl'], true)) {
            return [3, 'Местен или регионален възложител'];
        }
        if (! empty($d['framework'])) {
            return [0, 'Рамково споразумение: по-трудно за малка агенция'];
        }

        return [1, ''];
    }

    /**
     * Оценка на агенция партньор (от търсенето на бизнеси): пазар, качество на имейла, телефон, сайт и дали по правилата за
     * канала може да се пише по имейл (иначе обаждане или LinkedIn).
     *
     * @return array{score: int, reasons: list<array{label: string, points: int}>}
     */
    public static function partner(OutreachProspect $prospect): array
    {
        $reasons = [['label' => 'Агенция: може да е партньор за бял етикет', 'points' => 30]];
        $total = 30;

        [$market, $marketLabel] = self::market($prospect->country);
        $total += $market;
        $reasons[] = ['label' => 'Пазар: ' . $marketLabel, 'points' => $market];

        $email = ['verified' => 15, 'published' => 12, 'risky' => 5][$prospect->email_status] ?? 0;
        $total += $email;
        $reasons[] = ['label' => 'Имейл: ' . (OutreachProspect::EMAIL_STATUSES[$prospect->email_status] ?? 'непроверен'), 'points' => $email];

        if ($prospect->website) {
            $total += 10;
            $reasons[] = ['label' => 'Има сайт, виждат се примери и услуги', 'points' => 10];
        }
        if (filled($prospect->phone)) {
            $total += 5;
            $reasons[] = ['label' => 'Има телефон', 'points' => 5];
        }
        $allowed = $prospect->canBeEmailed();
        $total += $allowed ? 10 : 0;
        $reasons[] = ['label' => $allowed ? 'По правилата за канала може да се пише по имейл' : 'Без имейл по правилата за канала: обаждане или LinkedIn', 'points' => $allowed ? 10 : 0];

        return ['score' => max(0, min(100, $total)), 'reasons' => $reasons];
    }
}
