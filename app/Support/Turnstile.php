<?php

namespace App\Support;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Cloudflare Turnstile: защита на публичните форми от ботове (без бисквитки за реклама).
 * Включва се само когато и двата ключа са в .env; без тях формите работят както преди.
 */
class Turnstile
{
    public const FIELD = 'cf-turnstile-response';

    public const VERIFY_URL = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';

    public static function enabled(): bool
    {
        return filled(config('services.turnstile.site_key')) && filled(config('services.turnstile.secret_key'));
    }

    /** Правила за валидиране на полето с отговора; без ключове полето не се изисква. */
    public static function rules(): array
    {
        if (! self::enabled()) {
            return ['nullable'];
        }

        return ['required', 'string', 'max:2048', function ($attribute, $value, $fail) {
            if (! self::verify((string) $value, request()->ip())) {
                $fail(__('Проверката срещу ботове не мина. Моля, опитайте отново.'));
            }
        }];
    }

    public static function messages(): array
    {
        return [self::FIELD . '.required' => __('Моля, изчакайте проверката срещу ботове да завърши и опитайте отново.')];
    }

    /**
     * Пита Cloudflare дали отговорът е истински. Ако Cloudflare не отговаря (мрежа, 5xx),
     * пропускаме, за да не загубим истинско запитване; honeypot и лимитите остават.
     */
    public static function verify(string $token, ?string $ip = null): bool
    {
        try {
            $response = Http::asForm()->timeout(8)->post(self::VERIFY_URL, array_filter([
                'secret' => config('services.turnstile.secret_key'),
                'response' => $token,
                'remoteip' => $ip,
            ]));
        } catch (\Throwable $e) {
            Log::warning('Turnstile verify failed: ' . $e->getMessage());

            return true;
        }

        if ($response->serverError()) {
            Log::warning('Turnstile verify returned ' . $response->status());

            return true;
        }

        return $response->json('success') === true;
    }
}
