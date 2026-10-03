@extends('admin.layout')

@section('title', $label)

@section('content')
<div class="flex flex-wrap items-center justify-between gap-4 mb-6">
    <a href="{{ route('admin.content.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-brand-700"><x-icon name="arrow-left" /> Всички текстове</a>
    <div class="flex rounded-lg border border-gray-300 bg-white p-0.5 text-sm font-semibold" role="group" aria-label="Език">
        @foreach(['bg' => 'Български', 'en' => 'English'] as $l => $name)
            <a href="{{ route('admin.content.edit', ['key' => $key, 'lang' => $l]) }}" class="px-4 py-1.5 rounded-md {{ $locale === $l ? 'bg-brand-950 text-white' : 'text-gray-600 hover:text-brand-950' }}" @if($locale === $l) aria-current="true" @endif>{{ $name }}</a>
        @endforeach
    </div>
</div>

@if($block)
    <p class="text-sm text-gray-500 mb-4">Последна промяна: {{ $block->updated_at->format('d.m.Y H:i') }}@if($block->editor), {{ $block->editor->name }}@endif</p>
@endif

<form action="{{ route('admin.content.update', $key) }}" method="POST" enctype="multipart/form-data" class="bg-white rounded-lg shadow p-6 space-y-5 max-w-4xl">
    @csrf
    @method('PUT')
    <input type="hidden" name="locale" value="{{ $locale }}">
    @include('admin.content._field', ['name' => 'content', 'path' => $key, 'field' => null, 'template' => $template, 'value' => $value])
    <div class="flex flex-wrap items-center gap-3 pt-2">
        <button type="submit" class="inline-flex items-center gap-2 bg-brand-950 text-white px-5 py-2.5 rounded-lg font-semibold hover:bg-brand-800"><x-icon name="floppy-disk" /> Запази ({{ strtoupper($locale) }})</button>
    </div>
</form>

@if($block)
    <form action="{{ route('admin.content.reset', $key) }}" method="POST" class="mt-4" onsubmit="return confirm('Да върна ли оригиналния текст? Промените ви за този език ще се изгубят.')">
        @csrf
        @method('DELETE')
        <input type="hidden" name="locale" value="{{ $locale }}">
        <button type="submit" class="inline-flex items-center gap-2 text-sm font-semibold text-red-600 hover:text-red-800"><x-icon name="rotate-left" /> Върни по подразбиране ({{ strtoupper($locale) }})</button>
    </form>
@endif
@endsection

@push('scripts')
<script>
    document.querySelectorAll('[data-repeater]').forEach(function (repeater) {
        var template = repeater.querySelector(':scope > template');
        var count = repeater.querySelectorAll(':scope > [data-item]').length;
        repeater.querySelector(':scope > [data-add]').addEventListener('click', function () {
            var html = template.innerHTML.replace(/__INDEX__/g, 'new' + (count++));
            this.insertAdjacentHTML('beforebegin', html);
        });
    });
</script>
@endpush
