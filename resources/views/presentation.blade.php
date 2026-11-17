@php
    // Слайдове 1600×900 в стила на дизайн Г (светли и мастилени, квадратни, един акцент). Същото съдържание като на сайта (site()), затова промените от админа влизат и тук.
    // Показва се винаги в новия вид, при всеки creatium.design. PDF: node tools/presentation/render.mjs (вижте файла). Страницата е noindex.
    $title = e($hero['title']);
    foreach (array_filter((array) ($hero['accent'] ?? [])) as $word) {
        $title = preg_replace('/(?<![\p{L}])(' . preg_quote(e($word), '/') . ')(?![\p{L}])/u', '<span class="gp-accent">$1</span>', $title, 1);
    }
    $engineNodes = array_values(array_map(function ($card) {
        preg_match('/^(.+?)\s*\((.+)\)\s*$/u', $card['label'], $m);
        return ['icon' => $card['icon'], 'name' => $m[1] ?? $card['label'], 'tag' => $m[2] ?? null];
    }, $engineCards));
    // Домейнът от имейла (creatiumlab.com), за да е верен и когато PDF-ът се прави от локален сървър.
    $site = \Illuminate\Support\Str::after($contact['email'], '@');
    $slides = 10;
    $page = 1;
    $n = function () use (&$page, $slides) { return str_pad((string) ++$page, 2, '0', STR_PAD_LEFT) . ' / ' . str_pad((string) $slides, 2, '0', STR_PAD_LEFT); };
@endphp
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ __('Презентация на Creatium Lab') }}</title>
    @vite(['resources/css/presentation.css'])
</head>
<body class="gp-deck">

{{-- 1. Cover --}}
<section class="gp-slide is-ink">
    <div class="gp-pad is-between">
        <img src="{{ asset('images/logo-g-dark-352.webp') }}" alt="Creatium Lab" class="gp-logo">
        <div>
            @if(filled($trust['eyebrow'] ?? null))<p class="gp-k">{{ $trust['eyebrow'] }}</p>@endif
            <h1 class="gp-h is-xl">{!! $title !!}</h1>
            <p class="gp-lead">{{ $hero['highlight'] }}</p>
        </div>
        <div style="display:flex;gap:64px;font-size:22px;">
            <span style="font-weight:500;">{{ $site }}</span>
            <span style="color:var(--on-ink-mute);">{{ $contact['email'] }}</span>
            <span style="color:var(--on-ink-mute);">{{ $contact['phones'][0] ?? '' }}</span>
        </div>
    </div>
</section>

