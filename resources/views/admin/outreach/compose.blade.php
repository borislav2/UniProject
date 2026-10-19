@extends('admin.layout')

@section('title', 'Първо писмо: ' . $prospect->displayName())

@php($input = 'w-full rounded-lg border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500')

@section('content')
<a href="{{ route('admin.outreach.index') }}" class="inline-block mb-4 text-sm text-blue-600 hover:underline">← Потенциални клиенти</a>

@if(session('send_error'))
    <div class="mb-6 rounded-lg border border-red-300 bg-red-50 px-4 py-3 text-sm text-red-800" role="alert">{{ session('send_error') }}</div>
@endif
@unless($configured)
    <div class="mb-6 rounded-lg border border-yellow-300 bg-yellow-50 px-4 py-3 text-sm text-yellow-900" role="alert">Пощата за първи писма не е настроена: можете да подготвите и прегледате писмото, но не и да го изпратите.</div>
@endunless

<div class="grid gap-6 xl:grid-cols-[minmax(0,420px)_minmax(0,1fr)]">
    <form id="outreach-form" action="{{ route('admin.outreach.send', $prospect) }}" method="POST" class="bg-white rounded-lg shadow p-6 space-y-4 self-start" data-preview="{{ route('admin.outreach.preview', $prospect) }}">
        @csrf
        <div class="rounded-lg bg-gray-50 p-3 text-sm">
            <p><strong>{{ $prospect->displayName() }}</strong> · {{ $prospect->email }} <span class="text-green-700">({{ $prospect->email_status }})</span></p>
            @if($prospect->website)<a href="{{ $prospect->website }}" target="_blank" rel="noopener" class="text-blue-600 hover:underline">{{ $prospect->website }}</a>@endif
            @if($prospect->personalization)
                <p class="mt-2 text-gray-600"><span class="font-semibold text-gray-800">Източник:</span> {{ $prospect->personalization }}</p>
            @endif
        </div>
        <div>
            <label for="subject" class="block text-sm font-semibold text-gray-700 mb-1">Тема <span class="text-red-500">*</span></label>
            <input id="subject" name="subject" value="{{ old('subject', $defaults['subject']) }}" required maxlength="150" class="{{ $input }}">
        </div>
        <div>
            <label for="first_name" class="block text-sm font-semibold text-gray-700 mb-1">Име за обръщение <span class="font-normal text-gray-400">(по желание)</span></label>
            <input id="first_name" name="first_name" value="{{ old('first_name', $defaults['first_name']) }}" maxlength="60" class="{{ $input }}">
            <p class="mt-1 text-xs text-gray-500">Празно = „Здравейте,“.</p>
        </div>
        <div>
            <label for="observation" class="block text-sm font-semibold text-gray-700 mb-1">Какво забелязахме <span class="text-red-500">*</span></label>
            <textarea id="observation" name="observation" rows="7" required minlength="20" maxlength="900" class="{{ $input }}" placeholder="Разгледахме сайта ви и видяхме, че на телефон формата за запитване не се вижда, а телефонът не се набира с едно докосване.">{{ old('observation', $defaults['observation']) }}</textarea>
            <p class="mt-1 text-xs text-gray-500">@if($prospect->findings)Попълнено от автоматичната проверка на сайта им. Отворете сайта и махнете всичко, което не е вярно.@else 1–2 конкретни изречения на български, проверени от вас на сайта им. Това е причината да им пишем.@endif</p>
            @error('observation')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="sender" class="block text-sm font-semibold text-gray-700 mb-1">Подпис <span class="text-red-500">*</span></label>
            <input id="sender" name="sender" value="{{ old('sender', $defaults['sender']) }}" required maxlength="100" class="{{ $input }}">
        </div>
        <div class="flex flex-wrap gap-3 pt-2">
            <button type="submit" @disabled(! $configured || $remaining < 1) class="inline-flex items-center gap-2 bg-brand-950 text-white px-5 py-2.5 rounded-lg font-semibold hover:bg-brand-800 disabled:opacity-50"><x-icon name="paper-plane" /> Изпрати</button>
            <button type="submit" formaction="{{ route('admin.outreach.preview', $prospect) }}" formtarget="outreach-preview" formnovalidate class="inline-flex items-center gap-2 border border-gray-300 px-5 py-2.5 rounded-lg font-semibold text-gray-700 hover:bg-gray-50"><x-icon name="eye" /> Обнови прегледа</button>
        </div>
        <p class="text-xs text-gray-500">Остават {{ $remaining }} писма за днес. @if($configured)Изпраща се от {{ config('creatium.outreach.from_address') }}; отговорите идват там.@endif</p>
    </form>

    <div class="bg-white rounded-lg shadow overflow-hidden flex flex-col min-h-[60vh]">
        <div class="px-4 py-2 border-b text-sm text-gray-500">Преглед: така ще го види бизнесът</div>
        <iframe name="outreach-preview" title="Преглед на писмото" class="w-full flex-1 min-h-[60vh] bg-white"></iframe>
    </div>
</div>

<script>
(function () {
    var form = document.getElementById('outreach-form');
    var timer;
    function refresh() {
        var action = form.action, target = form.target;
        form.action = form.dataset.preview;
        form.target = 'outreach-preview';
        HTMLFormElement.prototype.submit.call(form);
        form.action = action;
        form.target = target;
    }
    form.addEventListener('input', function () { clearTimeout(timer); timer = setTimeout(refresh, 600); });
    refresh();
})();
</script>
@endsection
