@extends('admin.layout')

@section('title', 'Днес')

@php
    $sofia = fn ($at) => $at->timezone('Europe/Sofia');
    $waiting = function ($at) {
        $hours = (int) $at->diffInHours(now());
        return $hours < 1 ? 'току-що' : ($hours < 24 ? "чака {$hours} ч" : 'чака ' . intdiv($hours, 24) . ' дни');
    };
@endphp

@section('content')
{{-- Денят на един човек: отгоре какво има да свърши (по спешност), долу какво е направил днес. --}}
@if($team->count() > 1)
    <nav class="mb-4 flex flex-wrap gap-2 text-sm" aria-label="Чий ден">
        @foreach($team as $person)
            @php $active = $member?->id === $person->id; @endphp
            <a href="{{ route('admin.today', ['user' => $person->id]) }}" class="inline-flex items-center gap-2 rounded-full px-4 py-1.5 font-semibold {{ $active ? 'bg-gray-800 text-white' : 'bg-white text-gray-700 border border-gray-200 hover:bg-gray-50' }}">
                {{ $person->id === auth()->id() ? 'Моят ден' : $person->name }}
                @if($todoByMember[$person->id] ?? 0)<span class="rounded-full px-1.5 text-xs {{ $active ? 'bg-white/20' : 'bg-red-100 text-red-700' }}">{{ $todoByMember[$person->id] }}</span>@endif
            </a>
        @endforeach
        <a href="{{ route('admin.today', ['user' => 'all']) }}" class="rounded-full px-4 py-1.5 font-semibold {{ $member ? 'bg-white text-gray-700 border border-gray-200 hover:bg-gray-50' : 'bg-gray-800 text-white' }}">Целият екип</a>
    </nav>
@endif
<h2 class="mb-1 text-xl font-bold text-gray-900">{{ $member ? ($member->id === auth()->id() ? 'Вашият ден' : 'Денят на ' . $member->name) : 'Денят на екипа' }}, {{ now('Europe/Sofia')->locale('bg')->isoFormat('dddd, D MMMM') }}</h2>
<p class="mb-5 text-sm text-gray-500">{{ $member ? 'Запитванията, които води ' . ($member->id === auth()->id() ? 'вие' : $member->name) . ', и тези без човек (поемете ги).' : 'Всичко отворено, на всички.' }}</p>

<div class="mb-6 grid grid-cols-2 gap-3 sm:grid-cols-4 text-sm">
    <div class="rounded-lg bg-white p-4 shadow"><p class="text-gray-500">Непрочетени</p><p class="text-2xl font-bold {{ $unread->isNotEmpty() ? 'text-blue-600' : 'text-gray-900' }}">{{ $unread->count() }}</p></div>
    <div class="rounded-lg bg-white p-4 shadow"><p class="text-gray-500">Стъпки за днес</p><p class="text-2xl font-bold {{ $due->contains(fn ($p) => $p->isOverdue()) ? 'text-red-600' : 'text-gray-900' }}">{{ $due->count() }}</p></div>
    <a href="{{ route('admin.outreach.calls.next') }}" class="rounded-lg bg-white p-4 shadow hover:ring-2 hover:ring-blue-200"><p class="text-gray-500">Обаждания</p><p class="text-2xl font-bold text-gray-900">{{ $calls['total'] }} <span class="text-sm font-medium text-gray-400">/ {{ $calls['goal'] }}</span></p>@if($callbacks)<p class="text-xs font-semibold text-orange-600">{{ $callbacks }} отново днес</p>@endif</a>
    <div class="rounded-lg bg-white p-4 shadow"><p class="text-gray-500">Кликнали по писмата</p><p class="text-2xl font-bold {{ $hot->isNotEmpty() ? 'text-green-700' : 'text-gray-900' }}">{{ $hot->count() }}</p></div>
</div>

