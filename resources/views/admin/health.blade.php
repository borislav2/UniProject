@extends('admin.layout')

@section('title', 'Здраве на системата')

@php
    $badge = ['ok' => ['Работи', 'bg-green-100 text-green-800'], 'warn' => ['Внимание', 'bg-amber-100 text-amber-800'], 'off' => ['Изключено', 'bg-gray-100 text-gray-600']];
@endphp

@section('content')
<p class="mb-6 text-sm text-gray-600">Всичко, което трябва да е настроено, на едно място. Командите се пускат на сървъра като потребител <code>deploy</code> в папката на сайта.</p>
<div class="grid gap-6 lg:grid-cols-2">
    @foreach($checks as $group => $items)
        <section class="rounded-lg bg-white shadow">
            <h2 class="border-b px-5 py-3 font-semibold text-gray-800">{{ $group }}</h2>
            <ul class="divide-y">
                @foreach($items as $check)
                    <li class="flex items-start justify-between gap-3 px-5 py-3 text-sm">
                        <div class="min-w-0">
                            <p class="font-semibold text-gray-900">{{ $check['name'] }}</p>
                            <p class="text-gray-600">{{ $check['text'] }}</p>
                            @if($check['fix'])<p class="mt-1 text-xs text-gray-500">Как: <code class="rounded bg-gray-100 px-1">{{ $check['fix'] }}</code></p>@endif
                        </div>
                        <span class="shrink-0 rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $badge[$check['state']][1] }}">{{ $badge[$check['state']][0] }}</span>
                    </li>
                @endforeach
            </ul>
        </section>
    @endforeach
</div>
@endsection
