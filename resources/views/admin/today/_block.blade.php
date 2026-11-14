{{--
    Един блок на „Днес“ ($block, от App\Support\Admin\TodayBlocks): заглавие, брой, най-важните редове с преки действия.
    Празен блок се свива до един тих ред.
--}}
@php
    $rows = $block['rows'];
    $hasBody = ($block['kind'] ?? null) === 'calls' || count($rows) > 0;
    $toneClass = ['danger' => 'danger', 'warn' => 'warn', 'info' => 'info'];
@endphp
@if(! $hasBody)
    <div class="td-quiet" data-block="{{ $block['key'] }}">
        <b>{{ $block['title'] }}</b><span>{{ $block['empty'] }}</span>
        @if(! empty($block['url']))<a href="{{ $block['url'] }}">Към раздела →</a>@endif
    </div>
@else
<section class="td-block" data-block="{{ $block['key'] }}">
    <header>
        <h3>{{ $block['title'] }}</h3>
        <span class="td-count {{ ($block['count'] ?? 0) > 0 ? 'hot' : 'zero' }}">{{ $block['count'] }}</span>
        @if(! empty($block['url']))<a href="{{ $block['url'] }}" class="all">Виж всички →</a>@endif
    </header>
    @if(! empty($block['hint']))<p class="td-hint">{{ $block['hint'] }}</p>@endif

    @if(($block['kind'] ?? null) === 'calls')
        @php $calls = $block["calls"]; @endphp
        <div class="px-4 py-4">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <div>
                    <p class="adm-eyebrow">Обаждания днес</p>
                    <p class="mt-1 font-display text-3xl font-light tracking-tight">{{ $calls['total'] }} <span class="text-base text-gray-500">/ {{ $calls['goal'] }}</span></p>
                </div>
                <a href="{{ route('admin.outreach.calls.next') }}" class="adm-btn">Следващо обаждане <x-icon name="arrow-right" /></a>
            </div>
            <div class="td-meter"><i style="width: {{ min(100, round($calls['total'] / max(1, $calls['goal']) * 100)) }}%"></i></div>
            <p class="mt-3 text-sm text-gray-600">
                @if($block['callbacks'])<span class="font-semibold text-orange-700">{{ $block['callbacks'] }} отново днес</span> · @endif
                @if($calls['by_status']->isNotEmpty()){{ $calls['by_status']->map(fn ($n, $s) => $n . ' ' . (\App\Models\OutreachCall::STATUSES[$s] ?? $s))->join(', ') }}@else Още няма обаждания днес.@endif
            </p>
            <p class="mt-1 text-sm text-gray-600">Първи писма днес: <b>{{ $block['emails']['sent'] }}</b> / {{ $block['emails']['limit'] }}</p>
        </div>
    @else
        <ul class="td-rows">
            @foreach($rows as $row)
                <li class="td-row {{ $toneClass[$row['tone'] ?? ''] ?? '' }}">
                    <div class="td-row-main">
                        <div>
                            @if(! empty($row['url']))
                                <a href="{{ $row['url'] }}" class="td-title" @if(! empty($row['external'])) target="_blank" rel="noopener noreferrer" @endif>{{ $row['title'] }}</a>
                            @else
                                <span class="td-title">{{ $row['title'] }}</span>
                            @endif
                            @if(! empty($row['chips']) || ! empty($row['wait']))
                                <span class="td-chips">
                                    @if(! empty($row['wait']))<span class="text-xs font-semibold {{ $row['wait'][1] ? 'text-red-700' : 'text-gray-500' }}">{{ $row['wait'][0] }}</span>@endif
                                    @foreach($row['chips'] ?? [] as [$text, $class])<span class="rounded-full px-2 py-0.5 text-xs font-semibold {{ $class }}">{{ $text }}</span>@endforeach
                                </span>
                            @endif
                            @if(! empty($row['sub']))<p class="td-sub {{ ($row['sub_tone'] ?? null) === 'danger' ? 'danger' : '' }}">{{ $row['sub'] }}</p>@endif
                            @if(! empty($row['detail']))<p class="mt-1 line-clamp-2 text-sm text-gray-700">{{ $row['detail'] }}</p>@endif
                            @if(! empty($row['fix']))<p class="mt-1 text-xs text-gray-500">Оправя се с <code>{{ $row['fix'] }}</code></p>@endif
                        </div>
                        @if(! empty($row['phone']) || ! empty($row['mailto']) || ! empty($row['actions']))
                            <div class="td-actions">
                                @if(! empty($row['phone']))<x-phone :number="$row['phone']" class="text-sm" />@endif
                                @if(! empty($row['mailto']))<a href="mailto:{{ $row['mailto'] }}" class="text-sm">{{ $row['mailto'] }}</a>@endif
                                @foreach($row['actions'] ?? [] as $action)
                                    @if(($action[2] ?? 'get') === 'post')
                                        <form action="{{ $action[1] }}" method="POST" class="inline">@csrf<button type="submit" class="td-act {{ ! empty($action[3]) ? 'primary' : '' }}" @if(! empty($action[4])) title="{{ $action[4] }}" @endif>{{ $action[0] }}</button></form>
                                    @else
                                        <a href="{{ $action[1] }}" class="td-act {{ ! empty($action[3]) ? 'primary' : '' }}">{{ $action[0] }}</a>
                                    @endif
                                @endforeach
                            </div>
                        @endif
                    </div>
                    @if(! empty($row['pipeline']))
                        <details><summary>{{ $row['pipeline_label'] ?? 'Етап и следваща стъпка' }}</summary><div class="mt-2">@include('admin.projects._pipeline', ['project' => $row['pipeline']])</div></details>
                    @endif
                </li>
            @endforeach
        </ul>
        @if(($block['rest'] ?? 0) > 0)
            <p class="td-more"><a href="{{ $block['url'] ?? '#' }}">Още {{ $block['rest'] }} →</a></p>
        @endif
    @endif
</section>
@endif
