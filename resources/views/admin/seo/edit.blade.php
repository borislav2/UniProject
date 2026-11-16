@extends('admin.layout')

@section('title', 'SEO: ' . $label)

@section('content')
@php use App\Support\Seo\Snippet; @endphp
<div class="adm-page-head">
    <div>
        <h2>SEO: {{ $label }}</h2>
        <p class="adm-eyebrow mt-1">Какво виждат Google и социалните мрежи за тази страница</p>
    </div>
    <a href="{{ route('admin.seo.index') }}" class="adm-btn ghost sm"><x-icon name="arrow-left" /> Всички страници</a>
</div>
@include('admin.seo._tabs', ['active' => 'pages'])

<form action="{{ route('admin.seo.update', $page) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    <div class="sx-edit-grid">
        @foreach(['bg' => 'Български', 'en' => 'English'] as $l => $name)
            @php $r = $info[$l] ?? null; $own = $values[$l] ?? []; @endphp
            <div class="adm-card">
                <div class="flex items-baseline justify-between gap-4">
                    <h3 class="sx-card-h"><span class="sx-lang">{{ strtoupper($l) }}</span>{{ $name }}</h3>
                    <a href="{{ $urls[$l] }}" target="_blank" rel="noopener" class="text-sm underline truncate">{{ $urls[$l] }}</a>
                </div>

                @if($r)
                    <dl class="sx-facts-list">
                        <dt>В Google</dt><dd>{{ $r['noindex'] ? 'noindex (скрита от Google)' : 'индексира се' }}, {{ $r['in_sitemap'] ? 'в sitemap' : 'не е в sitemap' }}</dd>
                        <dt>Canonical</dt><dd>{{ ['self' => 'към самата страница', 'other' => 'към друг адрес', 'missing' => 'липсва'][$r['canonical_state']] }}@if($r['canonical'] && $r['canonical_state'] === 'other'): {{ $r['canonical'] }}@endif</dd>
                        <dt>Заглавие h1</dt><dd>{{ $r['h1'] ?: '—' }}@if($r['h1_count'] > 1) ({{ $r['h1_count'] }} × h1)@endif</dd>
                        <dt>Връзки към нея</dt><dd>{{ $r['inlinks'] ?? 'след проверката в „SEO здраве“' }}</dd>
                        <dt>Сега излиза като</dt><dd>@if($r['title_own'] || $r['description_own']) ваш текст за {{ collect([$r['title_own'] ? 'заглавието' : null, $r['description_own'] ? 'описанието' : null])->filter()->join(' и ') }} @else текстът по подразбиране от шаблона @endif</dd>
                    </dl>
                    @php
                        $defTitle = $r['title_own'] ? '' : (string) $r['title'];
                        $defDesc = $r['description_own'] ? '' : (string) $r['description'];
                    @endphp
                    @include('admin.seo._snippet', ['id' => "snippet_$l", 'url' => $urls[$l], 'titleInput' => "#meta_title_$l", 'descInput' => "#meta_description_$l", 'fallbackTitle' => $defTitle, 'fallbackDescription' => $defDesc])
                @endif

                @include('admin.seo._fields', ['prefix' => '', 'l' => $l, 'values' => [
                    'meta_title' => old("seo.$l.meta_title", $own['meta_title'] ?? null),
                    'meta_description' => old("seo.$l.meta_description", $own['meta_description'] ?? null),
                    'canonical_url' => old("seo.$l.canonical_url", $own['canonical_url'] ?? null),
                    'og_title' => old("seo.$l.og_title", $own['og_title'] ?? null),
                    'og_description' => old("seo.$l.og_description", $own['og_description'] ?? null),
                ], 'placeholders' => $r ? [
                    'meta_title' => ! $r['title_own'] && $r['title'] ? 'Празно = ' . $r['title'] : null,
                    'meta_description' => ! $r['description_own'] && $r['description'] ? 'Празно = ' . $r['description'] : null,
                ] : [], 'name' => fn ($field) => "seo[$l][$field]"])
                <div>
                    <label for="og_image_{{ $l }}" class="block text-sm font-semibold text-gray-700 mb-1">Open Graph снимка</label>
                    @if(!empty($own['og_image']))
                        <img src="{{ asset($own['og_image']) }}" alt="" class="mb-2 rounded-lg w-full max-w-sm aspect-[1200/630] object-cover">
                        <label class="flex items-center gap-2 text-sm text-gray-600 mb-2"><input type="checkbox" name="remove_og_image[{{ $l }}]" value="1"> Премахни снимката</label>
                    @endif
                    <input type="file" id="og_image_{{ $l }}" name="og_image[{{ $l }}]" accept="image/png,image/jpeg,image/webp" class="block w-full text-sm">
                    <p class="text-xs text-gray-500 mt-1">Празно = общата снимка на сайта. Най-добре 1200×630 px, до 5 MB.</p>
                </div>
            </div>
        @endforeach
    </div>
    <button type="submit" class="adm-btn mt-6"><x-icon name="floppy-disk" /> Запази</button>
</form>
@include('admin.seo._live')
@endsection
