{{-- Раздели на SEO. $active: pages | posts | health | keywords | ai --}}
@php
    $seoReport = \App\Support\Seo\SiteHealth::latest();
    $tabs = [
        'pages' => ['Страници', route('admin.seo.index'), null],
        'posts' => ['Статии', route('admin.seo.posts'), null],
        'health' => ['SEO здраве', route('admin.seo.health'), $seoReport && $seoReport->errors > 0 ? $seoReport->errors : null],
        'keywords' => ['Ключови думи', route('admin.seo.keywords'), null],
        'ai' => ['AI споменавания', route('admin.seo.ai'), null],
    ];
@endphp
<nav class="td-tabs" aria-label="SEO">
    @foreach($tabs as $key => [$label, $url, $badge])
        <a href="{{ $url }}" class="td-tab" @if($active === $key) aria-current="true" @endif>{{ $label }} @if($badge)<i>{{ $badge }}</i>@endif</a>
    @endforeach
</nav>
