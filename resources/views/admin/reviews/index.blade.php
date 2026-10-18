@extends('admin.layout')

@section('title', 'Отговори на отзиви')

@section('content')

@unless($usesClaude)
    <div class="mb-6 rounded-lg border border-yellow-300 bg-yellow-50 px-4 py-3 text-sm text-yellow-900" role="status">
        <p class="font-semibold">Claude не е свързан, затова черновите са от шаблон според звездите.</p>
        <p class="mt-1">За отговори, които засягат точно написаното в отзива, добавете <code class="rounded bg-yellow-100 px-1">ANTHROPIC_API_KEY</code> в <code class="rounded bg-yellow-100 px-1">.env</code> на сървъра (ключ от console.anthropic.com, плаща се на употреба: под 1 стотинка на отговор).</p>
    </div>
@endunless

<div class="grid gap-6 lg:grid-cols-5">
    <form action="{{ route('admin.reviews.store') }}" method="POST" class="space-y-4 rounded-lg bg-white p-5 shadow lg:col-span-3">
        @csrf
        <h3 class="font-bold text-gray-900">Нов отзив</h3>
        <p class="text-sm text-gray-600">Поставете отзива от Google. Получавате чернова на отговор, която преглеждате и публикувате ръчно от профила на бизнеса.</p>

        <div class="grid gap-4 sm:grid-cols-2">
            <label class="block text-sm">
                <span class="font-semibold text-gray-800">Бизнес (клиентът)</span>
                <input type="text" name="business_name" value="{{ old('business_name', $defaults['business_name']) }}" list="review-businesses" required maxlength="150" class="mt-1 w-full rounded border border-gray-300 px-3 py-2" placeholder="напр. Ресторант Лозата">
                <datalist id="review-businesses">@foreach($businesses as $business)<option value="{{ $business }}">@endforeach</datalist>
                @error('business_name')<span class="mt-1 block text-red-600">{{ $message }}</span>@enderror
            </label>
            <label class="block text-sm">
                <span class="font-semibold text-gray-800">Вид бизнес <span class="font-normal text-gray-500">(по желание)</span></span>
                <input type="text" name="business_type" value="{{ old('business_type', $defaults['business_type']) }}" maxlength="100" class="mt-1 w-full rounded border border-gray-300 px-3 py-2" placeholder="ресторант, салон, зъболекар…">
            </label>
            <label class="block text-sm">
                <span class="font-semibold text-gray-800">Име на клиента от отзива</span>
                <input type="text" name="reviewer_name" value="{{ old('reviewer_name') }}" maxlength="100" class="mt-1 w-full rounded border border-gray-300 px-3 py-2" placeholder="напр. Мария Петрова">
            </label>
            <fieldset class="text-sm">
                <legend class="font-semibold text-gray-800">Оценка</legend>
                <div class="mt-1 flex gap-1">
                    @foreach(range(1, 5) as $stars)
                        <label class="cursor-pointer">
                            <input type="radio" name="rating" value="{{ $stars }}" class="peer sr-only" @checked((int) old('rating') === $stars) required>
                            <span class="inline-flex h-10 w-10 items-center justify-center rounded border border-gray-300 font-semibold text-gray-700 peer-checked:border-yellow-500 peer-checked:bg-yellow-400 peer-checked:text-gray-900 peer-focus-visible:ring-2 peer-focus-visible:ring-brand-500">{{ $stars }}★</span>
                        </label>
                    @endforeach
                </div>
                @error('rating')<span class="mt-1 block text-red-600">{{ $message }}</span>@enderror
            </fieldset>
        </div>

        <label class="block text-sm">
            <span class="font-semibold text-gray-800">Текст на отзива</span>
            <textarea name="review_text" rows="5" required maxlength="5000" class="mt-1 w-full rounded border border-gray-300 px-3 py-2">{{ old('review_text') }}</textarea>
            @error('review_text')<span class="mt-1 block text-red-600">{{ $message }}</span>@enderror
        </label>

        <label class="block text-sm">
            <span class="font-semibold text-gray-800">Какво да спомене отговорът <span class="font-normal text-gray-500">(по желание)</span></span>
            <textarea name="notes" rows="2" maxlength="1000" class="mt-1 w-full rounded border border-gray-300 px-3 py-2" placeholder="напр. сменихме климатика; поканете го на кафе за наша сметка; телефон за връзка 0888…">{{ old('notes') }}</textarea>
            <span class="mt-1 block text-xs text-gray-500">Отговорът използва само това, което е в отзива и тук. Не измисля отстъпки и обещания.</span>
        </label>

        <div class="grid gap-4 sm:grid-cols-2">
            <label class="block text-sm">
                <span class="font-semibold text-gray-800">Тон</span>
                <select name="tone" class="mt-1 w-full rounded border border-gray-300 px-3 py-2">
                    @foreach(\App\Models\ReviewReply::TONES as $key => $label)<option value="{{ $key }}" @selected(old('tone', $defaults['tone']) === $key)>{{ $label }}</option>@endforeach
                </select>
            </label>
            <label class="block text-sm">
                <span class="font-semibold text-gray-800">Подпис <span class="font-normal text-gray-500">(по желание)</span></span>
                <input type="text" name="signature" value="{{ old('signature', $defaults['signature']) }}" maxlength="150" class="mt-1 w-full rounded border border-gray-300 px-3 py-2" placeholder="напр. Иван, управител">
            </label>
        </div>

        <button class="inline-flex items-center gap-2 rounded-lg bg-brand-950 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-800"><x-icon name="reply" /> Напиши чернова</button>
    </form>

    <div class="rounded-lg bg-white p-5 text-sm text-gray-700 shadow lg:col-span-2 space-y-2">
        <h3 class="font-bold text-gray-900">Как да се ползва</h3>
        <p>1. Копирайте отзива от Google Business Profile на клиента.</p>
        <p>2. Изберете звездите и, ако има нужда, напишете какво да се спомене.</p>
        <p>3. Прегледайте черновата, поправете я и я публикувайте от профила.</p>
        <p class="text-gray-500">Отговорите на отзиви помагат на позицията в Google Maps. Отговаряйте и на добрите, и на лошите, по възможност до 1–2 дни.</p>
    </div>
</div>

<h3 class="mb-3 mt-10 font-bold text-gray-900">Последни чернови</h3>
<ul class="divide-y divide-gray-100 rounded-lg bg-white shadow">
    @forelse($replies as $reply)
        <li>
            <a href="{{ route('admin.reviews.show', $reply) }}" class="block p-4 hover:bg-gray-50">
                <span class="font-semibold text-gray-900">{{ $reply->business_name }}</span>
                <span class="text-sm text-yellow-600">{{ str_repeat('★', $reply->rating) }}<span class="text-gray-300">{{ str_repeat('★', 5 - $reply->rating) }}</span></span>
                <span class="text-sm text-gray-500">· {{ $reply->reviewer_name ?: 'без име' }} · {{ $reply->created_at->timezone('Europe/Sofia')->format('d.m.Y H:i') }} · {{ \App\Models\ReviewReply::SOURCES[$reply->source] ?? $reply->source }}</span>
                <span class="mt-1 block text-sm text-gray-600">{{ \Illuminate\Support\Str::limit($reply->review_text, 140) }}</span>
            </a>
        </li>
    @empty
        <li class="p-6 text-center text-gray-500">Още няма чернови.</li>
    @endforelse
</ul>
<div class="mt-4">{{ $replies->links() }}</div>
@endsection