{{-- 2. Problem --}}
<section class="gp-slide">
    <div class="gp-pad">
        <p class="gp-k">{{ __('Проблемът') }}</p>
        <h2 class="gp-h is-l">{{ $problem['title'] }}</h2>
        <div class="gp-grid c3 gp-push">
            @foreach($problem['scenarios'] as $scenario)
                <div class="gp-card {{ $loop->last ? 'is-solid' : '' }}">
                    <span class="gp-k">{{ $scenario['label'] }}</span>
                    <h3 style="margin-top:20px;">{{ $scenario['title'] }}</h3>
                    <p>{{ $scenario['text'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
    <div class="gp-foot"><span>Creatium Lab</span><span>{{ $n() }}</span></div>
</section>

{{-- 3. The engine --}}
<section class="gp-slide is-ink">
    <div class="gp-pad">
        <p class="gp-k">{{ $trust['engine_eyebrow'] }}</p>
        <h2 class="gp-h is-l">{{ $engine['title'] }}</h2>
        <div class="gp-engine" style="margin-bottom:24px;">
            <svg viewBox="0 0 100 100" preserveAspectRatio="none" aria-hidden="true"><g stroke="rgba(236,238,240,.35)" stroke-width="1" fill="none"><path d="M50 50 L22 22" vector-effect="non-scaling-stroke"/><path d="M50 50 L78 22" vector-effect="non-scaling-stroke"/><path d="M50 50 L22 78" vector-effect="non-scaling-stroke"/><path d="M50 50 L78 78" vector-effect="non-scaling-stroke"/></g></svg>
            <div class="gp-core">
                <span class="gp-core-ico"><x-icon name="cogs" /></span>
                <span>{{ $trust['engine_center'] }}</span>
            </div>
            @foreach($engineNodes as $node)
                <div class="gp-node">
                    <span class="ico"><x-icon :name="$node['icon']" /></span>
                    <span>
                        <span class="gp-num" style="display:block;margin-bottom:6px;">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                        <b>{{ $node['name'] }}</b>
                        @if($node['tag'])<small>{{ $node['tag'] }}</small>@endif
                    </span>
                </div>
            @endforeach
        </div>
    </div>
    <div class="gp-foot"><span>Creatium Lab</span><span>{{ $n() }}</span></div>
</section>

{{-- 4. What is included --}}
<section class="gp-slide is-surface">
    <div class="gp-pad is-dense">
        <p class="gp-k">{{ __('Какво включва') }}</p>
        <div class="gp-grid c2" style="margin-top:22px;flex:1;">
            @foreach(['tech', 'growth'] as $group)
                <div class="gp-card is-tight">
                    <p class="gp-k">{{ $engine[$group . '_tag'] }}</p>
                    <h3 style="margin-top:10px;">{{ $engine[$group . '_title'] }}</h3>
                    <ul class="gp-list" style="margin-top:22px;">
                        @foreach($engine[$group . '_items'] as $item)
                            <li>
                                <span class="tick"><x-icon name="check" /></span>
                                <span><b>{{ $item['title'] }}</b><span class="t">{{ $item['text'] }}</span></span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>
    </div>
    <div class="gp-foot"><span>Creatium Lab</span><span>{{ $n() }}</span></div>
</section>

{{-- 5. Who we work with --}}
<section class="gp-slide">
    <div class="gp-pad">
        <div style="display:grid;grid-template-columns:5fr 7fr;gap:72px;flex:1;">
            <div>
                <p class="gp-k">{{ $trust['fit_eyebrow'] }}</p>
                <h2 class="gp-h is-l">{{ $trust['fit_title'] }}</h2>
                <p class="gp-lead" style="font-size:25px;color:var(--mute);">{{ $trust['fit_text'] }}</p>
            </div>
            <ol class="gp-rows" style="align-self:center;">
                @foreach($trust['fit_items'] as $item)
                    <li>
                        <span class="gp-num">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                        <span><b>{{ $item['title'] }}</b><span class="t">{{ $item['text'] }}</span></span>
                    </li>
                @endforeach
            </ol>
        </div>
    </div>
    <div class="gp-foot"><span>Creatium Lab</span><span>{{ $n() }}</span></div>
</section>

{{-- 6. Process --}}
<section class="gp-slide is-ink">
    <div class="gp-pad">
        <p class="gp-k">{{ __('Работен процес') }}</p>
        <h2 class="gp-h is-l">{{ $process['title'] }}</h2>
        <div class="gp-grid c3 is-steps">
            @foreach($process['steps'] as $step)
                <div class="gp-card">
                    <span class="gp-num" style="font-size:22px;">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                    <h3>{{ $step['title'] }}</h3>
                    <p>{{ $step['text'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
    <div class="gp-foot"><span>Creatium Lab</span><span>{{ $n() }}</span></div>
</section>

{{-- 7. Packages --}}
<section class="gp-slide is-surface">
    <div class="gp-pad">
        <p class="gp-k">{{ __('Пакети') }}</p>
        <h2 class="gp-h is-l">{{ __('Три пакета с ясни цени.') }}</h2>
        <div class="gp-grid c3 gp-push">
            @foreach($plans['items'] as $plan)
                <div class="gp-plan {{ ! empty($plan['highlighted']) ? 'is-hi' : '' }}">
                    @if(filled($plan['badge'] ?? null))<span class="gp-badge">{{ $plan['badge'] }}</span>@else<span class="gp-badge-gap"></span>@endif
                    <h3>{{ $plan['name'] }}</h3>
                    <p class="aud">{{ $plan['audience'] }}</p>
                    @php($card = \App\Support\PlanBilling::cardPrice(\App\Support\PlanBilling::config($plans), $plan, $loop->index))
                    <p class="price">{{ $card['from'] }}</p>
                    @if($card['caption'] !== '')<p class="cap">{{ $card['caption'] }}</p>@endif
                    @if($card['second'] !== '')
                        <p class="second">{{ $card['second'] }}</p>
                        @if($card['note'] !== '')<p class="note">{{ $card['note'] }}</p>@endif
                    @endif
                </div>
            @endforeach
        </div>
    </div>
    <div class="gp-foot"><span>Creatium Lab</span><span>{{ $n() }}</span></div>
</section>

{{-- 8. Industries (demos) --}}
<section class="gp-slide is-ink">
    <div class="gp-pad">
        <div style="display:flex;align-items:flex-end;justify-content:space-between;gap:40px;">
            <div>
                <p class="gp-k">{{ $trust['demos_eyebrow'] }}</p>
                <h2 class="gp-h is-l">{{ $trust['demos_title'] }}</h2>
            </div>
            <p style="max-width:34rem;font-size:19px;line-height:1.55;color:var(--on-ink-mute);">{{ $trust['demos_text'] }}</p>
        </div>
        <div class="gp-grid c3 is-demos">
            @foreach(array_slice($demos, 0, 6) as $demo)
                <div class="gp-demo">
                    <div class="shot">
                        @if(is_file(public_path('images/demos/' . $demo['slug'] . '.webp')))
                            <img src="{{ demo_img($demo['slug']) }}" alt="">
                        @endif
                    </div>
                    <div class="meta"><span class="gp-k">{{ $demo['industry'] }}</span><b>{{ $demo['name'] }}</b></div>
                </div>
            @endforeach
        </div>
    </div>
    <div class="gp-foot"><span>Creatium Lab</span><span>{{ $n() }}</span></div>
</section>

{{-- 9. Principles and team --}}
<section class="gp-slide">
    <div class="gp-pad">
        <div style="display:grid;grid-template-columns:7fr 5fr;gap:64px;flex:1;">
            <div>
                <p class="gp-k">{{ $trust['principles_eyebrow'] }}</p>
                <h2 class="gp-h is-m">{{ $trust['principles_title'] }}</h2>
                <div class="gp-grid c2" style="margin-top:44px;gap:36px 44px;">
                    @foreach($trust['principle_items'] as $item)
                        <div class="gp-pr">
                            <span class="gp-num">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                            <h3>{{ $item['title'] }}</h3>
                            <p>{{ $item['text'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
            <div style="display:flex;flex-direction:column;gap:20px;justify-content:center;">
                @foreach($team as $member)
                    <div class="gp-person">
                        @if(filled($member['image'] ?? null) && is_file(public_path($member['image'])))
                            <img src="{{ asset($member['image']) }}" alt="">
                        @endif
                        <div>
                            <b>{{ $member['name'] }}</b>
                            <span>{{ $member['role'] }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    <div class="gp-foot"><span>Creatium Lab</span><span>{{ $n() }}</span></div>
</section>

{{-- 10. Contact --}}
<section class="gp-slide is-ink">
    <div class="gp-pad is-between">
        <img src="{{ asset('images/logo-g-dark-352.webp') }}" alt="Creatium Lab" class="gp-logo">
        <div>
            <p class="gp-k">{{ $trust['contact_eyebrow'] }}</p>
            <h2 class="gp-h is-xl" style="font-size:80px;">{{ __('Разговор с нас за вашия бизнес.') }}</h2>
            <p class="gp-lead" style="font-size:26px;max-width:56rem;">{{ __(':minutes минути разговор. Разглеждаме сайта и присъствието ви в Google и ви казваме какво бихме направили първо.', ['minutes' => config('creatium.booking.minutes')]) }}</p>
        </div>
        <div class="gp-grid c3 is-contact" style="gap:24px;">
            <div class="gp-contact"><p class="gp-k">{{ __('Сайт') }}</p><p class="v">{{ $site }}</p></div>
            <div class="gp-contact"><p class="gp-k">{{ __('Имейл') }}</p><p class="v">{{ $contact['email'] }}</p></div>
            <div class="gp-contact"><p class="gp-k">{{ __('Телефон') }}</p><p class="v">{{ implode(' · ', $contact['phones']) }}</p></div>
        </div>
    </div>
</section>

</body>
</html>
