@extends('layouts.public')

{{-- The blog list in design G: a hairline category row, a grid of posts with equal 16:10 covers, plain pagination. --}}
@section('title', $category ? __('Новини за :category', ['category' => $category->trOrBg('name')]) : __('Новини за клиенти от Google и ChatGPT'))
@section('breadcrumb', $category ? $category->trOrBg('name') : '')
@section('meta_description', $category ? __('Статии от категория „:category“. Практични съвети за бизнеси, просто и с примери.', ['category' => $category->trOrBg('name')]) : __('Практични статии за собственици на бизнес. Как да ви намират в Google и ChatGPT, какво трябва да има сайтът и кои реклами и имейли се изплащат.'))
@section('g_lean', 'inner')

@section('content')
@include('partials.g.page-head', ['eyebrow' => $category ? __('Новини') : null, 'heading' => $category ? $category->trOrBg('name') : __('Новини'), 'sub' => __('Практични съвети как клиентите да ви намират в Google и ChatGPT и да ви пишат.')])

<section class="g-s" style="border-bottom:0">
    <div class="g-wrap">
        @unless($category)
            <div class="g-prose g-blog-intro">
                <p>{{ __('Пишем какво носи клиенти на един бизнес. Как да ви намират в Google и в ChatGPT, какво трябва да има сайтът, кога рекламите в Google и Meta се изплащат и как имейлите връщат клиенти. Има и бележки от eCommerce Summit и Digital Community Day и какво ще промени Google Shopping, когато стигне до България.') }}</p>
                <p>{{ __('Текстовете са за собственици на салони, ресторанти, сервизи, строителни фирми и магазини, без жаргон. Повечето статии завършват с нещо, което можете да направите още тази седмица.') }}</p>
            </div>
        @endunless
        @if($categories->count() > 1)
            <nav class="g-cats" aria-label="{{ __('Категории') }}">
                <a href="{{ lroute('blog.index') }}" @if(! $category) aria-current="page" @endif>{{ __('Всички') }}</a>
                @foreach($categories as $item)
                    <a href="{{ lroute('blog.category', ['slug' => $item->tr('slug')]) }}" @if($category && $category->is($item)) aria-current="page" @endif>{{ $item->trOrBg('name') }}</a>
                @endforeach
            </nav>
        @endif

        @if($posts->isEmpty())
            <div class="g-empty">
                @if(app()->getLocale() === 'en' && ! $category)
                    <h2 class="g-h">Our articles are in Bulgarian for now</h2>
                    <p>Read them on the <a class="g-link" href="{{ lroute('blog.index', [], 'bg') }}" hreflang="bg">Bulgarian blog</a>, or write to us if you have a question about your website or Google.</p>
                @else
                    <h2 class="g-h">{{ __('Скоро ще публикуваме първите статии') }}</h2>
                    <p>{{ __('Междувременно, ако имате въпрос за сайта или за Google, пишете ни.') }}</p>
                @endif
            </div>
        @else
            <div class="g-posts">
                @foreach($posts as $post)
                    @include('g.blog._card')
                @endforeach
            </div>

            @if($posts->hasPages())
                <nav class="g-pager" aria-label="{{ __('Страници') }}">
                    @if($posts->onFirstPage())<span></span>@else<a class="g-btn g-ghost g-sm" href="{{ $posts->previousPageUrl() }}" rel="prev">{{ __('По-нови') }}</a>@endif
                    <span class="g-mono g-mute">{{ __('Страница :current от :last', ['current' => $posts->currentPage(), 'last' => $posts->lastPage()]) }}</span>
                    @if($posts->hasMorePages())<a class="g-btn g-ghost g-sm" href="{{ $posts->nextPageUrl() }}" rel="next">{{ __('По-стари') }}</a>@else<span></span>@endif
                </nav>
            @endif
        @endif
    </div>
</section>
@endsection
