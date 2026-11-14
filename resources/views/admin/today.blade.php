@extends('admin.layout')

@section('title', 'Днес')

@php
    $dotColors = ['call' => 'bg-green-600', 'outreach-call' => 'bg-green-600', 'email' => 'bg-gray-800', 'outreach' => 'bg-indigo-500', 'linkedin-invite' => 'bg-sky-500', 'linkedin-message' => 'bg-sky-500', 'stage' => 'bg-orange-500', 'note' => 'bg-gray-400'];
    $isMaster = auth()->user()->isMaster();
    $mine = auth()->user();
    $others = $team->where('id', '!=', $mine->id);
    $tabs = $isMaster
        ? collect([['all', null, 'Целият екип']])->merge($team->map(fn ($p) => [$p->id, $p, $p->id === $mine->id ? 'Моят ден' : $p->name]))
        : collect([[$mine->id, $team->firstWhere('id', $mine->id) ?? $mine, 'Моят ден']])->merge($others->map(fn ($p) => [$p->id, $p, $p->name]))->push(['all', null, 'Целият екип']);
@endphp

@section('content')
{{-- Денят на един човек: отгоре числата, после блоковете на профила по спешност, най-долу какво е направено днес. --}}
@if($team->count() > 1)
    <nav class="td-tabs" aria-label="Чий ден">
        @foreach($tabs as [$tabKey, $person, $label])
            @php $active = $tabKey === 'all' ? ! $member : ($member?->id === $tabKey); @endphp
            <a href="{{ route('admin.today', ['user' => $tabKey]) }}" class="td-tab" @if($active) aria-current="true" @endif>
                {{ $label }}
                @if($person && ($todoByMember[$person->id] ?? 0))<i>{{ $todoByMember[$person->id] }}</i>@endif
            </a>
        @endforeach
    </nav>
@endif

<div class="td-head">
    <div>
        <h2>{{ $member ? ($member->id === auth()->id() ? 'Вашият ден' : 'Денят на ' . $member->name) : 'Денят на екипа' }}, {{ now('Europe/Sofia')->locale('bg')->isoFormat('dddd, D MMMM') }}</h2>
        <p>{{ $member ? 'Запитванията, които води ' . ($member->id === auth()->id() ? 'вие' : $member->name) . ', и тези без човек (поемете ги).' : 'Всичко отворено, на всички.' }}</p>
    </div>
    <span class="adm-eyebrow">{{ \App\Support\Admin\Profiles::label($profile) }}</span>
</div>

<div class="td-kpis" style="--cols: {{ count($kpis) }}">
    @foreach($kpis as $kpi)
        @php $tag = ! empty($kpi['url']) ? 'a' : 'div'; @endphp
        <{{ $tag }} @if(! empty($kpi['url'])) href="{{ $kpi['url'] }}" @endif class="td-kpi {{ ! empty($kpi['hot']) ? 'hot' : '' }}">
            <small>{{ $kpi['label'] }}</small>
            <b>{{ $kpi['value'] }}@if(isset($kpi['of'])) <span>/ {{ $kpi['of'] }}</span>@endif</b>
            @if(! empty($kpi['note']))<em>{{ $kpi['note'] }}</em>@endif
            @if(! empty($kpi['meter']))<div class="td-meter"><i style="width: {{ min(100, round($kpi['meter'][0] / max(1, $kpi['meter'][1]) * 100)) }}%"></i></div>@endif
        </{{ $tag }}>
    @endforeach
</div>

@foreach($sections as $sectionTitle => $keys)
    @if($sectionTitle !== '')<div class="td-section"><h3>{{ $sectionTitle }}</h3></div>@endif
    <div class="td-grid {{ count($sections) > 1 || count($keys) > 3 ? 'two' : '' }}">
        @foreach($keys as $key)
            @include('admin.today._block', ['block' => $blocks[$key]])
        @endforeach
    </div>
@endforeach

{{-- Направено днес: разговори, имейли, писма, смени на етапа. --}}
<div class="td-section"><h3>Направено днес · {{ $member ? ($member->id === auth()->id() ? 'от вас' : 'от ' . $member->name) : 'от целия екип' }}</h3></div>
<div class="td-kpis" style="--cols: 7">
    @foreach([['calls', 'Обаждания'], ['emails', 'Имейли до клиенти'], ['outreach', 'Писма до бизнеси'], ['linkedin_invites', 'Покани в LinkedIn'], ['linkedin_messages', 'Съобщения в LinkedIn'], ['stages', 'Сменени етапи'], ['notes', 'Бележки']] as [$key, $label])
        <div class="td-kpi"><small>{{ $label }}</small><b style="{{ $doneCounts[$key] ? '' : 'color: var(--line-strong); opacity: .5' }}">{{ $doneCounts[$key] }}</b></div>
    @endforeach
</div>
@if($done->isEmpty())
    <div class="td-quiet">Още нищо записано днес. Обажданията, имейлите и смените на етапа се появяват тук сами.</div>
@else
    <div class="td-block">
        <ol class="td-feed">
            @foreach($done->take(60) as $entry)
                <li>
                    <time>{{ $entry['at']->timezone('Europe/Sofia')->format('H:i') }}</time>
                    <span class="dot {{ $dotColors[$entry['type']] ?? 'bg-gray-400' }}"></span>
                    <span class="min-w-0">
                        <b class="font-semibold">{{ $entry['label'] }}</b>@if(! $member && $entry['user'])<span class="text-gray-500"> · {{ $entry['user'] }}</span>@endif
                        <span class="block text-gray-600">@if($entry['url'])<a href="{{ $entry['url'] }}" class="hover:text-brand-700">{{ $entry['text'] }}</a>@else{{ $entry['text'] }}@endif</span>
                    </span>
                </li>
            @endforeach
        </ol>
    </div>
@endif
@endsection
