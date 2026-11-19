{{--
    A pre-rendered Kreo still (tools/kreo/render.mjs, public/images/kreo): two files per theme, shown by the theme class, each with a 2 size srcset and its real
    width and height. Decorative (alt empty): the text beside it says everything. $base = file name without size and theme ('kreo-1-bg', 'team-en'),
    $wide / $narrow = the two widths, $height = height of the wide one, $class, $sizes, $eager (no lazy loading).
--}}
@php
    $wide = $wide ?? 960;
    $narrow = $narrow ?? 480;
    $height = $height ?? 720;
    $sizes = $sizes ?? '(min-width: 1024px) 560px, calc(100vw - 32px)';
    $url = fn (string $file) => asset('images/kreo/' . $file) . '?v=' . (@filemtime(public_path('images/kreo/' . $file)) ?: 1);
@endphp
@foreach(['' => 'g-kreo-light', '-dark' => 'g-kreo-dark'] as $theme => $themeClass)
    <img class="{{ trim(($class ?? '') . ' ' . $themeClass) }}" src="{{ $url($base . '-' . $wide . $theme . '.webp') }}" srcset="{{ $url($base . '-' . $narrow . $theme . '.webp') }} {{ $narrow }}w, {{ $url($base . '-' . $wide . $theme . '.webp') }} {{ $wide }}w" sizes="{{ $sizes }}" alt="" width="{{ $wide }}" height="{{ $height }}" @if(empty($eager))loading="lazy" @endif decoding="async">
@endforeach
