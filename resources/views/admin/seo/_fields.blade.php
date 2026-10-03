{{-- SEO fields for one language. $name(field) gives the input name, $values the current values, $l the language. --}}
@php($input = 'w-full rounded-lg border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500')
<div>
    <div class="flex items-baseline justify-between"><label for="meta_title_{{ $l }}{{ $prefix }}" class="block text-sm font-semibold text-gray-700 mb-1">Meta Title</label><span id="meta_title_count_{{ $l }}{{ $prefix }}" class="text-xs text-gray-500"></span></div>
    <input id="meta_title_{{ $l }}{{ $prefix }}" name="{{ $name('meta_title') }}" value="{{ $values['meta_title'] ?? '' }}" maxlength="120" data-counter="meta_title_count_{{ $l }}{{ $prefix }}" data-limit="60" class="{{ $input }}" placeholder="Празно = заглавието на страницата + „| Creatium Lab“">
    <p class="text-xs text-gray-500 mt-1">Заглавието в Google. Пълното заглавие, до около 60 знака.</p>
</div>
<div>
    <div class="flex items-baseline justify-between"><label for="meta_description_{{ $l }}{{ $prefix }}" class="block text-sm font-semibold text-gray-700 mb-1">Meta Description</label><span id="meta_description_count_{{ $l }}{{ $prefix }}" class="text-xs text-gray-500"></span></div>
    <textarea id="meta_description_{{ $l }}{{ $prefix }}" name="{{ $name('meta_description') }}" rows="2" maxlength="300" data-counter="meta_description_count_{{ $l }}{{ $prefix }}" data-limit="160" class="{{ $input }}" placeholder="Празно = описанието по подразбиране">{{ $values['meta_description'] ?? '' }}</textarea>
    <p class="text-xs text-gray-500 mt-1">Текстът под заглавието в Google, до около 160 знака.</p>
</div>
<div>
    <label for="canonical_url_{{ $l }}{{ $prefix }}" class="block text-sm font-semibold text-gray-700 mb-1">Canonical URL</label>
    <input id="canonical_url_{{ $l }}{{ $prefix }}" name="{{ $name('canonical_url') }}" value="{{ $values['canonical_url'] ?? '' }}" type="url" class="{{ $input }}" placeholder="Празно = адресът на самата страница (препоръчително)">
</div>
<div>
    <label for="og_title_{{ $l }}{{ $prefix }}" class="block text-sm font-semibold text-gray-700 mb-1">Open Graph заглавие <span class="font-normal text-gray-500">(при споделяне)</span></label>
    <input id="og_title_{{ $l }}{{ $prefix }}" name="{{ $name('og_title') }}" value="{{ $values['og_title'] ?? '' }}" maxlength="200" class="{{ $input }}" placeholder="Празно = Meta Title">
</div>
<div>
    <label for="og_description_{{ $l }}{{ $prefix }}" class="block text-sm font-semibold text-gray-700 mb-1">Open Graph описание</label>
    <textarea id="og_description_{{ $l }}{{ $prefix }}" name="{{ $name('og_description') }}" rows="2" maxlength="300" class="{{ $input }}" placeholder="Празно = Meta Description">{{ $values['og_description'] ?? '' }}</textarea>
</div>
