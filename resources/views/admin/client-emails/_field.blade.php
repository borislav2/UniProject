@php
    $required = $field['required'] ?? true;
    $type = $field['type'] ?? 'text';
@endphp
<div>
    <label for="f-{{ $name }}" class="block text-sm font-semibold text-gray-700 mb-1">{{ $field['label'] }} @if($required)<span class="text-red-500">*</span>@else<span class="font-normal text-gray-400">(по желание)</span>@endif</label>
    @if($type === 'textarea')
        <textarea id="f-{{ $name }}" name="data[{{ $name }}]" rows="{{ $field['rows'] ?? 4 }}" maxlength="5000" @if($required) required @endif class="{{ $input }}">{{ old('data.' . $name, $defaults[$name] ?? '') }}</textarea>
    @else
        <input id="f-{{ $name }}" name="data[{{ $name }}]" type="{{ in_array($type, ['date', 'url'], true) ? $type : 'text' }}"
               value="{{ old('data.' . $name, $defaults[$name] ?? '') }}" @if($required) required @endif
               @if($type === 'date') min="{{ now()->toDateString() }}" @else maxlength="255" @endif class="{{ $input }}">
    @endif
    @if(! empty($field['help']))<p class="mt-1 text-xs text-gray-500">{{ $field['help'] }}</p>@endif
    @error('data.' . $name)<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
</div>
