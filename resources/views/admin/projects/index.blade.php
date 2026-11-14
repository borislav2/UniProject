@extends('admin.layout')

@section('title', 'Проекти')

@section('content')
<div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <form action="{{ route('admin.projects.search') }}" method="GET" class="flex w-full sm:max-w-sm">
        <input type="search" name="search" placeholder="Търсене по име, клиент, телефон…"
               value="{{ $search ?? '' }}"
               class="min-w-0 flex-1">
        <button type="submit" class="adm-btn" style="height:auto" aria-label="Търси">
            <x-icon name="search" />
        </button>
    </form>
    <a href="{{ route('admin.projects.create') }}" class="adm-btn justify-center">
        <x-icon name="plus" class="mr-2" />Нов проект
    </a>
</div>

<p class="mb-3 text-sm text-gray-600">Проектите, които правим за клиенти: създадени тук или спечелени запитвания (етап „Спечелен“). Запитванията са в <a href="{{ route('admin.inquiries.index') }}" class="font-semibold text-blue-600 hover:underline">„Запитвания“</a>.</p>
<nav class="mb-4 flex flex-wrap gap-2 text-sm">
    @foreach($filters as $key => [$label, $count])
        <a href="{{ route('admin.projects.index', array_filter(['filter' => $key])) }}" class="rounded-full px-4 py-1.5 font-semibold {{ ($filter ?? '') === $key && ! isset($search) ? 'bg-gray-800 text-white' : 'bg-white text-gray-700 border border-gray-200 hover:bg-gray-50' }}">{{ $label }} ({{ $count }})</a>
    @endforeach
</nav>

@if(isset($search))
    <div class="adm-flash ok justify-between items-center">
        <span>Резултати за: <strong>{{ $search }}</strong></span>
        <a href="{{ route('admin.projects.index') }}" class="text-blue-600 hover:underline">Изчисти</a>
    </div>
@endif

<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="min-w-full block md:table">
        <thead class="bg-gray-50 hidden md:table-header-group">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-full">Проект</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Категория</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Статус</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Начало</th>
                <th class="px-6 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 block md:table-row-group">
            @forelse($projects as $project)
                <tr class="hover:bg-gray-50 block md:table-row">
                    <td class="block md:table-cell px-4 md:px-6 py-3 md:w-full">
                        <div class="flex flex-wrap items-center gap-2 text-sm font-medium">
                            <a href="{{ route('admin.projects.show', $project) }}" class="font-semibold text-gray-900 hover:text-brand-800">{{ $project->name }}</a>
                            @if($project->stage)
                                <span class="px-2 py-0.5 text-xs rounded-full font-semibold {{ $project->stageClass() }}">{{ $project->stageLabel() }}</span>
                            @endif
                            @if($project->isOverdue())
                                <span class="px-2 py-0.5 text-xs rounded-full bg-red-100 text-red-700 font-semibold">Просрочена стъпка</span>
                            @endif
                            @if($project->source === 'website')
                                <span class="px-2 py-0.5 text-xs rounded-full bg-blue-100 text-blue-800">От сайта</span>
                                @if($project->lead_channel)
                                    <span class="px-2 py-0.5 text-xs rounded-full bg-gray-100 text-gray-700" title="Канал">{{ $project->lead_channel }}</span>
                                @endif
                            @endif
                            <span class="md:hidden px-2 py-0.5 text-xs rounded-full font-semibold {{ $project->statusClass() }}">{{ $project->statusLabel() }}</span>
                            @if($project->assignee)<span class="px-2 py-0.5 text-xs rounded-full bg-gray-100 text-gray-600" title="Води го">{{ $project->assignee->name }}</span>@endif
                        </div>
                        <div class="mt-0.5 text-sm text-gray-500 line-clamp-1">{{ $project->locale === 'en' ? 'EN · ' : '' }}{{ $project->serviceLabel() ? Str::limit($project->serviceLabel(), 40) . ' · ' : '' }}{{ Str::limit($project->description, 70) }}</div>
                        @if($project->isOpenLead() && $project->next_action)
                            <div class="mt-1 text-sm {{ $project->isOverdue() ? 'text-red-700' : 'text-gray-700' }}">→ {{ $project->next_action }}@if($project->next_action_at) · {{ $project->next_action_at->timezone('Europe/Sofia')->format('d.m H:i') }}@endif</div>
                        @endif
                        @if($project->client_phone || $project->client_email)
                            <div class="mt-1 flex flex-wrap gap-x-4 text-sm">
                                @if($project->client_phone)<a href="tel:{{ preg_replace('/[^\d+]/', '', $project->client_phone) }}" class="text-gray-700">{{ $project->client_phone }}</a>@endif
                                @if($project->client_email)<a href="mailto:{{ $project->client_email }}" class="text-gray-700">{{ $project->client_email }}</a>@endif
                            </div>
                        @endif
                        <a href="{{ route('admin.projects.edit', $project) }}" class="adm-btn ghost sm mt-3 md:hidden">Редактирай</a>
                    </td>
                    <td class="hidden md:table-cell px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $project->category?->name }}</td>
                    <td class="hidden md:table-cell px-6 py-4 whitespace-nowrap">
                        <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $project->statusClass() }}">{{ $project->statusLabel() }}</span>
                    </td>
                    <td class="hidden md:table-cell px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $project->start_date->format('d.m.Y') }}</td>
                    <td class="hidden md:table-cell px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <a href="{{ route('admin.projects.edit', $project) }}" class="adm-btn ghost sm">Редактирай</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="block md:table-cell px-6 py-8 text-center text-gray-500">Няма проекти тук.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if($projects->hasPages())
        <div class="px-6 py-4 border-t">
            {{ $projects->links() }}
        </div>
    @endif
</div>
@endsection
