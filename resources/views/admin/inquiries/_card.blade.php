{{--
    Една карта в „Запитвания“ / „Проверки на сайт“ ($inquiry, $notifyEmail).
    Непрочетено: синя лента и син фон с етикет „Ново“; прочетено: бяла карта със сива лента.
    Проверка на сайт: най-отгоре е обобщението (оценка и какво не е наред) вместо съобщение.
--}}
@php
    $customer = preg_replace('/^(Запитване( за оферта)? от |Проверка на сайт: )/u', '', $inquiry->name);
    $isNew = ! $inquiry->read_at;
    $summary = $inquiry->siteCheckSummary();
    $hours = (int) $inquiry->created_at->diffInHours(now());
    $waiting = $hours < 1 ? 'преди малко' : ($hours < 24 ? "преди {$hours} ч" : 'преди ' . intdiv($hours, 24) . ' дни');
@endphp
<article class="rounded-lg shadow p-5 border-l-8 {{ $isNew ? 'border-blue-600 bg-blue-50 ring-1 ring-blue-200' : 'border-gray-200 bg-white' }}" @if($isNew) data-unread @endif>
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div class="min-w-0">
            <h3 class="flex flex-wrap items-center gap-2 text-lg {{ $isNew ? 'font-extrabold text-gray-900' : 'font-semibold text-gray-800' }}">
                @if($isNew)<span class="inline-flex items-center gap-1 rounded-full bg-blue-600 px-2.5 py-0.5 text-xs font-bold uppercase tracking-wide text-white"><span class="h-1.5 w-1.5 rounded-full bg-white"></span> Непрочетено</span>@endif
                {{ $customer }}
                @if($inquiry->stage)<span class="rounded-full px-2 py-0.5 text-xs font-semibold {{ $inquiry->stageClass() }}">{{ $inquiry->stageLabel() }}</span>@endif
                @if($inquiry->isOverdue())<span class="rounded-full bg-red-100 px-2 py-0.5 text-xs font-semibold text-red-700">Просрочена стъпка</span>@endif
            </h3>
            <p class="mt-1 text-sm {{ $isNew && $hours >= 4 ? 'font-semibold text-red-600' : 'text-gray-500' }}">
                {{ $inquiry->created_at->timezone('Europe/Sofia')->format('d.m.Y H:i') }} · {{ $waiting }}@if($isNew && $hours >= 4) · чака отговор@endif
                @unless($isNew)<span class="ml-1 text-gray-400">· прочетено</span>@endunless
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-2 text-xs">
            @if($inquiry->leadKind())<span class="rounded-full px-2 py-1 font-semibold {{ $inquiry->leadKindClass() }}">{{ $inquiry->leadKindLabel() }}</span>@endif
            @if($inquiry->service && $inquiry->leadKind() === 'form')<span class="rounded-full bg-gray-100 px-2 py-1 text-gray-700">{{ \Illuminate\Support\Str::limit($inquiry->serviceLabel(), 60) }}</span>@endif
            @if($inquiry->business_size)<span class="rounded-full bg-gray-100 px-2 py-1 text-gray-700">Бизнес: {{ $inquiry->business_size }}</span>@endif
            @if($inquiry->lead_channel)<span class="rounded-full bg-gray-100 px-2 py-1 text-gray-700" title="Откъде дойде">{{ $inquiry->lead_channel }}</span>@endif
            @if($inquiry->locale === 'en')<span class="rounded-full bg-purple-100 px-2 py-1 text-purple-800">EN: отговорете на английски</span>@endif
            @if($inquiry->assignee)
                <span class="inline-flex items-center gap-1 rounded-full bg-gray-800 px-2 py-1 font-semibold text-white" title="Кой го води">{{ $inquiry->assignee->name }}</span>
            @else
                <span class="rounded-full border border-dashed border-orange-400 px-2 py-1 font-semibold text-orange-700">Без човек</span>
            @endif
        </div>
    </div>

    @if($summary)
        {{-- Обобщение на проверката: поводът за разговора. --}}
        @php
            $score = $summary['score'];
            $tone = $score === null ? 'gray' : ($score >= 80 ? 'green' : ($score >= 50 ? 'amber' : 'red'));
            $tones = ['green' => 'bg-green-100 text-green-800 ring-green-300', 'amber' => 'bg-amber-100 text-amber-800 ring-amber-300', 'red' => 'bg-red-100 text-red-800 ring-red-300', 'gray' => 'bg-gray-100 text-gray-600 ring-gray-300'];
        @endphp
        <section class="mt-4 rounded-lg border border-gray-200 bg-white p-4">
            <div class="flex flex-wrap items-center gap-4">
                <div class="flex h-16 w-16 shrink-0 flex-col items-center justify-center rounded-full ring-4 {{ $tones[$tone] }}">
                    <span class="text-xl font-extrabold leading-none">{{ $score ?? '–' }}</span>
                    <span class="text-[10px] font-semibold">от 100</span>
                </div>
                <div class="min-w-0">
                    <p class="text-xs font-bold uppercase tracking-wider text-gray-500">Обобщение на проверката</p>
                    <p class="font-semibold text-gray-900">
                        @if($summary['domain'])<a href="{{ \Illuminate\Support\Str::startsWith($summary['domain'], 'http') ? $summary['domain'] : 'https://' . $summary['domain'] }}" target="_blank" rel="noopener" class="text-blue-600 hover:underline">{{ preg_replace('#^https?://#', '', $summary['domain']) }}</a>@endif
                    </p>
                    <p class="text-sm text-gray-600">
                        @if(! empty($summary['pending']))
                            Проверката още тече.
                        @elseif($summary['failed'])
                            Проверката не успя (сайтът не отговори). Свържете се директно.
                        @else
                            {{ collect([
                                count($summary['fail']) . ' ' . (count($summary['fail']) === 1 ? 'проблем' : 'проблема'),
                                count($summary['warn']) ? count($summary['warn']) . ' за подобряване' : null,
                                $summary['ok'] ? $summary['ok'] . ' наред' : null,
                            ])->filter()->join(', ') }}.
                            {{ $summary['sent'] ? 'Отчетът е изпратен на клиента.' : '' }}
                        @endif
                    </p>
                </div>
            </div>
            @if(! empty($summary['crawl']))
                @php $crawl = $summary['crawl']; @endphp
                <p class="mt-3 text-sm text-gray-600">
                    Обходени страници: <strong>{{ $crawl['analyzed'] ?? $crawl['pages'] }}</strong> (лимит {{ $crawl['limit'] }}), {{ $crawl['seconds'] }} сек.
                    @if(! empty($crawl['limited']))<span class="font-semibold text-amber-700">Обхождането е ограничено ({{ ($crawl['limited_by'] ?? '') === 'time' ? 'по време' : 'по брой страници' }}): има още страници, които не са проверени.</span>@endif
                    @if(! empty($crawl['error']))<span class="font-semibold text-red-700">Обхождането спря с грешка: {{ $crawl['error'] }}</span>@endif
                    @if(! empty($summary['pagespeed']))· Скорост на телефон: <strong>{{ $summary['pagespeed']['score'] }}/100</strong>@endif
                </p>
            @endif
            @if(! empty($summary['shopping']))
                @php $shop = $summary['shopping']; @endphp
                <p class="mt-3 text-sm text-gray-600">
                    <span class="font-semibold text-gray-900">Изглежда като онлайн магазин. Готовност за Google Shopping: {{ $shop['ok'] }} от {{ $shop['total'] }} наред.</span>
                    @if($shop['missing']) Липсват: {{ implode('; ', $shop['missing']) }}.@endif
                </p>
            @endif
            @if(! empty($summary['issues']))
                <p class="mt-3 text-xs font-bold uppercase tracking-wider text-gray-500">Проблеми по целия сайт</p>
                <ul class="mt-2 grid gap-2 text-sm sm:grid-cols-2">
                    @foreach(array_slice($summary['issues'], 0, 6) as $issue)
                        @php $isErr = $issue['severity'] === 'error'; @endphp
                        <li class="flex gap-2 rounded-md px-3 py-2 {{ $isErr ? 'bg-red-50' : 'bg-amber-50' }}"><span class="mt-1.5 h-2 w-2 shrink-0 rounded-full {{ $isErr ? 'bg-red-500' : 'bg-amber-500' }}"></span><span class="min-w-0"><span class="font-semibold {{ $isErr ? 'text-red-900' : 'text-amber-900' }}">{{ $issue['title'] }}</span>@if(! empty($issue['examples'][0]))<span class="block truncate text-xs text-gray-600">напр. {{ $issue['examples'][0]['url'] }}</span>@endif</span></li>
                    @endforeach
                </ul>
                @if(count($summary['issues']) > 6)<p class="mt-1 text-xs text-gray-500">и още {{ count($summary['issues']) - 6 }} вида проблеми (пълният списък е в отчета на клиента).</p>@endif
            @endif
            @if($summary['fail'] || $summary['warn'])
                <ul class="mt-4 grid gap-2 text-sm sm:grid-cols-2">
                    @foreach($summary['fail'] as $item)
                        <li class="flex gap-2 rounded-md bg-red-50 px-3 py-2"><span class="mt-1.5 h-2 w-2 shrink-0 rounded-full bg-red-500"></span><span><span class="font-semibold text-red-900">{{ $item['title'] }}</span>@if($item['detail'])<span class="block text-red-800/80">{{ $item['detail'] }}</span>@endif</span></li>
                    @endforeach
                    @foreach($summary['warn'] as $item)
                        <li class="flex gap-2 rounded-md bg-amber-50 px-3 py-2"><span class="mt-1.5 h-2 w-2 shrink-0 rounded-full bg-amber-500"></span><span><span class="font-semibold text-amber-900">{{ $item['title'] }}</span>@if($item['detail'])<span class="block text-amber-800/80">{{ $item['detail'] }}</span>@endif</span></li>
                    @endforeach
                </ul>
            @endif
        </section>
    @endif

    <dl class="mt-4 grid gap-3 text-sm sm:grid-cols-3">
        @if($inquiry->client_phone)
            <div><dt class="text-gray-500">Телефон</dt><dd><x-phone :number="$inquiry->client_phone" /></dd></div>
        @endif
        @if($inquiry->client_email)
            <div><dt class="text-gray-500">Имейл</dt><dd class="font-semibold"><a href="mailto:{{ $inquiry->client_email }}" class="text-blue-600 hover:underline break-all">{{ $inquiry->client_email }}</a></dd></div>
        @endif
        @if($inquiry->website_url && ! $summary)
            <div><dt class="text-gray-500">Сайт</dt><dd class="font-semibold"><a href="{{ $inquiry->website_url }}" target="_blank" rel="noopener" class="text-blue-600 hover:underline break-all">{{ $inquiry->website_url }}</a></dd></div>
        @endif
        @if($inquiry->consultation_at)
            <div class="sm:col-span-3 rounded-md bg-amber-50 px-3 py-2"><dt class="text-amber-800">Консултация</dt><dd class="font-semibold text-gray-900">{{ \App\Support\Booking::label($inquiry->consultation_at) }}
                <a href="{{ \App\Support\Booking::googleCalendarUrl($inquiry->consultation_at, 'Консултация: ' . $customer, 'Телефон: ' . $inquiry->client_phone) }}" target="_blank" rel="noopener" class="ml-2 text-sm font-normal text-blue-600 hover:underline">добави в Google Календар</a></dd></div>
        @endif
    </dl>

    @unless($summary)
        <div class="mt-4">
            <p class="text-sm text-gray-500">Съобщение</p>
            <p class="mt-1 whitespace-pre-line rounded-md bg-white/80 border border-gray-100 p-3 text-gray-800">{{ $inquiry->description }}</p>
        </div>
    @endunless

    @if($inquiry->next_action && $inquiry->isOpenLead())
        <p class="mt-3 text-sm {{ $inquiry->isOverdue() ? 'font-semibold text-red-700' : 'text-gray-700' }}">→ Следваща стъпка: {{ $inquiry->next_action }}@if($inquiry->next_action_at) · {{ $inquiry->next_action_at->timezone('Europe/Sofia')->format('d.m H:i') }}@endif</p>
    @endif

    <details class="mt-4" @if($isNew) open @endif>
        <summary class="cursor-pointer text-sm font-semibold text-blue-600">Етап, кой го води и следваща стъпка</summary>
        <div class="mt-2">@include('admin.projects._pipeline', ['project' => $inquiry])</div>
    </details>

    <div class="mt-4 flex flex-wrap items-center justify-between gap-3 text-sm">
        <p class="{{ $inquiry->email_sent_at ? 'text-gray-500' : 'text-red-600 font-semibold' }}">
            @if($inquiry->email_sent_at)
                Имейл до {{ $notifyEmail }}: изпратен {{ $inquiry->email_sent_at->timezone('Europe/Sofia')->format('d.m.Y H:i') }}
            @else
                Имейлът до {{ $notifyEmail }} не е изпратен: прочетете запитването тук
            @endif
        </p>
        <div class="flex flex-wrap items-center gap-2">
            @if($inquiry->assigned_to !== auth()->id())
                <form action="{{ route('admin.inquiries.take', $inquiry) }}" method="POST">
                    @csrf
                    <button type="submit" class="rounded-lg bg-green-600 px-3 py-1.5 font-semibold text-white hover:bg-green-700">Поемам го</button>
                </form>
            @endif
            <a href="{{ route('admin.client-emails.index', ['project' => $inquiry->id]) }}" class="inline-flex items-center gap-1.5 rounded-lg bg-brand-950 px-3 py-1.5 font-semibold text-white hover:bg-brand-800"><x-icon name="paper-plane" class="text-xs" /> Изпрати имейл</a>
            <a href="{{ route('admin.projects.show', $inquiry) }}" class="rounded-lg border border-gray-300 bg-white px-3 py-1.5 font-semibold text-gray-700 hover:bg-gray-50">История</a>
            <form action="{{ route($isNew ? 'admin.inquiries.read' : 'admin.inquiries.unread', $inquiry) }}" method="POST">
                @csrf
                <button type="submit" class="rounded-lg border px-3 py-1.5 font-semibold {{ $isNew ? 'border-blue-300 bg-white text-blue-700 hover:bg-blue-100' : 'border-gray-300 bg-white text-gray-600 hover:bg-gray-50' }}">{{ $isNew ? 'Маркирай като прочетено' : 'Маркирай като непрочетено' }}</button>
            </form>
        </div>
    </div>
</article>
