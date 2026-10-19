<?php

namespace App\Support\Outreach;

use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Проверява намерен адрес преди да се допусне до изпращане.
 * С REOON_API_KEY: verified / risky / invalid от Reoon. Без ключ: „published“, ако домейнът приема поща (MX),
 * иначе „guess“ (не се праща).
 */
class EmailVerifier
{
    public function status(string $email): string
    {
        $domain = substr(strrchr($email, '@'), 1);
        if (! $this->acceptsMail($domain)) {
            return 'guess';
        }

        if ($key = config('services.reoon.key')) {
            $verdict = $this->reoon($email, $key);
            if ($verdict) {
                return $verdict;
            }
        }

        return 'published';
    }

    protected function acceptsMail(string $domain): bool
    {
        return checkdnsrr($domain, 'MX') || checkdnsrr($domain, 'A');
    }

    protected function reoon(string $email, string $key): ?string
    {
        try {
            $response = Http::timeout(30)->get('https://emailverifier.reoon.com/api/v1/verify', ['email' => $email, 'key' => $key, 'mode' => 'power']);
        } catch (Throwable) {
            return null;
        }
        $status = strtolower((string) $response->json('status'));

        return match (true) {
            ! $response->successful() => null,
            (bool) $response->json('is_catch_all'), $status === 'catch_all' => 'risky',
            $status === 'valid' => 'verified',
            in_array($status, ['invalid', 'disabled'], true) => 'invalid',
            default => null,
        };
    }
}
