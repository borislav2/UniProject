<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Remembers where a visitor came from (last non-direct touch) so a later
 * inquiry can be attributed to a marketing channel.
 */
class LeadAttribution
{
    public const SESSION_KEY = 'lead_attribution';

    public static function fromRequest(Request $request): ?array
    {
        $utm = [
            'utm_source' => self::clean($request->query('utm_source')),
            'utm_medium' => self::clean($request->query('utm_medium')),
            'utm_campaign' => self::clean($request->query('utm_campaign')),
        ];

        $referrerHost = null;
        $referer = $request->headers->get('referer');
        if ($referer) {
            $host = strtolower((string) parse_url($referer, PHP_URL_HOST));
            if ($host !== '' && $host !== strtolower($request->getHost())) {
                $referrerHost = Str::limit($host, 190, '');
            }
        }

        $clickId = match (true) {
            $request->filled('gclid') => 'gclid',
            $request->filled('fbclid') => 'fbclid',
            default => null,
        };

        if (! array_filter($utm) && ! $referrerHost && ! $clickId) {
            return null;
        }

        return $utm + ['referrer' => $referrerHost, 'click_id' => $clickId];
    }

    public static function channel(?array $data): string
    {
        if (! $data) {
            return 'Директно';
        }

        $source = strtolower((string) ($data['utm_source'] ?? ''));
        $referrer = (string) ($data['referrer'] ?? '');
        $clickId = $data['click_id'] ?? null;

        return match (true) {
            str_contains($source, 'instagram') || $source === 'ig' || str_contains($referrer, 'instagram.') => 'Instagram',
            str_contains($source, 'facebook') || $source === 'fb' || str_contains($referrer, 'facebook.') || str_contains($referrer, 'fb.') => 'Facebook',
            $clickId === 'fbclid' || $source === 'meta' => 'Meta реклама',
            str_contains($source, 'google') || $clickId === 'gclid' || str_contains($referrer, 'google.') => 'Google',
            $source !== '' => Str::limit(ucfirst($source), 60, ''),
            $referrer !== '' => $referrer,
            default => 'Директно',
        };
    }

    private static function clean(mixed $value): ?string
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        return Str::limit(trim(strip_tags($value)), 100, '');
    }
}
