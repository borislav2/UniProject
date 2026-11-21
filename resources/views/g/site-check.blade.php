@extends('layouts.public')

{{-- /proverka-na-sait in design G (owners' brief, 18.11.2026, section 9). A dark head with the headline and the request form beside it, the report mock-up with the four
     things we get for the client, the four points of the check and the FAQ. The check is both automatic (the report by email within minutes) and personal
     (a specialist writes within two working days, see App\Support\SiteCheck\ReviewDue). All texts come from config site_check_page (BG and EN). --}}
@php
    $sc = (array) site('site_check_page');
    $goals = (array) ($sc['goals'] ?? []);
    $faq = array_values((array) ($sc['faq'] ?? []));
    $different = array_values((array) ($sc['different'] ?? []));
@endphp
@section('title', __('Безплатна проверка на сайт за SEO, скорост и Google'))
@section('meta_description', $sc['meta_description'] ?? '')
@section('g_lean', 'inner')

@section('content')
<section class="g-ph g-ph-dark g-ph-vis-on g-ph-check">
    <div class="g-wrap">
        <div class="g-ph-text">
            <h1 class="g-h">{{ $sc['title'] ?? '' }}</h1>
            <p class="g-lead">{{ $sc['text'] ?? '' }}</p>
        </div>
        <div class="g-ph-vis">
            <div id="site-check-form" class="g-fbox">
                @if(session('site_check_sent'))
                    <div class="g-thanks" role="status" tabindex="-1" data-g-thanks>
                        <p class="g-mono">{{ __('Изпратено') }}</p>
                        <h2 class="g-h">{{ $sc['thanks_title'] ?? '' }}</h2>
                        <p>{{ strtr($sc['thanks_text'] ?? '', [':domain' => session('site_check_domain', ''), ':email' => session('site_check_sent')]) }}</p>
                        <a class="g-btn g-ghost" href="{{ lroute('site-check') }}">{{ __('Проверете друг сайт') }}</a>
                    </div>
                    <script>(function () { var t = document.querySelector('#site-check-form [data-g-thanks]'); if (t) { t.scrollIntoView({ block: 'center' }); t.focus({ preventScroll: true }); } })();</script>
                @else
                    <form action="{{ lroute('site-check.submit') }}" method="POST" class="g-f" novalidate>
                        @csrf
                        <div class="g-trap" aria-hidden="true">
                            <label for="website">{{ __('Не попълвайте това поле') }}</label>
                            <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
                        </div>
                        <div class="g-fld">
                            <label for="domain">{{ $sc['domain_label'] ?? '' }}</label>
                            <input class="g-in" type="text" id="domain" name="domain" value="{{ old('domain') }}" required inputmode="url" autocomplete="url" autocapitalize="off" spellcheck="false" placeholder="{{ __('vashiyatsait.bg') }}"@error('domain') aria-invalid="true" aria-describedby="err-domain"@enderror>
                            @error('domain')<p class="g-err" id="err-domain">{{ $message }}</p>@enderror
                        </div>
                        <div class="g-fld">
                            <label for="email">{{ $sc['email_label'] ?? '' }}</label>
                            <input class="g-in" type="email" id="email" name="email" value="{{ old('email') }}" required autocomplete="email"@error('email') aria-invalid="true" aria-describedby="err-email"@enderror>
                            @error('email')<p class="g-err" id="err-email">{{ $message }}</p>@enderror
                        </div>
                        <fieldset class="g-fld g-opts"@error('goal') data-g-invalid tabindex="-1" aria-describedby="err-goal"@enderror>
                            <legend>{{ $sc['goal_label'] ?? '' }}</legend>
                            <div class="g-chips g-chips-goal">
                                @foreach($goals as $key => $label)
                                    <label class="g-chip-opt"><input type="radio" name="goal" value="{{ $key }}" @checked((string) old('goal') === (string) $key)><span>{{ $label }}</span></label>
                                @endforeach
                            </div>
                            @error('goal')<p class="g-err" id="err-goal">{{ $message }}</p>@enderror
                        </fieldset>
                        <div class="g-fld">
                            <label class="g-chk">
                                <input type="checkbox" name="consent" value="1" {{ old('consent') ? 'checked' : '' }}@error('consent') aria-invalid="true" aria-describedby="err-consent"@enderror>
                                <span>{!! __('Съгласявам се да ми изпратите отчета на този имейл и да се свържете с мен за него. Прочетох :link.', ['link' => '<a href="' . e(lroute('privacy')) . '" target="_blank">' . e(__('Политиката за поверителност')) . '</a>']) !!}</span>
                            </label>
                            @error('consent')<p class="g-err" id="err-consent">{{ $message }}</p>@enderror
                        </div>
                        @include('partials.turnstile')
                        <button type="submit" class="g-btn g-big">{{ $sc['button'] ?? '' }}</button>
                        <p class="g-promise">{{ $sc['promise'] ?? '' }}</p>
                    </form>
                    @include('partials.g.form-feedback', ['box' => '#site-check-form'])
                @endif
            </div>
        </div>
    </div>
</section>

<section class="g-s">
    <div class="g-wrap g-sys">
        <div class="g-sh">
            <p class="g-mono g-mute">{{ $sc['different_label'] ?? '' }}</p>
            <h2 class="g-h">{{ $sc['different_title'] ?? '' }}</h2>
            <div class="g-check-vis">@include('partials.g.site-check-visual', ['kind' => 'report'])</div>
        </div>
        <ol class="g-rows g-rows-tight g-numbered">
            @foreach($different as $item)
                <li><span class="g-n g-mono" aria-hidden="true">{{ sprintf('%02d', $loop->iteration) }}</span><h3>{{ $item['title'] ?? '' }}</h3><p>{{ $item['text'] ?? '' }}</p></li>
            @endforeach
        </ol>
    </div>
</section>

<section class="g-s g-last">
    <div class="g-wrap g-sys">
        <div class="g-sh">
            <div class="g-check-vis">@include('partials.g.site-check-visual', ['kind' => 'points'])</div>
        </div>
        <div>
            <h2 class="g-h g-sub-h g-faq-h">{{ $sc['faq_title'] ?? '' }}</h2>
            @include('partials.g.faq', ['items' => $faq])
        </div>
    </div>
</section>

{{-- '@' . 'context': Blade иначе чете @context като своя директива и поврежда ключа. --}}
<script type="application/ld+json">
{!! json_encode([
    '@' . 'context' => 'https://schema.org',
    '@type' => 'FAQPage',
    'mainEntity' => array_map(fn ($qa) => ['@type' => 'Question', 'name' => $qa['q'], 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $qa['a']]], $faq),
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}
</script>
@endsection
