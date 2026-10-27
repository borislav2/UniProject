@extends('admin.layout')

@section('title', $project->name)

@section('content')
<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
    <h2 class="text-2xl font-bold text-gray-800 flex flex-wrap items-center gap-3">
        {{ $project->name }}
        <span class="px-3 py-1 text-xs rounded-full font-semibold {{ $project->statusClass() }}">{{ $project->statusLabel() }}</span>
        @if($project->source === 'website')
            <span class="px-3 py-1 text-xs rounded-full bg-blue-100 text-blue-800 font-medium">Запитване от сайта</span>
        @endif
    </h2>
    <div class="flex flex-wrap gap-2">
        <a href="{{ route('admin.client-emails.index', ['project' => $project->id]) }}" class="inline-flex items-center px-4 py-2 bg-brand-950 text-white rounded hover:bg-brand-800">
            <x-icon name="paper-plane" class="mr-2" />Изпрати имейл
        </a>
        <a href="{{ route('admin.projects.edit', $project) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-gray-800 rounded hover:bg-gray-50">
            <x-icon name="edit" class="mr-2" />Редактирай
        </a>
        <form action="{{ route('admin.projects.destroy', $project) }}" method="POST" class="inline" onsubmit="return confirm('Да изтрием ли проекта „{{ $project->name }}“? Това не може да се върне.')">
            @csrf
            @method('DELETE')
            <button type="submit" class="inline-flex items-center px-4 py-2 text-red-600 rounded hover:bg-red-50">
                <x-icon name="trash" class="mr-2" />Изтрий
            </button>
        </form>
    </div>
</div>

@if($project->client_phone || $project->client_email || $project->website_url || $project->consultation_at)
    {{-- Контактът с клиента е най-отгоре: за обаждане или имейл с едно докосване. --}}
    <div class="mb-6 grid gap-4 rounded-lg bg-white p-6 shadow sm:grid-cols-2 lg:grid-cols-4">
        @if($project->client_phone)
            <div>
                <h4 class="text-sm font-medium text-gray-500">Телефон</h4>
                <x-phone :number="$project->client_phone" class="text-lg" />
            </div>
        @endif
        @if($project->client_email)
            <div class="min-w-0">
                <h4 class="text-sm font-medium text-gray-500">Имейл</h4>
                <a href="mailto:{{ $project->client_email }}" class="block truncate text-lg font-semibold text-blue-600 hover:underline">{{ $project->client_email }}</a>
            </div>
        @endif
        @if($project->website_url)
            <div class="min-w-0">
                <h4 class="text-sm font-medium text-gray-500">Сайт</h4>
                <a href="{{ $project->website_url }}" target="_blank" rel="noopener" class="block truncate text-lg font-semibold text-blue-600 hover:underline">{{ parse_url($project->website_url, PHP_URL_HOST) ?: $project->website_url }}</a>
            </div>
        @endif
        @if($project->consultation_at)
            <div class="rounded-lg bg-yellow-50 p-3">
                <h4 class="text-sm font-medium text-yellow-800">Консултация</h4>
                <p class="font-semibold text-gray-900">{{ \App\Support\Booking::label($project->consultation_at) }}</p>
            </div>
        @endif
    </div>
@endif

