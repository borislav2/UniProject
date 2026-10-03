@extends('admin.layout')

@section('title', 'SEO: ' . $label)

@section('content')
<a href="{{ route('admin.seo.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-brand-700 mb-4"><x-icon name="arrow-left" /> Всички страници</a>

<form action="{{ route('admin.seo.update', $page) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    <div class="grid lg:grid-cols-2 gap-6">
        @foreach(['bg' => 'Български', 'en' => 'English'] as $l => $name)
            <div class="bg-white rounded-lg shadow p-6 space-y-4">
                <div class="flex items-baseline justify-between gap-4">
                    <h3 class="text-lg font-bold text-gray-900">{{ $name }}</h3>
                    <a href="{{ $urls[$l] }}" target="_blank" class="text-sm text-brand-700 underline truncate">{{ $urls[$l] }}</a>
                </div>
                @include('admin.seo._fields', ['prefix' => '', 'l' => $l, 'values' => [
                    'meta_title' => old("seo.$l.meta_title", $values[$l]['meta_title'] ?? null),
                    'meta_description' => old("seo.$l.meta_description", $values[$l]['meta_description'] ?? null),
                    'canonical_url' => old("seo.$l.canonical_url", $values[$l]['canonical_url'] ?? null),
                    'og_title' => old("seo.$l.og_title", $values[$l]['og_title'] ?? null),
                    'og_description' => old("seo.$l.og_description", $values[$l]['og_description'] ?? null),
                ], 'name' => fn ($field) => "seo[$l][$field]"])
                <div>
                    <label for="og_image_{{ $l }}" class="block text-sm font-semibold text-gray-700 mb-1">Open Graph снимка</label>
                    @if(!empty($values[$l]['og_image']))
                        <img src="{{ asset($values[$l]['og_image']) }}" alt="" class="mb-2 rounded-lg w-full max-w-sm aspect-[1200/630] object-cover">
                        <label class="flex items-center gap-2 text-sm text-gray-600 mb-2"><input type="checkbox" name="remove_og_image[{{ $l }}]" value="1"> Премахни снимката</label>
                    @endif
                    <input type="file" id="og_image_{{ $l }}" name="og_image[{{ $l }}]" accept="image/png,image/jpeg,image/webp" class="block w-full text-sm">
                    <p class="text-xs text-gray-500 mt-1">Празно = общата снимка на сайта. Най-добре 1200×630 px, до 5 MB.</p>
                </div>
            </div>
        @endforeach
    </div>
    <button type="submit" class="mt-6 inline-flex items-center gap-2 bg-brand-950 text-white px-5 py-2.5 rounded-lg font-semibold hover:bg-brand-800"><x-icon name="floppy-disk" /> Запази</button>
</form>
@endsection

@push('scripts')
<script>
    document.querySelectorAll('[data-counter]').forEach(function (field) {
        var out = document.getElementById(field.dataset.counter);
        var limit = parseInt(field.dataset.limit, 10);
        var update = function () { out.textContent = field.value.length + ' / ' + limit; out.classList.toggle('text-red-600', field.value.length > limit); };
        field.addEventListener('input', update);
        update();
    });
</script>
@endpush
