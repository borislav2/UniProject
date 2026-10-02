@props(['name'])
@php($icon = \App\Support\Icons::get($name))
@if($icon)
<svg {{ $attributes->merge(['class' => 'inline-block shrink-0']) }} style="height:1em;width:{{ round($icon[0] / $icon[1], 4) }}em;vertical-align:-0.125em" viewBox="0 0 {{ $icon[0] }} {{ $icon[1] }}" fill="currentColor" aria-hidden="true" focusable="false"><path d="{{ $icon[2] }}"/></svg>
@endif
