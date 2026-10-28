@extends('admin.layout')

@section('title', $tpl['label'])

@php($input = 'w-full rounded-lg border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500')
@php($fields = collect($tpl['fields'])->except('sender')->put('sender', $tpl['fields']['sender']))

@section('content')
<a href="{{ route('admin.client-emails.index', $project ? ['project' => $project->id] : []) }}" class="inline-block mb-4 text-sm text-blue-600 hover:underline">← Всички шаблони</a>

@if($mailNotConfigured)
    <div class="mb-6 rounded-lg border border-yellow-300 bg-yellow-50 px-4 py-3 text-sm text-yellow-900" role="alert">
        <p class="font-semibold">Изпращането на имейли не е настроено.</p>
        <p class="mt-1">Сървърът е с <code class="rounded bg-yellow-100 px-1">MAIL_MAILER={{ config('mail.default') }}</code>: имейлът ще се запише само в лога и клиентът няма да го получи. Пуснете на сървъра <code class="rounded bg-yellow-100 px-1">bash deploy/configure-mail.sh</code>.</p>
    </div>
@endif
@if(session('send_error'))
    <div class="mb-6 rounded-lg border border-red-300 bg-red-50 px-4 py-3 text-sm text-red-800" role="alert">{{ session('send_error') }}</div>
@endif

<div class="grid gap-6 xl:grid-cols-[minmax(0,420px)_minmax(0,1fr)]">
    <form id="client-email-form" action="{{ route('admin.client-emails.store', $tpl['key']) }}" method="POST" enctype="multipart/form-data" class="bg-white rounded-lg shadow p-6 space-y-4 self-start"
          data-preview="{{ route('admin.client-emails.preview', $tpl['key']) }}">
        @csrf
        @if($project)<input type="hidden" name="project_id" value="{{ $project->id }}">@endif

        <div>
            <label for="to" class="block text-sm font-semibold text-gray-700 mb-1">До (имейл на клиента) <span class="text-red-500">*</span></label>
            <input id="to" type="email" name="to" value="{{ old('to', $to) }}" required placeholder="klient@primer.bg" class="{{ $input }}">
            @if($project && ! $project->client_email)<p class="mt-1 text-xs text-gray-500">Запитването няма имейл. След изпращане ще го запазим към него.</p>@endif
        </div>
        <div>
            <label for="subject" class="block text-sm font-semibold text-gray-700 mb-1">Тема <span class="text-red-500">*</span></label>
            <input id="subject" name="subject" value="{{ old('subject', $tpl['subject']) }}" required maxlength="255" class="{{ $input }}">
            <p class="mt-1 text-xs text-gray-500">{client_name} и {business} се попълват автоматично от полетата по-долу.</p>
        </div>

        <div class="border-t border-gray-200 pt-4 space-y-4">
            @foreach($fields as $name => $field)
                @php($required = $field['required'] ?? true)
                <div>
                    <label for="f-{{ $name }}" class="block text-sm font-semibold text-gray-700 mb-1">{{ $field['label'] }} @if($required)<span class="text-red-500">*</span>@else<span class="font-normal text-gray-400">(по желание)</span>@endif</label>
                    @if(($field['type'] ?? 'text') === 'textarea')
                        <textarea id="f-{{ $name }}" name="data[{{ $name }}]" rows="3" maxlength="1000" @if($required) required @endif class="{{ $input }}">{{ old('data.' . $name, $defaults[$name] ?? '') }}</textarea>
                    @else
                        <input id="f-{{ $name }}" name="data[{{ $name }}]" type="{{ in_array($field['type'] ?? 'text', ['date', 'url'], true) ? $field['type'] : 'text' }}"
                               value="{{ old('data.' . $name, $defaults[$name] ?? '') }}" @if($required) required @endif
                               @if(($field['type'] ?? 'text') === 'date') min="{{ now()->toDateString() }}" @else maxlength="255" @endif class="{{ $input }}">
                    @endif
                    @if(! empty($field['help']))<p class="mt-1 text-xs text-gray-500">{{ $field['help'] }}</p>@endif
                </div>
            @endforeach
        </div>

        <div class="border-t border-gray-200 pt-4">
            <label for="attachment" class="block text-sm font-semibold text-gray-700 mb-1">Прикачен PDF <span class="font-normal text-gray-400">(по желание)</span></label>
            <input id="attachment" type="file" name="attachment" accept="application/pdf,.pdf" class="block w-full text-sm text-gray-700 file:mr-3 file:rounded-lg file:border-0 file:bg-gray-100 file:px-4 file:py-2 file:font-semibold file:text-gray-700 hover:file:bg-gray-200" data-attachment>
            <p class="mt-1 text-xs text-gray-500">Оферта, договор, презентация: само PDF, до 10 MB. Не се вижда в прегледа, но тръгва с имейла.@if($errors->any()) <strong class="text-red-600">Ако сте избрали файл, изберете го отново.</strong>@endif</p>
            @error('attachment')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <div class="flex flex-wrap gap-3 pt-2">
            <button type="submit" name="send" value="1" class="inline-flex items-center gap-2 bg-brand-950 text-white px-5 py-2.5 rounded-lg font-semibold hover:bg-brand-800"><x-icon name="paper-plane" /> Изпрати</button>
            <button type="submit" formaction="{{ route('admin.client-emails.preview', $tpl['key']) }}" formtarget="email-preview" formnovalidate class="inline-flex items-center gap-2 border border-gray-300 px-5 py-2.5 rounded-lg font-semibold text-gray-700 hover:bg-gray-50"><x-icon name="eye" /> Обнови прегледа</button>
        </div>
        <p class="text-xs text-gray-500">Имейлът се праща от {{ config('mail.from.address') }}. Отговорите на клиента идват на {{ config('creatium.contact.email') }}.</p>
    </form>

    <div class="bg-white rounded-lg shadow overflow-hidden flex flex-col min-h-[70vh]">
        <div class="px-4 py-2 border-b text-sm text-gray-500">Преглед: така ще го види клиентът</div>
        <iframe name="email-preview" title="Преглед на имейла" class="w-full flex-1 min-h-[70vh] bg-[#eef1f5]"></iframe>
    </div>
</div>

<script>
(function () {
    var form = document.getElementById('client-email-form');
    var timer;
    function refresh() {
        var action = form.action, target = form.target;
        var file = form.querySelector('[data-attachment]');
        file.disabled = true; // прегледът не се нуждае от файла: не го качваме при всяко натискане на клавиш
        form.action = form.dataset.preview;
        form.target = 'email-preview';
        HTMLFormElement.prototype.submit.call(form);
        form.action = action;
        form.target = target;
        file.disabled = false;
    }
    form.addEventListener('input', function () { clearTimeout(timer); timer = setTimeout(refresh, 600); });
    form.addEventListener('submit', function (e) {
        if (e.submitter && e.submitter.name === 'send' && !confirm('Да изпратим ли имейла до ' + document.getElementById('to').value + '?')) {
            e.preventDefault();
        }
    });
    refresh();
})();
</script>
@endsection
