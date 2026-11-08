{{-- Cloudflare Turnstile (само когато ключовете са в .env). Скриптът се зарежда веднъж на страница. --}}
@if(\App\Support\Turnstile::enabled())
    <div>
        <div class="cf-turnstile" data-sitekey="{{ config('services.turnstile.site_key') }}" data-language="{{ app()->getLocale() }}" data-theme="auto" data-size="flexible"></div>
        @error(\App\Support\Turnstile::FIELD)<p class="text-red-600 text-sm mt-1 dark:text-red-400">{{ $message }}</p>@enderror
    </div>
    @once
        <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
    @endonce
@endif
