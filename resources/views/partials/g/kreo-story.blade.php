{{--
    Home page of design G, „Как работим“: the four steps of the website service (service_pages.website.g_process, BG and EN, the same texts as the service page),
    told with Kreo. A normal ordered list with a pre-rendered still above each step is the base (phones, reduced motion, no WebGL, no JavaScript). On a wide screen
    g-kreo.js switches the section to a sticky story: the list stays readable text on the left (one step at a time), one WebGL canvas (aria-hidden) on the right.
    The 3D code is loaded only when the section is near the screen. Nothing is invented here: no new copy, no figures.
--}}
@php
    $kreoPage = (array) (site('service_pages')['website'] ?? []);
    $kreoSteps = collect((array) ($kreoPage['g_process'] ?? []))->filter(fn ($step) => filled($step['title'] ?? null))->take(4)->values();
    $kreoLang = app()->getLocale() === 'en' ? 'en' : 'bg';
@endphp
@if($kreoSteps->count() === 4)
<section class="g-s g-kreo" id="kak-rabotim" aria-labelledby="kak-rabotim-h" data-g-kreo data-site="{{ demo_img('restorant') }}">
    <div class="g-wrap">
        <div class="g-kreo-story" data-g-kreo-story>
            <div class="g-kreo-stick" data-g-kreo-stick>
                <div class="g-kreo-text">
                    <h2 class="g-h" id="kak-rabotim-h">{{ $kreoPage['g_process_title'] ?? $kreoSteps[0]['title'] }}</h2>
                    <ol class="g-kreo-steps">
                        @foreach($kreoSteps as $step)
                            <li class="g-kreo-step{{ $loop->first ? ' is-active' : '' }}" data-g-step data-short="{{ $step['short'] ?? '' }}">
                                <figure class="g-kreo-still">
                                    @include('partials.g.kreo-still', ['base' => 'kreo-' . $loop->iteration . ($loop->first ? '-' . $kreoLang : ''), 'alt' => $step['title']])
                                </figure>
                                <span class="g-n g-mono">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                                <h3>{{ $step['title'] }}</h3>
                                <p>{{ $step['text'] ?? '' }}</p>
                            </li>
                        @endforeach
                    </ol>
                    <div class="g-kreo-bar" aria-hidden="true"><i></i></div>
                </div>
                <div class="g-kreo-stage" data-g-kreo-stage aria-hidden="true">
                    @include('partials.g.kreo-still', ['base' => 'kreo-1-' . $kreoLang, 'class' => 'g-kreo-poster', 'eager' => false, 'alt' => $kreoSteps[0]['title']])
                </div>
            </div>
        </div>
    </div>
</section>
@endif
