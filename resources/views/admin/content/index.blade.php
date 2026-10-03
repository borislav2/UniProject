@extends('admin.layout')

@section('title', 'Текстове по страниците')

@section('content')
<p class="text-gray-600 mb-6 max-w-3xl">Тук сменяте текстовете на сайта без програмиране. Промяната се вижда веднага. Ако нещо се обърка, „Върни по подразбиране“ връща оригиналния текст.</p>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200 text-sm">
        <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
            <tr><th class="px-6 py-3">Част от сайта</th><th class="px-6 py-3">Български</th><th class="px-6 py-3">English</th></tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @foreach($blocks as $key => $label)
                @php($rows = ($edited[$key] ?? collect())->keyBy('locale'))
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 font-medium text-gray-900">{{ $label }}</td>
                    @foreach(['bg', 'en'] as $l)
                        <td class="px-6 py-4">
                            <a href="{{ route('admin.content.edit', ['key' => $key, 'lang' => $l]) }}" class="text-brand-700 font-semibold">Редактирай</a>
                            @if($rows->has($l))
                                <span class="ml-2 text-xs text-gray-500" title="Променено">променено {{ $rows[$l]->updated_at->format('d.m.Y') }}</span>
                            @endif
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
