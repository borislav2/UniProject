<div id="contact-form" class="bg-white rounded-2xl p-6 md:p-8 text-gray-900 dark:bg-ink-900 dark:text-gray-100 dark:ring-1 dark:ring-white/10">
    @if(session('success'))
        <div class="text-center py-8" role="status">
            <div class="w-14 h-14 mx-auto mb-4 rounded-full bg-green-100 flex items-center justify-center dark:bg-green-500/15">
                <x-icon name="check" class="text-green-600 text-xl dark:text-green-400" />
            </div>
            <h3 class="text-xl font-bold mb-2">{{ __('Благодарим!') }}</h3>
            <p class="text-gray-600 dark:text-gray-300">{{ session('success') }}</p>
        </div>
    @else
        <form action="{{ lroute('contact.submit') }}" method="POST" class="space-y-4" novalidate>
            @csrf
            <div class="absolute -left-[9999px]" aria-hidden="true">
                <label for="website">{{ __('Не попълвайте това поле') }}</label>
                <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
            </div>
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1 dark:text-gray-200">{{ __('Име') }}</label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" required autocomplete="name"
                       class="w-full px-4 py-3 border border-gray-200 bg-gray-50/60 rounded-xl focus:bg-white focus:outline-none focus:ring-4 focus:ring-brand-100 focus:border-brand-400 transition dark:bg-white/5 dark:border-white/15 dark:text-white dark:focus:bg-white/10 dark:focus:ring-brand-500/30">
                @error('name')<p class="text-red-600 text-sm mt-1 dark:text-red-400">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="phone" class="block text-sm font-medium text-gray-700 mb-1 dark:text-gray-200">{{ __('Телефон') }}</label>
                <input type="tel" id="phone" name="phone" value="{{ old('phone') }}" required autocomplete="tel" inputmode="tel" placeholder="{{ __('08X XXX XXXX') }}"
                       class="w-full px-4 py-3 border border-gray-200 bg-gray-50/60 rounded-xl focus:bg-white focus:outline-none focus:ring-4 focus:ring-brand-100 focus:border-brand-400 transition dark:bg-white/5 dark:border-white/15 dark:text-white dark:focus:bg-white/10 dark:focus:ring-brand-500/30 placeholder:text-gray-500 dark:placeholder:text-gray-400">
                @error('phone')<p class="text-red-600 text-sm mt-1 dark:text-red-400">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="service" class="block text-sm font-medium text-gray-700 mb-1 dark:text-gray-200">{{ __('С какво да помогнем?') }}</label>
                <div class="relative">
                    <select id="service" name="service" required
                            class="w-full px-4 py-3 border border-gray-200 bg-gray-50/60 rounded-xl focus:bg-white focus:outline-none focus:ring-4 focus:ring-brand-100 focus:border-brand-400 transition dark:bg-white/5 dark:border-white/15 dark:text-white dark:focus:bg-white/10 dark:focus:ring-brand-500/30 appearance-none pr-10 {{ old('service') ? '' : 'text-gray-500 dark:text-gray-400' }}" onchange="this.classList.remove('text-gray-500', 'dark:text-gray-400')">
                        <option value="" disabled {{ old('service') ? '' : 'selected' }}>{{ __('Изберете услуга') }}</option>
                        @foreach(site('contact_topics') as $key => $topic)
                            <option value="{{ $key }}" class="text-gray-900 dark:text-white" @selected(old('service') === $key)>{{ $topic }}</option>
                        @endforeach
                    </select>
                    <x-icon name="chevron-down" class="pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 text-xs text-gray-500 dark:text-gray-400" />
                </div>
                @error('service')<p class="text-red-600 text-sm mt-1 dark:text-red-400">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="message" class="block text-sm font-medium text-gray-700 mb-1 dark:text-gray-200">{{ __('Нещо повече за бизнеса ви') }} <span class="text-gray-500 font-normal dark:text-gray-400">{{ __('(по желание)') }}</span></label>
                <textarea id="message" name="message" rows="3"
                          class="w-full px-4 py-3 border border-gray-200 bg-gray-50/60 rounded-xl focus:bg-white focus:outline-none focus:ring-4 focus:ring-brand-100 focus:border-brand-400 transition dark:bg-white/5 dark:border-white/15 dark:text-white dark:focus:bg-white/10 dark:focus:ring-brand-500/30">{{ old('message') }}</textarea>
                @error('message')<p class="text-red-600 text-sm mt-1 dark:text-red-400">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="flex items-start gap-2 text-sm text-gray-600 dark:text-gray-300">
                    <input type="checkbox" name="consent" value="1" class="mt-1 accent-brand-600" {{ old('consent') ? 'checked' : '' }}>
                    <span>{!! __('Съгласен/на съм да използвате данните ми, за да ми отговорите. Прочетох :link.', ['link' => '<a href="' . e(lroute('privacy')) . '" target="_blank" class="text-brand-700 underline dark:text-brand-300">' . e(__('Политиката за поверителност')) . '</a>']) !!}</span>
                </label>
                @error('consent')<p class="text-red-600 text-sm mt-1 dark:text-red-400">{{ $message }}</p>@enderror
            </div>
            <button type="submit" class="w-full inline-flex items-center justify-center gap-2 bg-brand-950 text-white py-3.5 px-6 rounded-xl font-semibold hover:bg-brand-800 transition-colors dark:bg-white dark:text-brand-950 dark:hover:bg-brand-100">
                {{ __('Изпратете запитване') }} <x-icon name="paper-plane" class="text-sm" />
            </button>
        </form>
    @endif
</div>
@if(session('success') || $errors->any())
    <script>document.getElementById('contact-form').scrollIntoView({ block: 'center' });</script>
@endif
