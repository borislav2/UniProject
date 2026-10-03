@extends('admin.layout')

@section('title', 'SEO на страниците')

@section('content')
<p class="text-gray-600 mb-6 max-w-3xl">Тук задавате как изглежда всяка страница в Google и при споделяне. Ако поле е празно, сайтът използва текста по подразбиране. SEO на статиите се настройва в самата статия.</p>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200 text-sm">
        <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
            <tr><th class="px-6 py-3">Страница</th><th class="px-6 py-3">Настроено</th><th class="px-6 py-3"></th></tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @foreach($pages as $page => $label)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 font-medium text-gray-900">{{ $label }} <span class="ml-2 text-xs text-gray-500">{{ parse_url(lroute($page, [], 'bg'), PHP_URL_PATH) ?: '/' }}</span></td>
                    <td class="px-6 py-4">
                        @foreach(['bg' => 'BG', 'en' => 'EN'] as $l => $short)
                            <span class="px-2 py-0.5 rounded text-xs font-semibold {{ in_array($l, $filled[$page] ?? []) ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-500' }}">{{ $short }}</span>
                        @endforeach
                    </td>
                    <td class="px-6 py-4 text-right"><a href="{{ route('admin.seo.edit', $page) }}" class="text-brand-700 font-semibold">Редактирай</a></td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
