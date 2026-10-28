@extends('admin.layout')

@section('title', 'Имейли до клиенти')

@section('content')
@if($project)
    <div class="mb-6 rounded-lg border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-900">
        Пишете на <strong>{{ str_replace('Запитване от ', '', $project->name) }}</strong>@if($project->client_email) ({{ $project->client_email }})@endif. Изберете шаблон.
        <a href="{{ route('admin.client-emails.index') }}" class="ml-2 text-blue-700 underline">Без конкретен клиент</a>
    </div>
@endif

<h3 class="text-base font-bold text-gray-900 mb-3">Изберете шаблон</h3>
<div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4 mb-10">
    @foreach($templates as $key => $tpl)
        <a href="{{ route('admin.client-emails.create', ['template' => $key] + ($project ? ['project' => $project->id] : [])) }}"
           class="group flex flex-col rounded-lg bg-white p-5 shadow hover:shadow-md hover:ring-2 hover:ring-brand-500 transition">
            <span class="inline-flex w-fit rounded-full px-2.5 py-0.5 text-xs font-semibold {{ isset($tpl['package']) ? 'bg-brand-950 text-white' : 'bg-gray-100 text-gray-700' }}">
                {{ isset($tpl['package']) ? 'Оферта' : 'Първи отговор' }}
            </span>
            <span class="mt-3 font-bold text-gray-900">{{ $tpl['label'] }}</span>
            <span class="mt-1 text-sm text-gray-600 flex-1">{{ $tpl['description'] }}</span>
            <span class="mt-4 text-sm font-semibold text-brand-700 group-hover:underline">Попълни и изпрати →</span>
        </a>
    @endforeach
</div>

<h3 class="text-base font-bold text-gray-900 mb-3">Изпратени</h3>
<div class="overflow-x-auto rounded-lg bg-white shadow">
    <table class="min-w-full text-sm">
        <thead class="bg-gray-50 text-left text-gray-600">
            <tr>
                <th class="px-4 py-3 font-semibold">Дата</th>
                <th class="px-4 py-3 font-semibold">До</th>
                <th class="px-4 py-3 font-semibold">Шаблон</th>
                <th class="px-4 py-3 font-semibold">Клиент</th>
                <th class="px-4 py-3 font-semibold">От</th>
                <th class="px-4 py-3 font-semibold">Статус</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($emails as $email)
                <tr>
                    <td class="px-4 py-3 whitespace-nowrap text-gray-600">{{ $email->created_at->format('d.m.Y H:i') }}</td>
                    <td class="px-4 py-3">{{ $email->to_email }}</td>
                    <td class="px-4 py-3">{{ $email->templateLabel() }}@if($email->attachment_name)<span class="block text-xs text-gray-500" title="Прикачен PDF">+ {{ $email->attachment_name }}</span>@endif</td>
                    <td class="px-4 py-3">
                        @if($email->project)
                            <a href="{{ route('admin.projects.show', $email->project) }}" class="text-blue-600 hover:underline">{{ str_replace('Запитване от ', '', $email->project->name) }}</a>
                        @else
                            {{ $email->data['client_name'] ?? '' }}
                        @endif
                    </td>
                    <td class="px-4 py-3 text-gray-600">{{ $email->user?->name }}</td>
                    <td class="px-4 py-3">
                        @if($email->sent_at)
                            <span class="rounded-full bg-green-100 px-2 py-0.5 text-xs font-semibold text-green-800">Изпратен</span>
                        @else
                            <span class="rounded-full bg-red-100 px-2 py-0.5 text-xs font-semibold text-red-800" title="{{ $email->error }}">Грешка</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-4 py-8 text-center text-gray-500">Още няма изпратени имейли.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-6">{{ $emails->links() }}</div>
@endsection
