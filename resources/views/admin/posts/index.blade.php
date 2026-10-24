@extends('admin.layout')

@section('title', 'Блог')

@section('content')
<div class="flex flex-wrap items-center justify-between gap-4 mb-6">
    <p class="text-gray-600">Статиите се показват на <a href="{{ lroute('blog.index', [], 'bg') }}" target="_blank" class="text-brand-700 underline">/blog</a> и <a href="{{ lroute('blog.index', [], 'en') }}" target="_blank" class="text-brand-700 underline">/en/blog</a>.</p>
    <a href="{{ route('admin.posts.create') }}" class="inline-flex items-center gap-2 bg-brand-950 text-white px-4 py-2 rounded-lg font-semibold hover:bg-brand-800"><x-icon name="plus" /> Нова статия</a>
</div>

@php($fb = \App\Support\Facebook\FacebookPublisher::class)
@if(! $fb::configured())
    <div class="mb-6 rounded-lg border border-gray-200 bg-white px-4 py-3 text-sm text-gray-700">
        <strong>Facebook не е свързан.</strong> Статиите могат да излизат сами във Facebook страницата: новите веднага, по-старите по една на ден. Свързва се веднъж на сървъра с <code class="rounded bg-gray-100 px-1">php artisan creatium:facebook-connect</code> (стъпките са в <code class="rounded bg-gray-100 px-1">tools/facebook/README.md</code>).
    </div>
@elseif($paused = $fb::paused())
    <div class="mb-6 rounded-lg border border-red-300 bg-red-50 px-4 py-3 text-sm text-red-800">
        <strong>Публикуването във Facebook е спряно:</strong> {{ $paused }}. Ключът вероятно е невалиден: свържете страницата пак с <code class="rounded bg-white px-1">php artisan creatium:facebook-connect</code>.
    </div>
@else
    <div class="mb-6 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
        Свързано с Facebook страницата <strong>{{ config('services.facebook.page_name') ?: config('services.facebook.page_id') }}</strong>: новите статии излизат веднага, по-старите по една на ден (09:00–20:00).
    </div>
@endif

<div class="bg-white rounded-lg shadow overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200 text-sm">
        <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
            <tr>
                <th class="px-6 py-3">Заглавие</th>
                <th class="px-6 py-3">Езици</th>
                <th class="px-6 py-3">Категория</th>
                <th class="px-6 py-3">Статус</th>
                <th class="px-6 py-3">Дата</th>
                <th class="px-6 py-3">Facebook</th>
                <th class="px-6 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($posts as $post)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 font-medium text-gray-900"><a href="{{ route('admin.posts.edit', $post) }}" class="hover:text-brand-700">{{ $post->tr('title', 'bg') ?? $post->tr('title', 'en') }}</a></td>
                    <td class="px-6 py-4">
                        @foreach(['bg' => 'BG', 'en' => 'EN'] as $l => $label)
                            <span class="px-2 py-0.5 rounded text-xs font-semibold {{ $post->tr('title', $l) ? 'bg-brand-50 text-brand-700' : 'bg-gray-100 text-gray-400 line-through' }}">{{ $label }}</span>
                        @endforeach
                    </td>
                    <td class="px-6 py-4 text-gray-600">{{ $post->category?->trOrBg('name', 'bg') ?? '—' }}</td>
                    <td class="px-6 py-4">
                        @if($post->status === 'published' && $post->published_at?->isFuture())
                            <span class="px-2 py-0.5 rounded-full text-xs bg-amber-100 text-amber-800">Насрочена</span>
                        @elseif($post->status === 'published')
                            <span class="px-2 py-0.5 rounded-full text-xs bg-green-100 text-green-800">Публикувана</span>
                        @else
                            <span class="px-2 py-0.5 rounded-full text-xs bg-gray-100 text-gray-700">Чернова</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-gray-600 whitespace-nowrap">{{ ($post->published_at ?? $post->updated_at)->format('d.m.Y') }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($post->facebook_post_id)
                            <a href="{{ $fb::postUrl($post) }}" target="_blank" rel="noopener" class="px-2 py-0.5 rounded-full text-xs bg-blue-100 text-blue-800 hover:underline" title="{{ $post->facebook_posted_at?->timezone('Europe/Sofia')->format('d.m.Y H:i') }}">Публикувана</a>
                        @elseif($post->isVisible('bg'))
                            @if($post->facebook_error)
                                <span class="px-2 py-0.5 rounded-full text-xs bg-red-100 text-red-800" title="{{ $post->facebook_error }}">Грешка</span>
                            @else
                                <span class="px-2 py-0.5 rounded-full text-xs bg-gray-100 text-gray-700">Чака ред</span>
                            @endif
                            @if($fb::configured())
                                <form action="{{ route('admin.posts.facebook', $post) }}" method="POST" class="inline">
                                    @csrf
                                    <button class="ml-2 text-xs font-semibold text-brand-700 hover:underline">{{ $post->facebook_error ? 'Опитай пак' : 'Пусни сега' }}</button>
                                </form>
                            @endif
                        @else
                            <span class="text-xs text-gray-400">—</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-right whitespace-nowrap">
                        @foreach($post->locales() as $l)
                            <a href="{{ $post->url($l) }}" target="_blank" class="text-gray-500 hover:text-brand-700 mr-3" title="Виж на сайта ({{ strtoupper($l) }})"><x-icon name="arrow-up-right-from-square" /> {{ strtoupper($l) }}</a>
                        @endforeach
                        <a href="{{ route('admin.posts.edit', $post) }}" class="text-brand-700 font-semibold">Редактирай</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="px-6 py-10 text-center text-gray-500">Още няма статии. <a href="{{ route('admin.posts.create') }}" class="text-brand-700 underline">Напишете първата.</a></td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $posts->links() }}</div>
@endsection
