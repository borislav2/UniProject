{{--
    Hero illustration: the client's future website on a laptop and a phone, with the marketing working around it.
    A loop of about 7 s: the cursor clicks "Запитване" -> a new inquiry arrives; an AI assistant answers with "Вашият бизнес".
    Decorative only (aria-hidden), no invented numbers. Everything stops under prefers-reduced-motion.
--}}
<div class="relative h-[360px] sm:h-[500px] lg:h-[540px]" aria-hidden="true">
    <div class="hidden md:block halo-spin absolute inset-8 rounded-full opacity-60 blur-3xl"></div>

    <div class="tilt absolute inset-0" data-tilt>
        {{-- Laptop / browser --}}
        <div class="absolute left-0 top-10 w-[88%] sm:w-[84%]">
            <div class="edge glass relative rounded-[1.4rem] p-1.5 shadow-[0_40px_120px_-30px_rgb(0_0_0/0.9)]">
                <div class="rounded-[1.1rem] bg-ink-900/95 overflow-hidden ring-1 ring-white/5">
                    <div class="flex items-center gap-3 px-3.5 py-2.5 border-b border-white/5">
                        <div class="flex gap-1.5">
                            <span class="w-2.5 h-2.5 rounded-full bg-[#ff5f57]/80"></span>
                            <span class="w-2.5 h-2.5 rounded-full bg-[#febc2e]/80"></span>
                            <span class="w-2.5 h-2.5 rounded-full bg-[#28c840]/80"></span>
                        </div>
                        <div class="flex-1 flex items-center justify-center gap-2 rounded-md bg-white/5 px-3 py-1 text-[10px] text-gray-400">
                            <x-icon name="lock" class="text-[8px] text-emerald-400" /> {{ __('vashiat-biznes.bg') }}
                        </div>
                    </div>
                    <div class="p-3 space-y-2.5">
                        <div class="sheen relative overflow-hidden rounded-xl bg-brand-gradient px-4 py-4 sm:px-5 sm:py-5 ring-1 ring-white/10">
                            <div class="absolute inset-0 bg-grid-light opacity-50"></div>
                            <div class="absolute -right-10 -top-12 w-40 h-40 rounded-full bg-gradient-to-br from-brand-300/60 to-transparent blur-xl"></div>
                            <div class="absolute right-6 bottom-4 w-16 h-16 rounded-2xl rotate-12 bg-white/10 ring-1 ring-white/20 backdrop-blur-sm"></div>
                            <div class="relative flex items-center justify-between">
                                <span class="flex items-center gap-1.5 text-[10px] font-bold text-white"><span class="w-3.5 h-3.5 rounded-md bg-gradient-to-br from-white to-brand-200"></span>{{ __('Вашият бизнес') }}</span>
                                <span class="hidden sm:flex gap-2"><span class="h-1 w-6 rounded-full bg-white/35"></span><span class="h-1 w-6 rounded-full bg-white/35"></span><span class="h-1 w-6 rounded-full bg-white/35"></span></span>
                            </div>
                            <p class="relative mt-4 max-w-[70%] text-base sm:text-lg font-extrabold leading-tight text-white">{{ __('Сайт, който носи клиенти') }}</p>
                            <div class="relative mt-2 h-1.5 w-1/2 rounded-full bg-white/30"></div>
                            <div class="relative mt-3.5 flex gap-2">
                                <span class="relative rounded-lg bg-white px-3 py-1.5 text-[10px] font-bold text-brand-950">
                                    {{ __('Запитване') }}
                                    <span class="click-ring absolute left-1/2 top-1/2 -ml-3 -mt-3 w-6 h-6 rounded-full border-2 border-white"></span>
                                    <svg class="hero-cursor absolute left-1/2 top-1/2 w-5 h-5 drop-shadow-[0_4px_8px_rgb(0_0_0/0.5)]" viewBox="0 0 24 24"><path d="M4 2l15 9-6.5 1.6L9.6 19z" fill="#fff" stroke="#0f1a2b" stroke-width="1.5" stroke-linejoin="round"/></svg>
                                </span>
                                <span class="rounded-lg bg-white/15 px-3 py-1.5 text-[10px] font-bold text-white ring-1 ring-white/20">{{ __('Обадете се') }}</span>
                            </div>
                        </div>
                        <div class="grid grid-cols-3 gap-2.5">
                            <div class="col-span-2 rounded-lg bg-white/[0.04] ring-1 ring-white/5 p-2.5">
                                <div class="flex items-center justify-between text-[9px] font-semibold text-gray-400"><span>{{ __('Посещения') }}</span><x-icon name="chart-line" class="text-brand-300" /></div>
                                <div class="mt-2 flex h-12 items-end gap-1">
                                    @foreach([28, 40, 36, 52, 47, 63, 70, 86, 94] as $h)
                                        <span class="grow-bar flex-1 rounded-t-[2px] bg-gradient-to-t from-brand-600 to-brand-300" style="height: {{ $h }}%; --d: {{ 500 + $loop->index * 60 }}ms"></span>
                                    @endforeach
                                </div>
                            </div>
                            <div class="space-y-1.5">
                                @foreach([['magnifying-glass', 'Google'], ['robot', __('AI търсене')], ['envelope-open-text', __('Имейли')]] as [$icon, $label])
                                    <div class="flex items-center gap-1.5 rounded-md bg-white/[0.04] ring-1 ring-white/5 px-2 py-1.5">
                                        <x-icon :name="$icon" class="text-[8px] text-brand-200" />
                                        <span class="truncate text-[9px] font-semibold text-gray-300">{{ $label }}</span>
                                        <span class="ml-auto w-1.5 h-1.5 shrink-0 rounded-full bg-emerald-400"></span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Phone --}}
        <div class="absolute right-0 bottom-0 w-[36%] sm:w-[34%]">
            <div class="animate-float rounded-[1.8rem] bg-gradient-to-b from-white/25 to-white/5 p-[3px] shadow-[0_30px_80px_-20px_rgb(0_0_0/0.9)]" style="animation-delay: -2s">
                <div class="relative rounded-[1.65rem] bg-ink-950 p-1.5">
                    <div class="absolute left-1/2 top-2.5 -translate-x-1/2 w-10 h-2.5 rounded-full bg-black z-10"></div>
                    <div class="rounded-[1.35rem] bg-ink-900 overflow-hidden aspect-[9/18.5] flex flex-col">
                        <div class="relative bg-brand-gradient px-3 pt-6 pb-3">
                            <div class="absolute inset-0 bg-grid-light opacity-40"></div>
                            <div class="relative flex items-center justify-between"><span class="w-3 h-3 rounded bg-white/90"></span><x-icon name="bars" class="text-[8px] text-white/80" /></div>
                            <p class="relative mt-3 text-[10px] font-extrabold leading-tight text-white">{{ __('Сайт, който носи клиенти') }}</p>
                            <span class="relative mt-2 inline-flex items-center gap-1 rounded-md bg-white px-2 py-1 text-[8px] font-bold text-brand-950"><x-icon name="calendar" class="text-[7px]" />{{ __('Запазете час') }}</span>
                        </div>
                        <div class="flex-1 p-2.5 space-y-2">
                            <p class="text-[8px] font-semibold text-gray-400">{{ __('Утре') }}</p>
                            <div class="grid grid-cols-3 gap-1">
                                @foreach(['10:00', '11:00', '12:00', '14:00', '15:00', '16:00'] as $time)
                                    <span class="rounded py-1 text-center text-[7px] font-bold {{ $time === '11:00' ? 'bg-brand-500 text-white' : 'bg-white/5 text-gray-300' }}">{{ $time }}</span>
                                @endforeach
                            </div>
                            <div class="rounded-lg bg-white/[0.04] p-2">
                                <svg viewBox="0 0 64 24" class="w-full h-7" fill="none"><path class="draw-line" d="M2 21 L12 17 L22 19 L32 11 L42 13 L52 6 L62 2" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="color: var(--color-brand-300)"/></svg>
                            </div>
                            <div class="flex items-center gap-1.5 rounded-lg bg-emerald-400/10 px-2 py-1.5"><x-icon name="circle-check" class="text-[8px] text-emerald-400" /><span class="text-[7px] font-semibold text-emerald-300">{{ __('Часът е записан') }}</span></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- New inquiry: arrives right after the click --}}
        <div class="toast-loop absolute -right-2 sm:-right-6 lg:right-0 2xl:-right-4 top-0">
            <div class="edge glass relative rounded-2xl bg-ink-900/95 px-4 py-3 flex items-center gap-3 shadow-[0_20px_50px_-20px_rgb(0_0_0/0.8)]">
                <span class="relative flex w-9 h-9 rounded-full bg-emerald-400/15 items-center justify-center">
                    <x-icon name="bell" class="text-emerald-400 text-sm" />
                    <span class="absolute top-1 right-1 w-2 h-2 rounded-full bg-emerald-400 animate-ping motion-reduce:animate-none"></span>
                </span>
                <span class="text-sm leading-tight"><strong class="block text-white">{{ __('Ново запитване') }}</strong><span class="text-gray-400 text-xs">{{ __('от формата на сайта') }}</span></span>
            </div>
        </div>

        {{-- AI assistant recommends the business --}}
        <div class="hidden sm:block absolute sm:-left-8 bottom-2 sm:w-[58%]">
            <div class="edge glass relative rounded-2xl bg-ink-900/95 p-3 shadow-[0_20px_50px_-20px_rgb(0_0_0/0.8)] space-y-2">
                <div class="flex items-center gap-2 text-[10px] font-semibold text-gray-400"><span class="w-5 h-5 rounded-md bg-gradient-to-br from-brand-400 to-emerald-400 flex items-center justify-center"><x-icon name="robot" class="text-[9px] text-white" /></span>{{ __('AI асистент') }}</div>
                <p class="ml-auto w-fit max-w-[90%] rounded-xl rounded-br-sm bg-brand-500/30 px-2.5 py-1.5 text-[10px] text-white">{{ __('Кой е добър салон в Лозенец?') }}</p>
                <div class="grid">
                    <span class="ai-typing col-start-1 row-start-1 flex w-fit gap-1 rounded-xl rounded-bl-sm bg-white/10 px-2.5 py-2"><span class="typing-dot"></span><span class="typing-dot" style="animation-delay: .15s"></span><span class="typing-dot" style="animation-delay: .3s"></span></span>
                    <p class="ai-answer col-start-1 row-start-1 w-fit max-w-[95%] rounded-xl rounded-bl-sm bg-white/10 px-2.5 py-1.5 text-[10px] leading-snug text-gray-200">{!! __('Препоръчвам :name: отлични отзиви и онлайн записване.', ['name' => '<strong class="text-white">' . e(__('Вашият бизнес')) . '</strong>']) !!}</p>
                </div>
            </div>
        </div>
    </div>
</div>
