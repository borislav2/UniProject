@extends('admin.layout')

@section('title', 'Следващо обаждане')

@section('content')
@include('admin.outreach._tabs')
@include('admin.outreach._call-stats')

<div class="mb-4 flex flex-wrap items-center justify-between gap-3 text-sm">
    <a href="{{ route('admin.outreach.calls.index', $query) }}" class="text-blue-600 hover:underline">&larr; Към списъка</a>
    <span class="text-gray-600">
        @if($query){{ collect([$filters['city'], $filters['industry'] ? \App\Models\OutreachCall::INDUSTRIES[mb_strtolower($filters['industry'])] ?? $filters['industry'] : null, ['mobile' => 'само мобилни', 'landline' => 'само стационарни'][$filters['mobile']] ?? null])->filter()->join(' · ') }} · @endif
        остават {{ $left }}
    </span>
</div>

@if(! $call)
    <div class="rounded-lg bg-white p-8 text-center shadow">
        <p class="text-lg font-semibold text-gray-900">Няма повече за обаждане с тези филтри.</p>
        <p class="mt-2 text-sm text-gray-600">Уговорените обаждания („Обади се отново“) ще се появят тук на датата си. Тези, които не вдигнаха, идват отново на следващия ден (до {{ \App\Models\OutreachCall::MAX_NO_ANSWER }} опита).</p>
    </div>
@else
    <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_minmax(0,1.1fr)]">
        <div class="rounded-lg bg-white p-6 shadow">
            @if($call->callbackDue())
                <span class="mb-3 inline-block rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-800">Уговорено обаждане за {{ $call->callback_on->format('d.m.Y') }}</span>
            @elseif($call->status === 'no_answer')
                <span class="mb-3 inline-block rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-700">Не вдигна {{ $call->attempts }} път</span>
            @endif
            <h2 class="text-2xl font-bold text-gray-900">{{ $call->name }}</h2>
            <p class="mt-1 text-sm text-gray-600">{{ collect([$call->industryLabel(), $call->city])->filter()->join(' · ') }}</p>
            @if($call->source_url)<a href="{{ $call->source_url }}" target="_blank" rel="noopener" class="mt-1 inline-block text-sm text-blue-600 hover:underline">На картата</a>@endif

            <div class="mt-5 flex flex-col gap-2">
                @foreach($call->phones() as $phone)
                    @if($phone['href'])
                        <a href="{{ $phone['href'] }}" class="inline-flex items-center justify-center gap-2 rounded-lg {{ $loop->first ? 'bg-green-600 hover:bg-green-700 py-4 text-lg' : 'bg-green-50 text-green-800 hover:bg-green-100 py-2.5' }} px-4 font-semibold {{ $loop->first ? 'text-white' : '' }}">
                            <x-icon name="phone" /> {{ $phone['label'] }}
                            <span class="text-xs font-normal opacity-80">{{ $phone['mobile'] ? 'мобилен' : 'стационарен' }}</span>
                        </a>
                    @else
                        <span class="text-sm text-gray-500">{{ $phone['label'] }}</span>
                    @endif
                @endforeach
            </div>
            @if($call->note)<p class="mt-4 rounded bg-gray-50 p-3 text-sm text-gray-700"><strong>Бележка:</strong> {{ $call->note }}</p>@endif

            <form action="{{ route('admin.outreach.calls.update', $call) }}" method="POST" class="mt-6 space-y-4 border-t border-gray-100 pt-5">
                @csrf @method('PATCH')
                <input type="hidden" name="next" value="1">
                @foreach($query as $key => $value)<input type="hidden" name="{{ $key }}" value="{{ $value }}">@endforeach
                <fieldset>
                    <legend class="mb-2 text-sm font-semibold text-gray-700">Резултат</legend>
                    <div class="grid grid-cols-2 gap-2">
                        @foreach(['no_answer' => 'Не вдигна', 'interested' => 'Интерес', 'not_interested' => 'Без интерес', 'client' => 'Клиент'] as $key => $label)
                            <label class="flex cursor-pointer items-center gap-2 rounded-lg border border-gray-200 px-3 py-2.5 text-sm has-[:checked]:border-brand-600 has-[:checked]:bg-brand-50">
                                <input type="radio" name="status" value="{{ $key }}" required @checked(old('status') === $key)> {{ $label }}
                            </label>
                        @endforeach
                    </div>
                </fieldset>
                <div>
                    <label for="callback_on" class="mb-1 block text-sm font-semibold text-gray-700">Обади се отново на <span class="font-normal text-gray-400">(по желание)</span></label>
                    <input type="date" id="callback_on" name="callback_on" min="{{ now('Europe/Sofia')->addDay()->toDateString() }}" value="{{ old('callback_on') }}" class="rounded border border-gray-300 px-2 py-1.5 text-sm">
                    <div class="mt-2 flex flex-wrap gap-2 text-xs">
                        {{-- Бързите дати падат само в работни дни: събота и неделя се местят на понеделник. --}}
                        @foreach(['Следващ работен ден' => 1, 'След 3 дни' => 3, 'След седмица' => 7, 'След месец' => 30] as $label => $days)
                            @php($date = now('Europe/Sofia')->addDays($days))
                            @php($date = $date->isWeekend() ? $date->next(\Carbon\CarbonInterface::MONDAY) : $date)
                            <button type="button" class="rounded-full border border-gray-300 px-2.5 py-1 text-gray-700 hover:bg-gray-50" data-callback="{{ $date->toDateString() }}" title="{{ $date->format('d.m.Y') }}">{{ $label }}</button>
                        @endforeach
                    </div>
                </div>
                <div>
                    <label for="note" class="mb-1 block text-sm font-semibold text-gray-700">Бележка</label>
                    <textarea id="note" name="note" rows="2" maxlength="1000" class="w-full rounded border border-gray-300 px-2 py-1.5 text-sm" placeholder="С кого говорихме, имейл за прегледа, кога да звъннем…">{{ old('note', $call->note) }}</textarea>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <button class="rounded-lg bg-brand-950 px-5 py-2.5 text-sm font-semibold text-white hover:bg-brand-800">Запази и следващ</button>
                </div>
            </form>
            <form action="{{ route('admin.outreach.calls.skip', $call) }}" method="POST" class="mt-3">
                @csrf
                @foreach($query as $key => $value)<input type="hidden" name="{{ $key }}" value="{{ $value }}">@endforeach
                <button class="text-sm text-gray-500 hover:text-gray-800 hover:underline">Пропусни засега</button>
            </form>
        </div>

        @include('admin.outreach._call-script', ['call' => $call])
    </div>
    <script>
        document.querySelectorAll('[data-callback]').forEach(function (button) {
            button.addEventListener('click', function () { document.getElementById('callback_on').value = button.dataset.callback; });
        });
    </script>
@endif
@endsection
