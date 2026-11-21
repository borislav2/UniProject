{{-- Phone bar of the redesign (concept G): call (a round phone icon), the free website check and the quote, below md only; hidden while a form is in view (resources/js/g-bar.js). --}}
@php($gPhone = g_phones())
<div class="g-bar" data-g-bar>
    @if($gPhone)
        <a class="g-bar-call" href="tel:{{ preg_replace('/[^0-9+]/', '', $gPhone[0]) }}" aria-label="{{ __('Обадете се') }}" title="{{ __('Обадете се') }}"><svg width="20" height="20" viewBox="0 0 24 24" aria-hidden="true"><path d="M6.6 10.8a15.1 15.1 0 0 0 6.6 6.6l2.2-2.2c.3-.3.7-.4 1-.2 1.1.4 2.3.6 3.6.6.6 0 1 .4 1 1V20c0 .6-.4 1-1 1A17 17 0 0 1 3 4c0-.6.4-1 1-1h3.5c.6 0 1 .4 1 1 0 1.3.2 2.5.6 3.6.1.3 0 .7-.2 1z" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/></svg></a>
    @endif
    <a class="g-bar-check" href="{{ lroute('site-check') }}">{{ __('Проверете сайта си') }}</a>
    <a class="g-bar-offer" href="{{ g_offer_url() }}">{{ __('Изпратете запитване') }}</a>
</div>
