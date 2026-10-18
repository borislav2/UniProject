@extends('admin.layout')

@section('title', 'Отговор на отзив')

@section('content')
@if(session('send_error'))
    <div class="mb-6 rounded-lg border border-yellow-300 bg-yellow-50 px-4 py-3 text-sm text-yellow-900" role="alert">{{ session('send_error') }}</div>
@endif

<a href="{{ route('admin.reviews.index') }}" class="mb-4 inline-flex items-center gap-2 text-sm text-gray-600 hover:text-gray-900"><x-icon name="arrow-left" /> Всички чернови</a>

<div class="grid gap-6 lg:grid-cols-2">
    <section class="rounded-lg bg-white p-5 shadow">
        <h3 class="font-bold text-gray-900">{{ $reply->business_name }}</h3>
        <p class="text-sm text-gray-500">{{ $reply->reviewer_name ?: 'Без име' }} · <span class="text-yellow-600">{{ str_repeat('★', $reply->rating) }}</span><span class="text-gray-300">{{ str_repeat('★', 5 - $reply->rating) }}</span></p>
        <blockquote class="mt-4 whitespace-pre-line border-l-4 border-gray-200 pl-4 text-gray-700">{{ $reply->review_text }}</blockquote>
        @if($reply->notes)<p class="mt-4 text-sm text-gray-600"><span class="font-semibold">Да спомене:</span> {{ $reply->notes }}</p>@endif
    </section>

    <section class="rounded-lg bg-white p-5 shadow">
        <div class="flex items-center justify-between gap-3">
            <h3 class="font-bold text-gray-900">Чернова на отговора</h3>
            <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs text-gray-600">{{ \App\Models\ReviewReply::SOURCES[$reply->source] ?? $reply->source }}</span>
        </div>
        <form action="{{ route('admin.reviews.update', $reply) }}" method="POST" class="mt-3 space-y-3">
            @csrf @method('PATCH')
            <textarea id="reply-text" name="reply_text" rows="9" required maxlength="5000" class="w-full rounded border border-gray-300 px-3 py-2 text-gray-900">{{ old('reply_text', $reply->reply_text) }}</textarea>
            @error('reply_text')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
            <div class="flex flex-wrap gap-2">
                <button type="button" onclick="navigator.clipboard.writeText(document.getElementById('reply-text').value).then(() => { this.textContent = 'Копирано'; })" class="inline-flex items-center gap-2 rounded-lg bg-brand-950 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-800"><x-icon name="clipboard-list" /> Копирай</button>
                <button class="inline-flex items-center gap-2 rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-800 hover:bg-gray-50"><x-icon name="floppy-disk" /> Запази промените</button>
                <a href="{{ route('admin.reviews.index', ['from' => $reply->id]) }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-800 hover:bg-gray-50"><x-icon name="plus" /> Друг отзив за {{ \Illuminate\Support\Str::limit($reply->business_name, 30) }}</a>
            </div>
        </form>
        <p class="mt-4 text-xs text-gray-500">Прочетете я, преди да я публикувате: не трябва да обещава нищо, което бизнесът не е казал.</p>
    </section>
</div>
@endsection
