{{--
    A picture on an industry site (/demo/{slug}). When public/images/demos/{slug}/{name}.webp exists it is shown as a photo;
    until then the slot draws its own picture with shapes and colour, so every page is complete without photos.
--}}
@props(['slug', 'name', 'alt' => ''])
@php($file = "images/demos/{$slug}/{$name}.webp")
@if(is_file(public_path($file)))
    <img src="{{ asset($file) }}" alt="{{ $alt }}" loading="lazy" decoding="async" {{ $attributes->class('object-cover') }}>
@else
    <div {{ $attributes }} @if($alt !== '') role="img" aria-label="{{ $alt }}" @else aria-hidden="true" @endif>{{ $slot }}</div>
@endif
