{{--
    BreadcrumbList за Google: в резултатите вместо адреса се показва пътят „Creatium Lab › Услуги › …“.
    Страница без родител: Начало › страницата. Услуга и статия: Начало › Услуги / Блог › страницата;
    страница за бранш: Начало › Изработка на сайт › страницата
    (името на последната идва от @section('breadcrumb') или от заглавието).
--}}
@php
    $crumbLabels = [
        'services' => __('Услуги'), 'packages' => __('Пакети и цени'), 'portfolio' => __('Проекти'),
        'site-check' => __('Безплатна проверка на сайт'), 'about' => __('За нас'), 'faq' => __('Често задавани въпроси'),
        'contact' => __('Контакти'), 'blog.index' => __('Блог'),
    ];
    $crumbParents = ['services.show' => 'services', 'blog.category' => 'blog.index', 'blog.show' => 'blog.index'];
    $crumbs = [];
    if ($routeName === 'industry') {
        $crumbs[] = [config('creatium.service_pages.website.menu'), \App\Http\Controllers\PageController::serviceUrl('website', 'bg')];
        $crumbs[] = [trim($__env->yieldContent('breadcrumb')), $canonicalUrl];
    } elseif (isset($crumbLabels[$routeName])) {
        $crumbs[] = [$crumbLabels[$routeName], $canonicalUrl];
    } elseif (isset($crumbParents[$routeName]) && ($parentUrl = rescue(fn () => lroute($crumbParents[$routeName]), null, false))) {
        $crumbs[] = [$crumbLabels[$crumbParents[$routeName]], $parentUrl];
        $crumbs[] = [trim($__env->yieldContent('breadcrumb', $__env->yieldContent('title'))), $canonicalUrl];
    }
@endphp
@if($crumbs)
    @php
        $items = [];
        foreach (array_merge([[__('Начало'), lroute('home')]], $crumbs) as $i => [$name, $url]) {
            $items[] = ['@type' => 'ListItem', 'position' => $i + 1, 'name' => html_entity_decode($name, ENT_QUOTES), 'item' => $url];
        }
    @endphp
    <script type="application/ld+json">{!! json_encode(['@context' => 'https://schema.org', '@type' => 'BreadcrumbList', 'itemListElement' => $items], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}</script>
@endif
