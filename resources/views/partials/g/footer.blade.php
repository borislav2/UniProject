{{-- Footer of the redesign (concept G): who we are in one line, how to reach us, the pages and the legal links. --}}
<footer class="g-ft">
    <div class="g-wrap">
        <div class="g-ft-top">
            <div class="g-ft-brand">
                <a class="g-logo" href="{{ lroute('home') }}" aria-label="{{ __('Creatium Lab, начало') }}">Creatium Lab<span class="g-dot" aria-hidden="true"></span></a>
                <p>{{ __('Дигитална агенция за бизнеси') }}</p>
            </div>
            <div class="g-ft-col">
                <p class="g-mono">{{ __('Контакти') }}</p>
                <ul>
                    <li><a href="mailto:{{ config('creatium.contact.email') }}">{{ config('creatium.contact.email') }}</a></li>
                    @foreach(config('creatium.contact.phones') as $phone)
                        <li><a href="tel:{{ preg_replace('/[^0-9+]/', '', $phone) }}">{{ $phone }}</a></li>
                    @endforeach
                    @if(config('creatium.contact.facebook'))
                        <li><a href="{{ config('creatium.contact.facebook') }}" target="_blank" rel="noopener">{{ __('Последвайте ни във Facebook') }}</a></li>
                    @endif
                </ul>
            </div>
            <div class="g-ft-col">
                <p class="g-mono">{{ __('Навигация') }}</p>
                <ul>
                    @foreach(array_merge([[__('Услуги'), lroute('services')], [__('Пакети'), lroute('packages')], [__('Проекти'), lroute('portfolio')]], $aboutLinks, [[__('Безплатна проверка на сайт'), lroute('site-check')]]) as [$label, $href])
                        <li><a href="{{ $href }}">{{ $label }}</a></li>
                    @endforeach
                </ul>
            </div>
        </div>

        @if($locale === 'bg')
            <nav class="g-ft-sites" aria-label="Сайт за бранш">
                <span class="g-mono">Изработка на сайт за</span>
                @foreach(config('creatium.industry_pages') as $industrySlug => $industry)
                    <a href="{{ route('industry', $industrySlug) }}">{{ $industry['menu'] }}</a>
                @endforeach
            </nav>
        @endif

        <div class="g-ft-legal">
            <p>&copy; {{ date('Y') }} Creatium Lab. {{ __('Всички права запазени.') }}</p>
            <div>
                <a href="{{ lroute('privacy') }}">{{ __('Политика за поверителност') }}</a>
                <a href="{{ lroute('terms') }}">{{ __('Общи условия') }}</a>
                <a href="{{ lroute('cookies') }}">{{ __('Политика за бисквитките') }}</a>
                @if(config('creatium.gtm_id'))
                    <a href="#" data-cookie-settings>{{ __('Настройки за бисквитки') }}</a>
                @endif
                @guest
                    <a href="{{ route('login') }}" rel="nofollow">{{ __('Вход за екипа') }}</a>
                @endguest
            </div>
        </div>
    </div>
</footer>
