@extends('admin.layout')

@section('title', $post->exists ? 'Редактиране на статия' : 'Нова статия')

@php
    $input = 'w-full rounded-lg border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500';
    $languages = \App\Http\Controllers\Admin\PostController::LOCALES;
@endphp

@push('head')
    @vite('resources/js/admin-editor.js')
    <style>.editor-toolbar button { display: inline-flex; align-items: center; justify-content: center; } .editor-icon svg { height: 15px; } .EasyMDEContainer .CodeMirror { border-radius: 0 0 .5rem .5rem; }</style>
@endpush

@section('content')
<script type="application/json" id="editor-icons" data-upload-url="{{ route('admin.uploads.image') }}">{!! json_encode(collect(['heading', 'bold', 'italic', 'list-ul', 'list-ol', 'quote-left', 'link', 'image', 'minus', 'eye'])->mapWithKeys(fn ($i) => [$i => \App\Support\Icons::svg($i)]), JSON_HEX_TAG) !!}</script>

<form action="{{ $post->exists ? route('admin.posts.update', $post) : route('admin.posts.store') }}" method="POST" enctype="multipart/form-data" class="grid grid-cols-1 xl:grid-cols-3 gap-6 items-start">
    @csrf
    @if($post->exists) @method('PUT') @endif

    <div class="min-w-0 xl:col-span-2 bg-white rounded-lg shadow p-6">
        <div data-tabs class="hidden mb-6 flex gap-2 border-b border-gray-200" role="tablist">
            @foreach($languages as $l => $name)
                <button type="button" data-tab="{{ $l }}" role="tab" class="px-4 py-2 -mb-px border-b-2 border-transparent font-semibold text-gray-500 aria-selected:border-brand-600 aria-selected:text-brand-700">
                    {{ $name }}
                    @if($post->tr('title', $l)) <span class="ml-1 inline-block w-2 h-2 rounded-full bg-green-500" title="Има текст"></span> @endif
                </button>
            @endforeach
        </div>
        <p class="text-sm text-gray-500 mb-6">Попълнете езиците, на които искате статията да излиза. Ако заглавието на даден език е празно, статията не се показва на този език.</p>

        <div class="space-y-10">
        @foreach($languages as $l => $name)
            <div data-panel="{{ $l }}" class="space-y-5">
                <h3 class="text-lg font-bold text-gray-900">{{ $name }}</h3>
                <div>
                    <label for="title_{{ $l }}" class="block text-sm font-semibold text-gray-700 mb-1">Заглавие</label>
                    <input id="title_{{ $l }}" name="title[{{ $l }}]" value="{{ old("title.$l", $post->tr('title', $l)) }}" class="{{ $input }} text-lg" maxlength="200">
                </div>
                <div>
                    <label for="slug_{{ $l }}" class="block text-sm font-semibold text-gray-700 mb-1">Адрес на статията</label>
                    <div class="flex items-center rounded-lg border border-gray-300 focus-within:ring-2 focus-within:ring-brand-500 overflow-hidden">
                        <span class="pl-3 text-gray-500 text-sm whitespace-nowrap">{{ rtrim(lroute('blog.index', [], $l), '/') }}/</span>
                        <input id="slug_{{ $l }}" name="slug[{{ $l }}]" value="{{ old("slug.$l", $post->tr('slug', $l)) }}" class="flex-1 min-w-0 px-1 py-2 focus:outline-none" placeholder="създава се от заглавието">
                    </div>
                </div>
                <div>
                    <label for="excerpt_{{ $l }}" class="block text-sm font-semibold text-gray-700 mb-1">Кратко описание <span class="font-normal text-gray-500">(показва се в списъка със статии)</span></label>
                    <textarea id="excerpt_{{ $l }}" name="excerpt[{{ $l }}]" rows="2" maxlength="500" class="{{ $input }}">{{ old("excerpt.$l", $post->tr('excerpt', $l)) }}</textarea>
                </div>
                <div>
                    <label for="body_{{ $l }}" class="block text-sm font-semibold text-gray-700 mb-1">Текст</label>
                    <textarea id="body_{{ $l }}" name="body[{{ $l }}]" rows="16" data-markdown class="{{ $input }} font-mono text-sm" placeholder="Пишете тук. С бутоните горе добавяте подзаглавия, списъци, линкове и снимки.">{{ old("body.$l", $post->tr('body', $l)) }}</textarea>
                </div>

                @php
                    $seoChecks = $post->exists ? \App\Support\Seo\PostSeo::checks($post, $l) : [];
                    [$seoOk, $seoTotal] = \App\Support\Seo\PostSeo::score($seoChecks);
                    $effTitle = $post->exists ? \App\Support\Seo\PostSeo::effectiveTitle($post, $l) : '';
                    $effDesc = $post->exists ? \App\Support\Seo\PostSeo::effectiveDescription($post, $l) : '';
                @endphp
                <details class="sx-panel" open>
                    <summary>SEO и споделяне ({{ strtoupper($l) }}) @if($seoChecks)<span class="sx-score {{ $seoOk >= 9 ? 'ok' : 'warn' }} ml-2">{{ $seoOk }}/{{ $seoTotal }}</span>@endif</summary>
                    <div class="mt-4 space-y-4">
                        @include('admin.seo._snippet', ['id' => "snippet_$l", 'url' => $post->exists && $post->tr('slug', $l) ? $post->url($l) : rtrim(lroute('blog.index', [], $l), '/') . '/' . ($post->tr('slug', $l) ?: 'adres-na-statiyata'), 'titleInput' => "#meta_title_$l", 'descInput' => "#meta_description_$l", 'fallbackTitle' => $post->tr('meta_title', $l) ? '' : $effTitle, 'fallbackDescription' => $post->tr('meta_description', $l) ? '' : $effDesc])
                        <div>
                            <label for="focus_keyword_{{ $l }}" class="block text-sm font-semibold text-gray-700 mb-1">Ключова дума <span class="font-normal text-gray-500">(по какво искате да ви намират; по желание)</span></label>
                            <input id="focus_keyword_{{ $l }}" name="focus_keyword[{{ $l }}]" value="{{ old("focus_keyword.$l", $post->tr('focus_keyword', $l)) }}" maxlength="120" class="{{ $input }}" placeholder="например изработка на сайт за ресторант">
                            <p class="text-xs text-gray-500 mt-1">Не се показва на сайта. Само проверява дали думата е на важните места в текста.</p>
                        </div>
                        @include('admin.seo._fields', ['prefix' => '', 'l' => $l, 'values' => [
                            'meta_title' => old("meta_title.$l", $post->tr('meta_title', $l)),
                            'meta_description' => old("meta_description.$l", $post->tr('meta_description', $l)),
                            'canonical_url' => old("canonical_url.$l", $post->tr('canonical_url', $l)),
                            'og_title' => old("og_title.$l", $post->tr('og_title', $l)),
                            'og_description' => old("og_description.$l", $post->tr('og_description', $l)),
                        ], 'placeholders' => ['meta_title' => $effTitle ? 'Празно = ' . $effTitle : null, 'meta_description' => $effDesc ? 'Празно = ' . $effDesc : null], 'name' => fn ($field) => "{$field}[$l]"])
                        @if($seoChecks)
                            <div>
                                <p class="sx-snippet-label">Проверки за SEO ({{ $seoOk }} от {{ $seoTotal }}, по последно запазеното)</p>
                                <ul class="sx-check">
                                    @foreach($seoChecks as $c)
                                        <li class="{{ $c['state'] }}"><span class="dot"></span><span>{{ $c['label'] }}</span><small>{{ $c['detail'] }}</small></li>
                                    @endforeach
                                </ul>
                            </div>
                        @else
                            <p class="text-xs text-gray-500">Запазете статията, за да видите проверките за SEO.</p>
                        @endif
                    </div>
                </details>
            </div>
        @endforeach
        </div>
    </div>

    <div class="min-w-0 space-y-6">
        <div class="bg-white rounded-lg shadow p-6 space-y-4">
            <div>
                <label for="status" class="block text-sm font-semibold text-gray-700 mb-1">Статус</label>
                <select id="status" name="status" class="{{ $input }}">
                    @foreach(\App\Models\Post::STATUSES as $value => $label)
                        <option value="{{ $value }}" @selected(old('status', $post->status) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="published_at" class="block text-sm font-semibold text-gray-700 mb-1">Дата на публикуване</label>
                <input type="datetime-local" id="published_at" name="published_at" value="{{ old('published_at', $post->published_at?->format('Y-m-d\TH:i')) }}" class="{{ $input }}">
                <p class="text-xs text-gray-500 mt-1">Празно = сега. Бъдеща дата = статията излиза тогава.</p>
            </div>
            <div>
                <label for="post_category_id" class="block text-sm font-semibold text-gray-700 mb-1">Категория</label>
                <select id="post_category_id" name="post_category_id" class="{{ $input }}">
                    <option value="">Без категория</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" @selected((string) old('post_category_id', $post->post_category_id) === (string) $category->id)>{{ $category->tr('name', 'bg') }}</option>
                    @endforeach
                </select>
                <a href="{{ route('admin.post-categories.index') }}" class="text-xs text-brand-700 underline">Управление на категориите</a>
            </div>
            <div>
                <label for="user_id" class="block text-sm font-semibold text-gray-700 mb-1">Автор</label>
                <select id="user_id" name="user_id" class="{{ $input }}">
                    @foreach($authors as $user)
                        @php $person = \App\Support\Schema::personForUser($user->name); @endphp
                        <option value="{{ $user->id }}" @selected((int) old('user_id', $post->user_id ?? auth()->id()) === $user->id)>{{ $user->name }}{{ $person ? '' : ' (фирмата)' }}</option>
                    @endforeach
                </select>
                <p class="text-xs text-gray-500 mt-1">В структурираните данни за Google авторът е човекът от екипа със същото име. Общият акаунт води до „Creatium Lab“.@if($post->exists) Сега: {{ \App\Support\Seo\PostSeo::authorLabel($post) }}.@endif</p>
            </div>
            <div class="flex flex-wrap gap-2 pt-2">
                <button type="submit" class="inline-flex items-center gap-2 bg-brand-950 text-white px-5 py-2.5 rounded-lg font-semibold hover:bg-brand-800"><x-icon name="floppy-disk" /> Запази</button>
                @foreach($post->exists ? $post->locales() : [] as $l)
                    <a href="{{ $post->url($l) }}" target="_blank" class="inline-flex items-center gap-2 border border-gray-300 px-4 py-2.5 rounded-lg font-semibold text-gray-700 hover:border-brand-500"><x-icon name="arrow-up-right-from-square" /> {{ strtoupper($l) }}</a>
                @endforeach
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6 space-y-5">
            @foreach(['cover_image' => ['Снимка на статията', 'Показва се горе в статията и в списъка. Хоризонтална, поне 1200 px широка.'], 'og_image' => ['Снимка за споделяне (Facebook, LinkedIn…)', 'По желание. Ако е празно, се използва снимката на статията. Най-добре 1200×630 px.']] as $field => [$label, $hint])
                <div>
                    <label for="{{ $field }}" class="block text-sm font-semibold text-gray-700 mb-1">{{ $label }}</label>
                    @if($post->{$field})
                        <img src="{{ asset($post->{$field}) }}" alt="" class="mb-2 rounded-lg w-full aspect-[16/9] object-cover">
                        <label class="flex items-center gap-2 text-sm text-gray-600 mb-2"><input type="checkbox" name="remove_{{ $field }}" value="1"> Премахни снимката</label>
                    @endif
                    <input type="file" id="{{ $field }}" name="{{ $field }}" accept="image/png,image/jpeg,image/webp,image/gif" class="block w-full text-sm">
                    <p class="text-xs text-gray-500 mt-1">{{ $hint }} До 5 MB.</p>
                </div>
            @endforeach
        </div>
    </div>
</form>

@if($post->exists)
    <form action="{{ route('admin.posts.destroy', $post) }}" method="POST" class="mt-6" onsubmit="return confirm('Да изтрия ли статията? Това не може да се върне.')">
        @csrf
        @method('DELETE')
        <button type="submit" class="inline-flex items-center gap-2 text-red-600 hover:text-red-800 font-semibold"><x-icon name="trash" /> Изтрий статията</button>
    </form>
@endif
@include('admin.seo._live')
@endsection
