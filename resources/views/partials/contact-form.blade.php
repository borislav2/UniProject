<div id="contact-form" class="bg-white rounded-xl p-6 text-gray-900">
    @if(session('success'))
        <div class="text-center py-8" role="status">
            <div class="w-14 h-14 mx-auto mb-4 rounded-full bg-green-100 flex items-center justify-center">
                <i class="fas fa-check text-green-600 text-xl"></i>
            </div>
            <h3 class="text-xl font-bold mb-2">Благодарим ви!</h3>
            <p class="text-gray-600">{{ session('success') }}</p>
        </div>
    @else
        <form action="{{ route('contact.submit') }}" method="POST" class="space-y-4" novalidate>
            @csrf
            <div class="absolute -left-[9999px]" aria-hidden="true">
                <label for="website">Не попълвайте това поле</label>
                <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
            </div>
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Име</label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" required autocomplete="name"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                @error('name')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="contact" class="block text-sm font-medium text-gray-700 mb-1">Телефон или имейл</label>
                <input type="text" id="contact" name="contact" value="{{ old('contact') }}" required autocomplete="email"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                @error('contact')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="message" class="block text-sm font-medium text-gray-700 mb-1">Разкажете ни повече</label>
                <textarea id="message" name="message" rows="4" required
                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">{{ old('message') }}</textarea>
                @error('message')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="flex items-start gap-2 text-sm text-gray-600">
                    <input type="checkbox" name="consent" value="1" class="mt-1" {{ old('consent') ? 'checked' : '' }}>
                    <span>Запознат/а съм с <a href="{{ route('privacy') }}" target="_blank" class="text-blue-600 underline">Политиката за поверителност</a> и съм съгласен/а данните ми да бъдат използвани, за да се свържете с мен.</span>
                </label>
                @error('consent')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
            </div>
            <button type="submit" class="w-full bg-blue-600 text-white py-3 px-6 rounded-lg font-semibold hover:bg-blue-700 transition-colors">
                Изпратете запитване
            </button>
        </form>
    @endif
</div>
@if(session('success') || $errors->any())
    <script>document.getElementById('contact-form').scrollIntoView({ block: 'center' });</script>
@endif