@if($consultations->isNotEmpty())
    <section class="mb-6">
        <h2 class="mb-2 text-lg font-semibold text-gray-800">Консултации</h2>
        <div class="space-y-2">
            @foreach($consultations as $item)
                @php $at = $sofia($item->consultation_at); @endphp
                <div class="flex flex-wrap items-center justify-between gap-2 rounded-lg border-l-4 {{ $at->isToday() ? 'border-orange-500' : 'border-yellow-300' }} bg-white p-4 shadow">
                    <div>
                        <p class="font-semibold text-gray-900">{{ $at->isToday() ? 'Днес' : 'Утре' }}, {{ $at->format('H:i') }} · <a href="{{ route('admin.projects.show', $item) }}" class="hover:text-blue-600">{{ $item->name }}</a></p>
                        <p class="text-sm text-gray-500">{{ \Illuminate\Support\Str::limit($item->serviceLabel(), 70) }}</p>
                    </div>
                    <x-phone :number="$item->client_phone" button class="text-sm" />
                </div>
            @endforeach
        </div>
    </section>
@endif

<section class="mb-6">
    <h2 class="mb-2 text-lg font-semibold text-gray-800">Нови запитвания <span class="text-sm font-normal text-gray-500">(отговорете възможно най-бързо)</span></h2>
    @forelse($unread as $lead)
        <article class="mb-3 rounded-lg border-l-4 border-blue-500 bg-white p-4 shadow">
            <div class="flex flex-wrap items-start justify-between gap-2">
                <div class="min-w-0">
                    <a href="{{ route('admin.projects.show', $lead) }}" class="font-semibold text-gray-900 hover:text-blue-600">{{ $lead->name }}</a>
                    <span class="ml-1 text-xs font-semibold {{ $lead->created_at->lt(now()->subHours(4)) ? 'text-red-600' : 'text-gray-500' }}">{{ $waiting($lead->created_at) }}</span>
                    @if($lead->leadKind())<span class="ml-1 rounded-full px-2 py-0.5 text-xs font-semibold {{ $lead->leadKindClass() }}">{{ $lead->leadKindLabel() }}</span>@endif
                    @if($lead->assignee)<span class="ml-1 rounded-full bg-gray-800 px-2 py-0.5 text-xs font-semibold text-white">{{ $lead->assignee->name }}</span>@else<span class="ml-1 rounded-full border border-dashed border-orange-400 px-2 py-0.5 text-xs font-semibold text-orange-700">Без човек</span>@endif
                    <p class="text-sm text-gray-500">{{ collect([\Illuminate\Support\Str::limit($lead->serviceLabel(), 60), $lead->lead_channel])->filter()->join(' · ') }}</p>
                    @php $check = $lead->siteCheckSummary(); @endphp
                    @if($check && $check['score'] !== null)
                        <p class="mt-1 text-sm font-semibold {{ $check['score'] >= 80 ? 'text-green-700' : ($check['score'] >= 50 ? 'text-amber-700' : 'text-red-700') }}">Оценка {{ $check['score'] }}/100 · {{ count($check['fail']) }} {{ count($check['fail']) === 1 ? 'проблем' : 'проблема' }}@if($check['fail']): {{ collect($check['fail'])->pluck('title')->take(2)->join(', ') }}@endif</p>
                    @else
                        <p class="mt-1 line-clamp-2 text-sm text-gray-700">{{ $lead->description }}</p>
                    @endif
                </div>
                <div class="flex flex-wrap gap-2 text-sm">
                    @unless($lead->assigned_to)<form action="{{ route('admin.inquiries.take', $lead) }}" method="POST">@csrf<button class="rounded-lg bg-green-600 px-3 py-1.5 font-semibold text-white hover:bg-green-700">Поемам го</button></form>@endunless
                    <x-phone :number="$lead->client_phone" button class="text-sm" />
                    <a href="{{ route('admin.client-emails.index', ['project' => $lead->id]) }}" class="rounded-lg border border-gray-300 px-3 py-1.5 font-semibold text-gray-700 hover:bg-gray-50">Имейл</a>
                </div>
            </div>
            <details class="mt-3"><summary class="cursor-pointer text-sm font-semibold text-blue-600">Обнови етапа и следващата стъпка</summary><div class="mt-2">@include('admin.projects._pipeline', ['project' => $lead])</div></details>
        </article>
    @empty
        <p class="rounded-lg bg-white p-4 text-sm text-gray-500 shadow">Няма непрочетени запитвания.</p>
    @endforelse
