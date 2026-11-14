@extends('layouts.public')

{{-- /za-nas in design G, in the voice of an agency: who we are, method, standards, founders (names and roles), the company line. --}}
@section('title', __('За нас и екипа зад вашия сайт и маркетинг'))
@section('meta_description', __('Creatium Lab прави сайтове и маркетинг за бизнеси в България, от първата страница до клиентите, които я намират в Google.'))
@section('g_lean', 'inner')

@php
    $pad = fn ($n) => str_pad((string) $n, 2, '0', STR_PAD_LEFT);
    $intro = array_values(array_filter((array) ($about['g_intro'] ?? []), 'filled'));
    if (! $intro) {
        $intro = array_values(array_filter((array) ($about['paragraphs'] ?? []), 'filled'));
    }
    $method = array_values(array_filter((array) ($about['g_method'] ?? []), fn ($m) => filled($m['title'] ?? null)));
    $standards = array_values(array_filter((array) ($about['g_standards'] ?? []), fn ($m) => filled($m['title'] ?? null)));
    $founders = array_values(array_filter((array) $team, fn ($m) => filled($m['name'] ?? null) || filled($m['role'] ?? null)));
    $legal = config('creatium.legal');
    $legalLine = collect([
        $legal['company'] ?? null,
        ! empty($legal['eik']) ? __('ЕИК') . ' ' . $legal['eik'] : null,
        $legal['address'] ?? null,
        __(config('creatium.contact.city')) . ', ' . __('България'),
    ])->filter()->implode(', ');
@endphp

@section('content')
@include('partials.g.page-head', ['heading' => $about['g_intro_title'] ?? __('Дигитална агенция за бизнеси'), 'sub' => $intro[0] ?? null, 'more' => array_slice($intro, 1)])

@if($method)
<section class="g-s">
    <div class="g-wrap g-sys">
        <div class="g-sh">
            <h2 class="g-h">{{ $about['g_method_title'] ?? __('Как работим') }}</h2>
        </div>
        <ol class="g-rows g-numbered">
            @foreach($method as $step)
                <li><span class="g-n g-mono">{{ $pad($loop->iteration) }}</span><h3>{{ $step['title'] }}</h3><p>{{ $step['text'] ?? '' }}</p></li>
            @endforeach
        </ol>
    </div>
</section>
@endif

@if($standards)
<section class="g-s">
    <div class="g-wrap g-sys">
        <div class="g-sh">
            <h2 class="g-h">{{ $about['g_standards_title'] ?? __('Нашите стандарти') }}</h2>
        </div>
        <dl class="g-defs">
            @foreach($standards as $item)<div><dt>{{ $item['title'] }}</dt><dd>{{ $item['text'] ?? '' }}</dd></div>@endforeach
        </dl>
    </div>
</section>
@endif

@if($founders)
<section class="g-s" id="ekip">
    <div class="g-wrap">
        <div class="g-sh">
            <h2 class="g-h">{{ $about['g_founders_title'] ?? __('Екипът') }}</h2>
        </div>
        {{-- Team cards (fifth round): photo or initials, name, role, a short description, the longer text, skills and LinkedIn. Horizontal cards, one per row, any number of members. --}}
        <div class="g-team" data-g-team>
            @foreach($founders as $member)
                @php
                    $name = trim((string) ($member['name'] ?? ''));
                    $initials = collect(preg_split('/\s+/u', $name, -1, PREG_SPLIT_NO_EMPTY))->take(2)->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('');
                    $hasPhoto = ! empty($member['image']) && is_file(public_path($member['image']));
                    $linkedin = str_starts_with((string) ($member['linkedin'] ?? ''), 'https://') ? $member['linkedin'] : null;
                @endphp
                <article class="g-member">
                    @if($hasPhoto)
                        <img src="{{ asset($member['image']) }}" alt="{{ $name !== '' ? $name : ($member['role'] ?? '') }}" width="260" height="260" loading="lazy" decoding="async">
                    @else
                        <span class="g-initials" aria-hidden="true">{{ $initials !== '' ? $initials : '·' }}</span>
                    @endif
                    <div class="g-member-body">
                        @if($name !== '')<h3>{{ $name }}</h3>@endif
                        <p class="g-role">{{ $member['role'] ?? '' }}</p>
                        @if(filled($member['description'] ?? null))<p class="g-member-lead">{{ $member['description'] }}</p>@endif
                        @if(! empty($member['bio']))
                            <div class="g-member-bio">
                                @foreach((array) $member['bio'] as $paragraph)<p>{{ $paragraph }}</p>@endforeach
                            </div>
                        @endif
                        @if(! empty($member['skills']))
                            <ul class="g-skills">
                                @foreach((array) $member['skills'] as $skill)<li>{{ $skill }}</li>@endforeach
                            </ul>
                        @endif
                        @if($linkedin)<a class="g-link g-member-link" href="{{ $linkedin }}" target="_blank" rel="noopener">{{ __('Профил в LinkedIn') }}</a>@endif
                    </div>
                </article>
            @endforeach
        </div>
        <p class="g-legal-line">{{ $legalLine }}</p>
    </div>
</section>
@endif

@include('partials.g.closer', ['kicker' => __('Имате въпроси?'), 'text' => __('Запазете час за разговор'), 'label' => __('Запазете час'), 'href' => lroute('contact') . '#konsultatsiya', 'link' => ['label' => __('Към въпросите'), 'href' => lroute('faq')]])
@endsection
