@extends('admin.layout')

@section('title', 'Следващо обаждане')

@section('content')
@include('admin.outreach._tabs')
@include('admin.outreach._call-stats')

<div class="mb-4 flex flex-wrap items-center justify-between gap-3 text-sm">
    <span class="flex flex-wrap items-center gap-3">
        @if($canGoBack)
            <a href="{{ route('admin.outreach.calls.back', $query) }}" class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3 py-1.5 font-semibold text-gray-800 hover:bg-gray-50"><x-icon name="rotate-left" /> Назад към предишния</a>
        @endif
        <a href="{{ route('admin.outreach.calls.index', $query) }}" class="text-blue-600 hover:underline">Към списъка</a>
    </span>
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
            @if($revisit && $call->status !== 'new')
                <span class="mb-3 inline-block rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-800">Записано: {{ \App\Models\OutreachCall::STATUSES[$call->status] }}@if($call->called_at), {{ $call->called_at->timezone('Europe/Sofia')->format('d.m H:i') }}@endif. Можете да го поправите.</span>
            @elseif($call->callbackDue())
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
                        @if($phone['mobile'])
                            <div class="flex justify-center gap-2 text-xs">
                                <a href="{{ \App\Support\Phone::viber($phone['label']) }}" class="rounded-md bg-purple-100 px-2 py-1 font-semibold text-purple-800 hover:bg-purple-200">Viber</a>
                                <a href="{{ \App\Support\Phone::whatsapp($phone['label']) }}" target="_blank" rel="noopener" class="rounded-md bg-emerald-100 px-2 py-1 font-semibold text-emerald-800 hover:bg-emerald-200">WhatsApp</a>
                            </div>
                        @endif
                    @else
                        <span class="text-sm text-gray-500">{{ $phone['label'] }}</span>
                    @endif
                @endforeach
            </div>
            @if($call->note)<p class="mt-4 rounded bg-gray-50 p-3 text-sm text-gray-700"><strong>Бележка:</strong> {{ $call->note }}</p>@endif
            <div class="mt-4 text-center">@if($call->project_id)<a href="{{ route('admin.projects.show', $call->project_id) }}" class="inline-flex items-center rounded-lg bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-800 hover:bg-blue-100">Запитване →</a>@else<form action="{{ route('admin.outreach.calls.lead', $call) }}" method="POST" class="inline">@csrf<button class="rounded-lg border border-blue-300 px-2.5 py-1 text-xs font-semibold text-blue-700 hover:bg-blue-50" title="Прояви интерес: направи запитване с етапи и следваща стъпка">Има интерес → направи запитване</button></form>@endif</div>

            <form action="{{ route('admin.outreach.calls.update', $call) }}" method="POST" class="mt-6 space-y-4 border-t border-gray-100 pt-5" data-call-form>
                @csrf @method('PATCH')
                <input type="hidden" name="next" value="1">
                @foreach($query as $key => $value)<input type="hidden" name="{{ $key }}" value="{{ $value }}">@endforeach
                <fieldset>
                    <legend class="mb-2 text-sm font-semibold text-gray-700">Резултат</legend>
                    <div class="grid grid-cols-2 gap-2">
                        @foreach(['no_answer' => 'Не вдигна', 'interested' => 'Интерес', 'not_interested' => 'Без интерес', 'client' => 'Клиент'] as $key => $label)
                            <label class="flex cursor-pointer items-center gap-2 rounded-lg border border-gray-200 px-3 py-2.5 text-sm has-[:checked]:border-brand-600 has-[:checked]:bg-brand-50">
                                <input type="radio" name="status" value="{{ $key }}" required @checked(old('status', $revisit ? $call->status : null) === $key)> <kbd class="hidden md:inline rounded border border-gray-300 px-1 text-[10px] text-gray-500">{{ $loop->iteration }}</kbd> {{ $label }}
                            </label>
                        @endforeach
                    </div>
                </fieldset>
                <div>
                    <label for="callback_on" class="mb-1 block text-sm font-semibold text-gray-700">Обади се отново на <span class="font-normal text-gray-400">(по желание)</span></label>
                    <input type="date" id="callback_on" name="callback_on" min="{{ now('Europe/Sofia')->addDay()->toDateString() }}" value="{{ old('callback_on', $revisit ? $call->callback_on?->toDateString() : null) }}" class="rounded border border-gray-300 px-2 py-1.5 text-sm">
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
            <form action="{{ route('admin.outreach.calls.skip', $call) }}" method="POST" class="mt-3" data-skip-form>
                @csrf
                @foreach($query as $key => $value)<input type="hidden" name="{{ $key }}" value="{{ $value }}">@endforeach
                <button class="text-sm text-gray-500 hover:text-gray-800 hover:underline">Пропусни засега</button>
            </form>
        </div>

        @include('admin.outreach._call-script', ['call' => $call])
    </div>
    <p class="mt-3 hidden text-center text-xs text-gray-400 md:block">Клавиши: <kbd>1</kbd>–<kbd>4</kbd> резултат · <kbd>Enter</kbd> запази и следващ · <kbd>S</kbd> пропусни · <kbd>B</kbd> назад</p>
    <script>
        // Бързо минаване през списъка от клавиатурата (не пречи, докато пишете в полетата).
        document.addEventListener('keydown', function (e) {
            var tag = (e.target.tagName || '').toLowerCase();
            if (e.ctrlKey || e.metaKey || e.altKey || tag === 'textarea' || (tag === 'input' && ! ['radio', 'checkbox'].includes(e.target.type)) || tag === 'select') return;
            var form = document.querySelector('[data-call-form]');
            if (!form) return;
            var radios = form.querySelectorAll('input[name=status]');
            if (/^[1-4]$/.test(e.key) && radios[e.key - 1]) { radios[e.key - 1].checked = true; radios[e.key - 1].dispatchEvent(new Event('change', {bubbles: true})); e.preventDefault(); }
            else if (e.key === 'Enter') { e.preventDefault(); form.requestSubmit(); }
            else if (e.key.toLowerCase() === 's') { document.querySelector('[data-skip-form]')?.requestSubmit(); }
            else if (e.key.toLowerCase() === 'b') { var back = document.querySelector('a[href*="outreach-calls/back"]'); if (back) location.href = back.href; }
        });
        document.querySelectorAll('[data-callback]').forEach(function (button) {
            button.addEventListener('click', function () { document.getElementById('callback_on').value = button.dataset.callback; });
        });
    </script>
@endif
@endsection