</section>

<section class="mb-6">
    <h2 class="mb-2 text-lg font-semibold text-gray-800">Следващи стъпки за днес</h2>
    @forelse($due as $lead)
        <article class="mb-3 rounded-lg border-l-4 {{ $lead->isOverdue() && ! $sofia($lead->next_action_at)->isToday() ? 'border-red-500' : 'border-gray-300' }} bg-white p-4 shadow">
            <div class="flex flex-wrap items-start justify-between gap-2">
                <div class="min-w-0">
                    <a href="{{ route('admin.projects.show', $lead) }}" class="font-semibold text-gray-900 hover:text-blue-600">{{ $lead->name }}</a>
                    <span class="ml-1 rounded-full px-2 py-0.5 text-xs font-semibold {{ $lead->stageClass() }}">{{ $lead->stageLabel() }}</span>
                    <p class="mt-1 text-sm {{ $sofia($lead->next_action_at)->isToday() ? 'text-gray-800' : 'font-semibold text-red-700' }}">→ {{ $lead->next_action ?: 'Свържете се' }} · {{ $sofia($lead->next_action_at)->isToday() ? 'днес ' . $sofia($lead->next_action_at)->format('H:i') : 'от ' . $sofia($lead->next_action_at)->format('d.m') }}</p>
                </div>
                <x-phone :number="$lead->client_phone" button class="text-sm" />
            </div>
            <details class="mt-3"><summary class="cursor-pointer text-sm font-semibold text-blue-600">Готово? Запишете следващата стъпка</summary><div class="mt-2">@include('admin.projects._pipeline', ['project' => $lead])</div></details>
        </article>
    @empty
        <p class="rounded-lg bg-white p-4 text-sm text-gray-500 shadow">Няма стъпки за днес.</p>
    @endforelse
</section>

@if($linkedinDue->isNotEmpty())
    <section class="mb-6">
        <h2 class="mb-2 text-lg font-semibold text-gray-800">LinkedIn: време е да напишете <span class="text-sm font-normal text-gray-500">(<a href="{{ route('admin.linkedin.index', ['due' => 1]) }}" class="text-blue-600 hover:underline">всички за днес →</a>)</span></h2>
        <div class="overflow-hidden rounded-lg bg-white shadow">
            <ul class="divide-y">
                @foreach($linkedinDue->take(15) as $contact)
                    <li class="flex flex-wrap items-center justify-between gap-2 px-4 py-3 text-sm">
                        <div class="min-w-0">
                            <a href="{{ $contact->profile_url }}" target="_blank" rel="noopener noreferrer" class="font-semibold text-gray-900 hover:text-blue-600">{{ $contact->name }}</a>
                            <span class="ml-1 rounded-full px-2 py-0.5 text-xs font-semibold {{ $contact->statusClass() }}">{{ $contact->statusLabel() }}</span>
                            <p class="{{ $contact->next_action_at->toDateString() < now('Europe/Sofia')->toDateString() ? 'font-semibold text-red-700' : 'text-gray-500' }}">{{ $contact->next_action_at->toDateString() < now('Europe/Sofia')->toDateString() ? 'от ' . $contact->next_action_at->format('d.m') : 'днес' }}@if($contact->note) · {{ \Illuminate\Support\Str::limit($contact->note, 90) }}@endif</p>
                        </div>
                        <a href="{{ route('admin.linkedin.index', ['due' => 1, 'q' => $contact->name]) }}" class="text-blue-600 hover:underline">Отбележи →</a>
                    </li>
                @endforeach
            </ul>
        </div>
    </section>