@if($project->stage)
    <div class="mb-6 rounded-lg bg-white p-5 shadow">
        <h3 class="mb-3 font-semibold text-gray-800">Продажба</h3>
        @include('admin.projects._pipeline')
    </div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2">
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Описание</h3>
            <p class="whitespace-pre-line text-gray-900">{{ $project->description }}</p>

            <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <h4 class="text-sm font-medium text-gray-500">Отговорник</h4>
                    <p class="text-gray-900">{{ $project->manager }}</p>
                </div>
                <div>
                    <h4 class="text-sm font-medium text-gray-500">Категория</h4>
                    <p class="text-gray-900">{{ $project->category?->name }}</p>
                </div>
                <div>
                    <h4 class="text-sm font-medium text-gray-500">Период</h4>
                    <p class="text-gray-900">
                        {{ $project->start_date->format('d.m.Y') }}
                        @if($project->end_date)
                            – {{ $project->end_date->format('d.m.Y') }}
                            ({{ (int) $project->start_date->diffInDays($project->end_date) }} дни)
                        @else
                            – досега ({{ (int) $project->start_date->diffInDays(now()) }} дни)
                        @endif
                    </p>
                </div>
                @if($project->is_public)
                    <div>
                        <h4 class="text-sm font-medium text-gray-500">Портфолио</h4>
                        <p class="text-gray-900">Показва се на /proekti</p>
                    </div>
                @endif
            </div>
        </div>

        @if($project->technologies->count() > 0)
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Технологии</h3>
                <div class="flex flex-wrap gap-2">
                    @foreach($project->technologies as $technology)
                        <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm">
                            {{ $technology->name }} {{ $technology->version ? "({$technology->version})" : '' }}
                        </span>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    <div>
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Информация</h3>
            <div class="space-y-3">
                @if($project->service)
                    <div>
                        <h4 class="text-sm font-medium text-gray-500">Услуга</h4>
                        <p class="text-gray-900 font-semibold">{{ $project->serviceLabel() }}</p>
                    </div>
                @endif
                @if($project->business_size)
                    <div>
                        <h4 class="text-sm font-medium text-gray-500">Размер на бизнеса</h4>
                        <p class="text-gray-900 font-semibold">{{ $project->business_size }}</p>
                    </div>
                @endif
                @if($project->locale === 'en')
                    <div>
                        <h4 class="text-sm font-medium text-gray-500">Език</h4>
                        <p class="text-gray-900 font-semibold">Английски (от /en): отговорете на английски</p>
                    </div>
                @endif
                @if($project->source === 'website')
                    <div>
                        <h4 class="text-sm font-medium text-gray-500">Канал</h4>
                        <p class="text-gray-900 font-semibold">{{ $project->lead_channel ?? 'Директно' }}</p>
                        @if($project->utm_source || $project->utm_medium || $project->utm_campaign || $project->referrer)
                            <dl class="mt-1 text-xs text-gray-500 space-y-0.5">
                                @if($project->utm_source)<div>utm_source: {{ $project->utm_source }}</div>@endif
                                @if($project->utm_medium)<div>utm_medium: {{ $project->utm_medium }}</div>@endif
                                @if($project->utm_campaign)<div>utm_campaign: {{ $project->utm_campaign }}</div>@endif
                                @if($project->referrer)<div>Препращащ сайт: {{ $project->referrer }}</div>@endif
                            </dl>
                        @endif
                    </div>
                @endif
                <div>
                    <h4 class="text-sm font-medium text-gray-500">Създаден</h4>
                    <p class="text-gray-900">{{ $project->created_at->timezone('Europe/Sofia')->format('d.m.Y H:i') }}</p>
                </div>
                <div>
                    <h4 class="text-sm font-medium text-gray-500">Последна промяна</h4>
                    <p class="text-gray-900">{{ $project->updated_at->timezone('Europe/Sofia')->format('d.m.Y H:i') }}</p>
                </div>
                @if($project->file_path)
                    <div>
                        <h4 class="text-sm font-medium text-gray-500">Файл</h4>
                        <a href="{{ asset($project->file_path) }}" target="_blank"
                           class="text-blue-600 hover:underline flex items-center">
                            <x-icon name="file" class="mr-2" />
                            {{ basename($project->file_path) }}
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<section class="mt-6 rounded-lg bg-white p-6 shadow">
    <h3 class="text-lg font-semibold text-gray-800">История</h3>
    <form action="{{ route('admin.projects.notes', $project) }}" method="POST" class="mt-3 flex flex-col gap-2 sm:flex-row">
        @csrf
        <select name="kind" class="rounded-lg border border-gray-300 px-3 py-2 text-sm" aria-label="Вид">
            <option value="call">Разговор</option>
            <option value="note">Бележка</option>
        </select>
        <textarea name="body" rows="1" required maxlength="2000" placeholder="Какво говорихте, какво иска клиентът, бюджет, срок…" class="min-w-0 flex-1 rounded-lg border border-gray-300 px-3 py-2 text-sm"></textarea>
        <button class="rounded-lg bg-gray-800 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-700">Добави</button>
    </form>
    <ol class="mt-5 space-y-4 border-l-2 border-gray-100 pl-4">
        @foreach($timeline as $item)
            <li class="relative">
                <span class="absolute -left-[1.4rem] top-1.5 h-2.5 w-2.5 rounded-full bg-brand-500"></span>
                <p class="text-xs text-gray-500"><span class="font-semibold text-gray-700">{{ $item['kind'] }}</span> · {{ $item['at']->timezone('Europe/Sofia')->format('d.m.Y H:i') }}@if($item['user']) · {{ $item['user'] }}@endif</p>
                <p class="whitespace-pre-line text-sm text-gray-800">{{ $item['text'] }}@if($item['url']) <a href="{{ $item['url'] }}" class="text-blue-600 hover:underline">Отвори</a>@endif</p>
            </li>
        @endforeach
    </ol>
</section>

<div class="mt-6">
    <a href="{{ route('admin.projects.index') }}" class="text-blue-600 hover:underline">
        <x-icon name="arrow-left" class="mr-2" />Към проектите
    </a>
</div>
@endsection
