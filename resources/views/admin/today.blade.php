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
{{-- Всичко за вършене днес, по спешност. Празен списък = свършена работа. --}}
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
                @php($at = $sofia($item->consultation_at))
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
                    <p class="text-sm text-gray-500">{{ collect([\Illuminate\Support\Str::limit($lead->serviceLabel(), 60), $lead->lead_channel])->filter()->join(' · ') }}</p>
                    <p class="mt-1 line-clamp-2 text-sm text-gray-700">{{ $lead->description }}</p>
                </div>
                <div class="flex flex-wrap gap-2 text-sm">
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
@endsection
