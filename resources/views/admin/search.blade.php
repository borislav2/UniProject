@extends('admin.layout')

@section('title', 'Търсене')

@section('content')
<form action="{{ route('admin.search') }}" method="GET" class="mb-6 flex max-w-2xl">
    <input type="search" name="q" value="{{ $q }}" autofocus placeholder="Име, телефон, имейл, сайт…" class="min-w-0 flex-1 rounded-l-lg border border-gray-300 px-4 py-3 text-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
    <button class="rounded-r-lg bg-gray-800 px-5 text-white hover:bg-gray-700" aria-label="Търси"><x-icon name="search" /></button>
</form>

@if(mb_strlen($q) >= 2 && ! $results)
    <p class="rounded-lg bg-white p-6 text-gray-500 shadow">Няма нищо за „{{ $q }}“.</p>
@elseif(mb_strlen($q) < 2)
    <p class="text-sm text-gray-500">Търси в запитванията и проектите, потенциалните клиенти, обажданията, разговорите с Крео и статиите. Телефонът може да е с или без интервали. Отвсякъде: Ctrl+K.</p>
@endif

@foreach($results as $group => $items)
    <section class="mb-6">
        <h2 class="mb-2 text-sm font-semibold uppercase tracking-wider text-gray-500">{{ $group }}</h2>
        <ul class="divide-y overflow-hidden rounded-lg bg-white shadow">
            @foreach($items as $item)
                <li><a href="{{ $item['url'] }}" class="block px-4 py-3 hover:bg-gray-50"><span class="font-semibold text-gray-900">{{ $item['title'] }}</span>@if($item['meta'])<span class="block text-sm text-gray-500">{{ $item['meta'] }}</span>@endif</a></li>
            @endforeach
        </ul>
    </section>
@endforeach
@endsection
