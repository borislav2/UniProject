{{-- Преглед като в Google. Параметри: $id, $url, $titleInput и $descInput (id на полетата), $fallbackTitle, $fallbackDescription (когато полето е празно). --}}
<div>
    <p class="sx-snippet-label">Как изглежда в Google</p>
    <div class="sx-snippet" id="{{ $id }}" data-snippet data-title-input="{{ $titleInput }}" data-desc-input="{{ $descInput }}" data-fallback-title="{{ $fallbackTitle ?? '' }}" data-fallback-desc="{{ $fallbackDescription ?? '' }}">
        <div class="u"><i>C</i><span>{{ \App\Support\Seo\Snippet::breadcrumb($url) }}</span></div>
        <p class="t" data-t>{{ \App\Support\Seo\Snippet::truncateTitle((string) ($fallbackTitle ?? '')) }}</p>
        <p class="d" data-d>{{ \App\Support\Seo\Snippet::truncateDescription((string) ($fallbackDescription ?? '')) }}</p>
    </div>
</div>
