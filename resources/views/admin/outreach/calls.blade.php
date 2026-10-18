@extends('admin.layout')

@section('title', 'Потенциални клиенти')

@section('content')
@include('admin.outreach._tabs')

@if(session('send_error'))
    <div class="mb-6 rounded-lg border border-red-300 bg-red-50 px-4 py-3 text-sm text-red-800" role="alert">{{ session('send_error') }}</div>
@endif

<div class="mb-8 rounded-lg bg-white p-5 shadow">
    <h3 class="font-bold text-gray-900">Бизнеси без сайт</h3>
    <p class="mt-1 text-sm text-gray-600">Harvey ги намира в картата (OpenStreetMap), но без сайт няма откъде да вземе имейл. Затова са за обаждане: точно на тях им трябва това, което правим.</p>
    <form action="{{ route('admin.outreach.calls.import') }}" method="POST" class="mt-4 flex flex-wrap items-center gap-3">
        @csrf
        <button class="inline-flex items-center gap-2 rounded-lg bg-brand-950 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-800"><x-icon name="rotate-left" /> Внеси от сървъра</button>
        @if($serverCsv)
            <span class="text-sm text-gray-600">Harvey на сървъра изнесе списък на {{ $serverCsv->timezone('Europe/Sofia')->format('d.m.Y H:i') }}.</span>
        @else
            <span class="text-sm text-gray-600">Още няма списък на сървъра. Изнася се всеки ден заедно с имейлите (веднага: <code class="rounded bg-gray-100 px-1">sudo systemctl start harvey-export</code>).</span>
        @endif
    </form>
</div>

<nav class="mb-4 flex flex-wrap gap-2 text-sm">
    @php $base = array_filter(['city' => $filters['city'], 'industry' => $filters['industry'], 'phone' => $filters['phone'] ? 1 : null]); @endphp
    <a href="{{ route('admin.outreach.calls.index', $base) }}" class="rounded-full px-3 py-1 {{ $filters['status'] ? 'bg-white text-gray-700 shadow' : 'bg-brand-950 text-white' }}">Всички ({{ $counts->sum() }})</a>
    @foreach(\App\Models\OutreachCall::STATUSES as $key => $label)
        <a href="{{ route('admin.outreach.calls.index', $base + ['status' => $key]) }}" class="rounded-full px-3 py-1 {{ $filters['status'] === $key ? 'bg-brand-950 text-white' : 'bg-white text-gray-700 shadow' }}">{{ $label }} ({{ $counts[$key] ?? 0 }})</a>
    @endforeach
</nav>

<form method="GET" action="{{ route('admin.outreach.calls.index') }}" class="mb-4 flex flex-wrap items-center gap-3 text-sm">
    @if($filters['status'])<input type="hidden" name="status" value="{{ $filters['status'] }}">@endif
    <select name="city" class="rounded border border-gray-300 px-2 py-1.5" aria-label="Град">
        <option value="">Всички градове</option>
        @foreach($cities as $city)<option value="{{ $city }}" @selected($filters['city'] === $city)>{{ $city }}</option>@endforeach
    </select>
    <select name="industry" class="rounded border border-gray-300 px-2 py-1.5" aria-label="Вид бизнес">
        <option value="">Всички видове</option>
        @foreach($industries as $industry)<option value="{{ $industry }}" @selected($filters['industry'] === $industry)>{{ \App\Models\OutreachCall::INDUSTRIES[mb_strtolower($industry)] ?? $industry }}</option>@endforeach
    </select>
    <label class="inline-flex items-center gap-2"><input type="checkbox" name="phone" value="1" @checked($filters['phone'])> Само с телефон</label>
    <button class="rounded-lg border border-gray-300 px-3 py-1.5 font-semibold text-gray-800 hover:bg-gray-50">Филтрирай</button>
</form>

<ul class="divide-y divide-gray-100 rounded-lg bg-white shadow">
    @forelse($calls as $call)
        <li class="grid gap-3 p-4 md:grid-cols-[minmax(0,1.2fr)_auto_minmax(0,2fr)] md:items-start md:gap-6">
            <div class="min-w-0">
                <span class="font-semibold text-gray-900">{{ $call->name }}</span>
                <span class="block text-xs text-gray-500">{{ collect([$call->industryLabel(), $call->city])->filter()->join(' · ') }}</span>
                @if($call->source_url)<a href="{{ $call->source_url }}" target="_blank" rel="noopener" class="text-xs text-blue-600 hover:underline">На картата</a>@endif
            </div>
            <div class="whitespace-nowrap">
                @if($href = $call->telHref())
                    <a href="{{ $href }}" class="inline-flex items-center gap-2 rounded-lg bg-green-600 px-3 py-2 text-sm font-semibold text-white hover:bg-green-700"><x-icon name="phone" /> {{ $call->phone }}</a>
                @else
                    <span class="text-sm text-gray-400">{{ $call->phone ?: 'Няма телефон' }}</span>
                @endif
            </div>
            <div class="min-w-0">
                <form action="{{ route('admin.outreach.calls.update', $call) }}" method="POST" class="flex flex-wrap items-center gap-2">
                    @csrf @method('PATCH')
                    <select name="status" class="rounded border border-gray-300 px-2 py-1.5 text-sm" aria-label="Статус">
                        @foreach(\App\Models\OutreachCall::STATUSES as $key => $label)<option value="{{ $key }}" @selected($call->status === $key)>{{ $label }}</option>@endforeach
                    </select>
                    <input type="text" name="note" value="{{ $call->note }}" maxlength="1000" placeholder="Бележка (кога да звъннем, с кого говорихме…)" class="min-w-0 flex-1 basis-48 rounded border border-gray-300 px-2 py-1.5 text-sm" aria-label="Бележка">
                    <button class="rounded bg-brand-950 px-3 py-1.5 text-sm font-semibold text-white hover:bg-brand-800">Запази</button>
                </form>
                @if($call->called_at)<span class="mt-1 block text-xs text-gray-500">Обаждане: {{ $call->called_at->timezone('Europe/Sofia')->format('d.m.Y H:i') }}</span>@endif
            </div>
        </li>
    @empty
        <li class="px-4 py-8 text-center text-gray-500">Няма бизнеси без сайт. Натиснете „Внеси от сървъра“.</li>
    @endforelse
</ul>
<div class="mt-4">{{ $calls->links() }}</div>
@endsection