@endif

@if($followups)
    <a href="{{ route('admin.outreach.index', ['followup' => 1]) }}" class="mb-6 flex items-center justify-between rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-semibold text-amber-900 hover:bg-amber-100">
        <span>{{ $followups }} {{ $followups === 1 ? 'бизнес чака' : 'бизнеса чакат' }} напомняне (без отговор {{ config('creatium.outreach.followup_after_days') }}+ дни)</span><span>Напомни →</span>
    </a>
@endif

@if($hot->isNotEmpty())
    <section class="mb-6">
        <h2 class="mb-2 text-lg font-semibold text-gray-800">Кликнаха по писмото <span class="text-sm font-normal text-gray-500">(топли контакти, обадете се или пишете)</span></h2>
        <div class="overflow-hidden rounded-lg bg-white shadow">
            <ul class="divide-y">
                @foreach($hot as $prospect)
                    <li class="flex flex-wrap items-center justify-between gap-2 px-4 py-3 text-sm">
                        <div class="min-w-0">
                            <p class="font-semibold text-gray-900">{{ $prospect->displayName() }}</p>
                            <p class="text-gray-500">Кликна: {{ \App\Models\OutreachProspect::CLICK_LINKS[$prospect->last_click_link] ?? $prospect->last_click_link }} · {{ $sofia($prospect->last_clicked_at)->format('d.m H:i') }}@if($prospect->click_count > 1) ({{ $prospect->click_count }}×)@endif</p>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            @if($prospect->phone)<x-phone :number="$prospect->phone" />@endif
                            <a href="mailto:{{ $prospect->email }}" class="text-blue-600 hover:underline">{{ $prospect->email }}</a>
                            @if($prospect->project_id)<a href="{{ route('admin.projects.show', $prospect->project_id) }}" class="inline-flex items-center rounded-lg bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-800 hover:bg-blue-100">Запитване →</a>@else<form action="{{ route('admin.outreach.lead', $prospect) }}" method="POST" class="inline">@csrf<button class="rounded-lg border border-blue-300 px-2.5 py-1 text-xs font-semibold text-blue-700 hover:bg-blue-50" title="Прояви интерес: направи запитване с етапи и следваща стъпка">→ Запитване</button></form>@endif
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
    </section>
@endif

@if($clientIssues->isNotEmpty())
    <section class="mb-6">
        <h2 class="mb-2 text-lg font-semibold text-gray-800">Клиенти на абонамент <span class="text-sm font-normal text-gray-500">(плащания и сайтове)</span></h2>
        <div class="overflow-hidden rounded-lg bg-white shadow">
            <ul class="divide-y">
                @foreach($clientIssues as $row)
                    <li class="flex flex-wrap items-center justify-between gap-2 px-4 py-3 text-sm">
                        <div class="min-w-0">
                            <a href="{{ route('admin.clients.index') }}" class="font-semibold text-gray-900 hover:text-blue-600">{{ $row['client']->name }}</a>
                            <div class="mt-1 flex flex-wrap gap-1.5">
                                @if($row['payment']['state'] === 'late')<span class="rounded-full bg-red-100 px-2 py-0.5 text-xs font-semibold text-red-800">Плащането закъснява {{ $row['payment']['days'] }} дни</span>@endif
                                @foreach($row['problems'] as $problem)<span class="rounded-full bg-red-100 px-2 py-0.5 text-xs font-semibold text-red-800">{{ $problem }}</span>@endforeach
                            </div>
                        </div>
                        <x-phone :number="$row['client']->client_phone" />
                    </li>
                @endforeach
            </ul>
        </div>
    </section>
@endif

