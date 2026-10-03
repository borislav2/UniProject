{{--
    One field of the content form, built from the Bulgarian default ($template).
    $name: input name (content[...]), $path: dot path (also used for files[...]), $field: key, $value: current value.
--}}
@php
    $input = 'w-full rounded-lg border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500';
    $label = \App\Support\Content::FIELD_LABELS[$field] ?? (is_int($field) ? null : $field);
    $id = 'f_' . preg_replace('/[^a-z0-9]+/i', '_', $path);
    $filePath = 'files[' . str_replace('.', '][', $path) . ']';
    $removePath = 'remove_files[' . str_replace('.', '][', $path) . ']';
@endphp

@if(is_bool($template))
    <label class="flex items-center gap-2 text-sm font-semibold text-gray-700">
        <input type="hidden" name="{{ $name }}" value="0">
        <input type="checkbox" name="{{ $name }}" value="1" @checked($value)> {{ $label }}
    </label>
@elseif(is_array($template) && array_is_list($template) && ($template === [] || ! is_array($template[0])))
    <div>
        <label for="{{ $id }}" class="block text-sm font-semibold text-gray-700 mb-1">{{ $label }}</label>
        <textarea id="{{ $id }}" name="{{ $name }}" rows="{{ max(3, count((array) $value) + 1) }}" class="{{ $input }}">{{ implode("\n", (array) $value) }}</textarea>
    </div>
@elseif(is_array($template) && array_is_list($template))
    <fieldset class="space-y-3" data-repeater>
        <legend class="text-sm font-bold text-gray-900 mb-2">{{ $label }}</legend>
        @foreach((array) $value as $index => $item)
            <div class="rounded-lg border border-gray-200 bg-gray-50 p-4 space-y-3" data-item>
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-gray-500">№ {{ $loop->iteration }}</span>
                    <label class="flex items-center gap-1.5 text-xs text-red-700"><input type="checkbox" name="{{ $name }}[{{ $index }}][_remove]" value="1"> Премахни</label>
                </div>
                @foreach($template[0] as $childField => $childTemplate)
                    @include('admin.content._field', ['name' => "{$name}[{$index}][{$childField}]", 'path' => "$path.$index.$childField", 'field' => $childField, 'template' => $childTemplate, 'value' => $item[$childField] ?? null])
                @endforeach
            </div>
        @endforeach
        <template>
            <div class="rounded-lg border border-dashed border-brand-300 bg-brand-50/40 p-4 space-y-3" data-item>
                <span class="text-xs font-bold uppercase tracking-wider text-brand-700">Нов елемент</span>
                @foreach($template[0] as $childField => $childTemplate)
                    @include('admin.content._field', ['name' => "{$name}[__INDEX__][{$childField}]", 'path' => "$path.__INDEX__.$childField", 'field' => $childField, 'template' => $childTemplate, 'value' => \App\Support\Content::blank($childTemplate)])
                @endforeach
            </div>
        </template>
        <button type="button" data-add class="inline-flex items-center gap-2 text-sm font-semibold text-brand-700 hover:text-brand-900"><x-icon name="plus" /> Добави</button>
    </fieldset>
@elseif(is_array($template))
    <fieldset class="space-y-4 @if($label) rounded-lg border border-gray-200 p-4 @endif">
        @if($label)<legend class="px-1 text-sm font-bold text-gray-900">{{ $label }}</legend>@endif
        @foreach($template as $childField => $childTemplate)
            @include('admin.content._field', ['name' => "{$name}[{$childField}]", 'path' => "$path.$childField", 'field' => $childField, 'template' => $childTemplate, 'value' => $value[$childField] ?? null])
        @endforeach
    </fieldset>
@elseif(str_ends_with((string) $field, 'image'))
    <div>
        <label for="{{ $id }}" class="block text-sm font-semibold text-gray-700 mb-1">{{ $label }}</label>
        <input type="hidden" name="{{ $name }}" value="{{ $value }}">
        @if($value)
            <img src="{{ asset($value) }}" alt="" class="mb-2 rounded-lg max-w-xs">
            <label class="flex items-center gap-2 text-sm text-gray-600 mb-2"><input type="checkbox" name="{{ $removePath }}" value="1"> Премахни снимката</label>
        @endif
        <input type="file" id="{{ $id }}" name="{{ $filePath }}" accept="image/png,image/jpeg,image/webp,image/gif" class="block w-full text-sm">
        <p class="text-xs text-gray-500 mt-1">JPG, PNG или WebP, до 5 MB.</p>
    </div>
@elseif($field === 'icon')
    @php($current = preg_replace('/^fa-/', '', (string) $value))
    <div>
        <label for="{{ $id }}" class="block text-sm font-semibold text-gray-700 mb-1">{{ $label }}</label>
        <div class="flex items-center gap-3">
            @if($current)<span class="w-10 h-10 shrink-0 rounded-lg bg-brand-950 text-white flex items-center justify-center"><x-icon :name="$current" /></span>@endif
            <select id="{{ $id }}" name="{{ $name }}" class="{{ $input }}">
                @foreach(array_keys(\App\Support\Icons::ICONS) as $icon)
                    <option value="{{ $icon }}" @selected($current === $icon)>{{ $icon }}</option>
                @endforeach
            </select>
        </div>
    </div>
@else
    <div>
        <label for="{{ $id }}" class="block text-sm font-semibold text-gray-700 mb-1">{{ $label }}</label>
        @if(in_array($field, \App\Support\Content::LONG_FIELDS, true) || mb_strlen((string) $value) > 90)
            <textarea id="{{ $id }}" name="{{ $name }}" rows="3" class="{{ $input }}">{{ $value }}</textarea>
        @else
            <input id="{{ $id }}" name="{{ $name }}" value="{{ $value }}" class="{{ $input }}">
        @endif
    </div>
@endif
