@extends('admin.layout')

@section('title', 'Блог')

@section('content')
<div class="flex flex-wrap items-center justify-between gap-4 mb-6">
    <p class="text-gray-600">Статиите се показват на <a href="{{ lroute('blog.index', [], 'bg') }}" target="_blank" class="text-brand-700 underline">/blog</a> и <a href="{{ lroute('blog.index', [], 'en') }}" target="_blank" class="text-brand-700 underline">/en/blog</a>.</p>
    <a href="{{ route('admin.posts.create') }}" class="inline-flex items-center gap-2 bg-brand-950 text-white px-4 py-2 rounded-lg font-semibold hover:bg-brand-800"><x-icon name="plus" /> Нова статия</a>
</div>

<div class="bg-white rounded-lg shadow overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200 text-sm">
        <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
            <tr>
                <th class="px-6 py-3">Заглавие</th>
                <th class="px-6 py-3">Езици</th>
                <th class="px-6 py-3">Категория</th>
                <th class="px-6 py-3">Статус</th>
                <th class="px-6 py-3">Дата</th>
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
                    <td class="px-6 py-4 text-right whitespace-nowrap">
                        @foreach($post->locales() as $l)
                            <a href="{{ $post->url($l) }}" target="_blank" class="text-gray-500 hover:text-brand-700 mr-3" title="Виж на сайта ({{ strtoupper($l) }})"><x-icon name="arrow-up-right-from-square" /> {{ strtoupper($l) }}</a>
                        @endforeach
                        <a href="{{ route('admin.posts.edit', $post) }}" class="text-brand-700 font-semibold">Редактирай</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-6 py-10 text-center text-gray-500">Още няма статии. <a href="{{ route('admin.posts.create') }}" class="text-brand-700 underline">Напишете първата.</a></td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $posts->links() }}</div>
@endsection