@if($withoutNextStep->isNotEmpty())
    <section class="mb-6">
        <h2 class="mb-2 text-lg font-semibold text-gray-800">Без следваща стъпка <span class="text-sm font-normal text-gray-500">(лесно се забравят)</span></h2>
        @foreach($withoutNextStep as $lead)
            <article class="mb-3 rounded-lg border-l-4 border-orange-400 bg-white p-4 shadow">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <div>
                        <a href="{{ route('admin.projects.show', $lead) }}" class="font-semibold text-gray-900 hover:text-blue-600">{{ $lead->name }}</a>
                        <span class="ml-1 rounded-full px-2 py-0.5 text-xs font-semibold {{ $lead->stageClass() }}">{{ $lead->stageLabel() }}</span>
                    </div>
                    <x-phone :number="$lead->client_phone" button class="text-sm" />
                </div>
                <details class="mt-3"><summary class="cursor-pointer text-sm font-semibold text-blue-600">Запишете следваща стъпка</summary><div class="mt-2">@include('admin.projects._pipeline', ['project' => $lead])</div></details>
            </article>
        @endforeach
    </section>
@endif
{{-- Направено днес: разговори, имейли, писма, смени на етапа. --}}
<section class="mb-6">
    <h2 class="mb-2 text-lg font-semibold text-gray-800">Направено днес <span class="text-sm font-normal text-gray-500">({{ $member ? ($member->id === auth()->id() ? 'от вас' : 'от ' . $member->name) : 'от целия екип' }})</span></h2>
    <div class="mb-3 grid grid-cols-2 gap-3 text-sm sm:grid-cols-4 lg:grid-cols-7">
        @foreach([['calls', 'Обаждания', 'text-green-700'], ['emails', 'Имейли до клиенти', 'text-blue-700'], ['outreach', 'Писма до бизнеси', 'text-indigo-700'], ['linkedin_invites', 'Покани в LinkedIn', 'text-sky-700'], ['linkedin_messages', 'Съобщения в LinkedIn', 'text-sky-700'], ['stages', 'Сменени етапи', 'text-purple-700'], ['notes', 'Бележки', 'text-gray-700']] as [$key, $label, $color])
            <div class="rounded-lg bg-white p-3 shadow"><p class="text-gray-500">{{ $label }}</p><p class="text-2xl font-bold {{ $doneCounts[$key] ? $color : 'text-gray-300' }}">{{ $doneCounts[$key] }}</p></div>
        @endforeach
    </div>
    @if($done->isEmpty())
        <p class="rounded-lg bg-white p-4 text-sm text-gray-500 shadow">Още нищо записано днес. Обажданията, имейлите и смените на етапа се появяват тук сами.</p>
    @else
        <ol class="divide-y overflow-hidden rounded-lg bg-white shadow">
            @foreach($done->take(60) as $entry)
                @php $dot = ['call' => 'bg-green-500', 'outreach-call' => 'bg-green-500', 'email' => 'bg-blue-500', 'outreach' => 'bg-indigo-500', 'linkedin-invite' => 'bg-sky-500', 'linkedin-message' => 'bg-sky-500', 'stage' => 'bg-purple-500', 'note' => 'bg-gray-400'][$entry['type']] ?? 'bg-gray-400'; @endphp
                <li class="flex items-start gap-3 px-4 py-2.5 text-sm">
                    <span class="w-11 shrink-0 font-mono text-gray-500">{{ $entry['at']->timezone('Europe/Sofia')->format('H:i') }}</span>
                    <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full {{ $dot }}"></span>
                    <span class="min-w-0">
                        <span class="font-semibold text-gray-900">{{ $entry['label'] }}</span>@if(! $member && $entry['user'])<span class="text-gray-500"> · {{ $entry['user'] }}</span>@endif
                        <span class="block text-gray-600">@if($entry['url'])<a href="{{ $entry['url'] }}" class="hover:text-blue-600">{{ $entry['text'] }}</a>@else{{ $entry['text'] }}@endif</span>
                    </span>
                </li>
            @endforeach
        </ol>
    @endif
</section>
@endsection
