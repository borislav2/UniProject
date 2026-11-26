<?php

namespace App\Support\SiteCheck;

use App\Models\Project;
use Illuminate\Support\Facades\URL;

/**
 * Платените стъпки след безплатната проверка: „Оправяме трите най-важни проблема“ (fix) и следене на сайта (monitor).
 * Числата са в config creatium.site_check.offers, текстовете в site('site_check_offers') (BG и EN). Линковете в имейлите са подписани
 * и валидни 60 дни; те само показват страница за потвърждение, самата поръчка е POST (SiteCheckOfferController).
 */
class Offers
{
    public const KEYS = ['fix', 'monitor'];

    public const LINK_DAYS = 60;

    public static function price(string $offer): int
    {
        return (int) config("creatium.site_check.offers.$offer.price");
    }

    /** „€190“ */
    public static function money(int $amount): string
    {
        return '€' . number_format($amount, 0, '', ' ');
    }

    /** Текстовете на офертата в езика на страницата. */
    public static function text(string $offer): array
    {
        return (array) site("site_check_offers.$offer");
    }

    /** Попълва :price, :days и :credit_days в текст от site_check_offers. */
    public static function fill(string $text, string $offer = 'fix'): string
    {
        return strtr($text, [
            ':price' => self::money(self::price($offer)),
            ':days' => (string) config('creatium.site_check.offers.fix.days'),
            ':credit_days' => (string) config('creatium.site_check.offers.fix.credit_days'),
        ]);
    }

    /** Ред „€190, еднократно“ или „€29 на месец“. */
    public static function priceLine(string $offer): string
    {
        return self::fill((string) (self::text($offer)['price_line'] ?? ''), $offer);
    }

    /** Подписаният линк за поръчка в езика на запитването (или на $locale). */
    public static function url(Project $project, string $offer, ?string $locale = null): string
    {
        $locale ??= $project->locale ?: 'bg';

        return URL::temporarySignedRoute(($locale === 'en' ? 'en.' : '') . 'site-check.offer', now()->addDays(self::LINK_DAYS), ['project' => $project->id, 'offer' => $offer]);
    }

    /** Подписаният линк за отписване от писмата след проверката (без срок). */
    public static function unsubscribeUrl(Project $project): string
    {
        return URL::signedRoute('site-check.unsubscribe', ['project' => $project->id]);
    }

    /** Трите най-важни неща от записания отчет: [заглавие, обяснение, ...] от SiteAudit::items(), без бележките. */
    public static function topIssues(Project $project, int $n = 3): array
    {
        $report = $project->site_check_report;
        if (! is_array($report) || ! isset($report['score'])) {
            return [];
        }

        return Areas::top(SiteAudit::items($report + ['reachable' => true]), $n);
    }
}
