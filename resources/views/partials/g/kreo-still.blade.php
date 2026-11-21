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
    // A middle size (720) when the file exists: a 560px box on desktop and a phone at about 2x then load 720, not 960.
    $mid = $mid ?? 720;
@endphp
@foreach(['' => 'g-kreo-light', '-dark' => 'g-kreo-dark'] as $theme => $themeClass)
    @php
        $set = [$narrow => $base . '-' . $narrow . $theme . '.webp'];
        if ($mid > $narrow && $mid < $wide && is_file(public_path('images/kreo/' . $base . '-' . $mid . $theme . '.webp'))) {
            $set[$mid] = $base . '-' . $mid . $theme . '.webp';
        }
        $set[$wide] = $base . '-' . $wide . $theme . '.webp';
    @endphp
    <img class="{{ trim(($class ?? '') . ' ' . $themeClass) }}" src="{{ $url($base . '-' . $wide . $theme . '.webp') }}" srcset="{{ collect($set)->map(fn ($file, $w) => $url($file) . ' ' . $w . 'w')->implode(', ') }}" sizes="{{ $sizes }}" alt="" width="{{ $wide }}" height="{{ $height }}" @if(empty($eager))loading="lazy" @endif decoding="async">
@endforeach
