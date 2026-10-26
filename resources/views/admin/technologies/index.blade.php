@extends('admin.layout')

@section('title', 'Технологии')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <h2 class="text-2xl font-bold text-gray-800">Технологии</h2>
    <a href="{{ route('admin.technologies.create') }}" class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600">
        <x-icon name="plus" class="mr-2" />Нова технология
    </a>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Име</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Версия</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Проекти</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"></th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($technologies as $technology)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">
                                <a href="{{ route('admin.technologies.show', $technology) }}" class="text-blue-600 hover:underline">
                                    {{ $technology->name }}
                                </a>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800">
                                {{ $technology->version ?? 'N/A' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800">
                                {{ $technology->projects_count }} projects
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <a href="{{ route('admin.technologies.show', $technology) }}" class="text-blue-600 hover:text-blue-900 mr-3">Отвори</a>
                            <a href="{{ route('admin.technologies.edit', $technology) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">Редактирай</a>
                            <form action="{{ route('admin.technologies.destroy', $technology) }}" method="POST" class="inline" onsubmit="return confirm('Сигурни ли сте?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900" {{ $technology->projects_count > 0 ? 'disabled title="Технология с проекти не може да се изтрие"' : '' }}>Изтрий</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-4 text-center text-gray-500">Няма технологии.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <div class="px-6 py-4 border-t">
        {{ $technologies->links() }}
    </div>
</div>
@endsection
