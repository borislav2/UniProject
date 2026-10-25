{{--
    Hero showcase: real demo sites (/proekti) on a laptop and a phone, so visitors see colourful client work, not our own theme.
    The laptop cycles three demos (12 s); each comes with its own "new booking" notification. The phone scrolls the demo shop.
    Decorative only (aria-hidden). Animates transform/opacity only and stops under prefers-reduced-motion.
--}}
@php
    $slides = [
        ['restorant', 'lozata.bg', 'calendar', __('Нова резервация'), __('Маса за 4 · Лозата')],
        ['salon', 'lavandula.bg', 'spa', __('Нов записан час'), __('Маникюр · Лавандула')],
        ['pokrivi', 'zdrav-pokriv.bg', 'helmet-safety', __('Заявка за оглед'), __('Покрив · Здрав покрив')],
    ];
@endphp
<div class="relative h-[360px] sm:h-[480px] lg:h-[520px]" aria-hidden="true">
    <div class="hidden md:block halo-spin absolute inset-8 rounded-full opacity-60 blur-3xl"></div>

    <div class="tilt absolute inset-0" data-tilt>
        {{-- Laptop --}}
        <div class="absolute left-0 top-12 w-[90%] sm:w-[86%]">
            <div class="relative rounded-t-[1.1rem] bg-gradient-to-b from-[#2a3446] to-[#151c28] p-[7px] pb-2 ring-1 ring-white/15 shadow-[0_50px_120px_-30px_rgb(0_0_0/0.95)]">
                <div class="absolute left-1/2 top-[3px] -translate-x-1/2 w-1 h-1 rounded-full bg-white/25"></div>
                <div class="overflow-hidden rounded-md bg-white">
                    <div class="flex items-center gap-2 bg-[#eef1f5] px-2.5 py-1.5">
                        <div class="flex gap-1">
                            <span class="w-2 h-2 rounded-full bg-[#ff5f57]"></span>
                            <span class="w-2 h-2 rounded-full bg-[#febc2e]"></span>
                            <span class="w-2 h-2 rounded-full bg-[#28c840]"></span>
                        </div>
                        <div class="relative flex-1 h-4 rounded bg-white text-[8px] text-gray-700">
                            @foreach($slides as [$slug, $domain])
                                <span class="slide absolute inset-0 flex items-center justify-center gap-1" style="--i: {{ $loop->index }}"><x-icon name="lock" class="text-[6px] text-emerald-600" /><span class="mock-text" data-text="{{ $domain }}"></span></span>
                            @endforeach
                        </div>
                    </div>
                    <div class="relative aspect-[16/10] overflow-hidden [container-type:inline-size] cursor-ew-resize" data-reveal>
                        {{-- Подсказката е върху екрана, за да не се застъпва с картата на AI асистента под лаптопа. --}}
                        <p class="reveal-hint pointer-events-none absolute bottom-2 left-1/2 z-20 -translate-x-1/2 flex items-center gap-1.5 whitespace-nowrap rounded-full bg-ink-950/80 px-3 py-1 text-[10px] font-semibold text-white ring-1 ring-white/15 shadow-lg"><span class="hint-arrows">←</span>
                            <span class="hidden [@media(hover:hover)]:inline">{{ __('Минете с курсора над екрана') }}</span>
                            <span class="[@media(hover:hover)]:hidden">{{ __('Докоснете екрана') }}</span>
                            <span class="hint-arrows">→</span></p>
                        @foreach($slides as [$slug])
                            <img src="{{ asset('images/demos/' . $slug . '.webp') }}" srcset="{{ demo_srcset($slug) }}" sizes="(min-width: 640px) 420px, 85vw" alt="" width="960" height="600" decoding="async" @if(! $loop->first) loading="lazy" @endif class="slide slide-zoom absolute inset-0 w-full h-full object-cover object-top" style="--i: {{ $loop->index }}">
                        @endforeach

                        {{-- Behind the site: the marketing we run for it. Revealed by moving the pointer left to right, a click or a tap. --}}
                        <div class="reveal-layer absolute inset-0 bg-[#0b1220] p-[2.2%] text-white">
                            <div class="flex items-center justify-between gap-2">
                                <span class="flex items-center gap-1 text-[clamp(8px,2.4cqw,12px)] font-bold"><span class="w-2 h-2 rounded-sm bg-gradient-to-br from-brand-300 to-emerald-400"></span>{{ __('Отчет · lozata.bg') }}</span>
                                <span class="rounded-full bg-white/10 px-1.5 py-px text-[clamp(7px,1.9cqw,10px)] text-gray-400">{{ __('Примерни данни') }}</span>
                            </div>
                            <div class="mt-[2%] grid h-[88%] grid-cols-3 grid-rows-2 gap-[1.6%] text-[clamp(7px,2.05cqw,11px)] leading-snug">
                                {{-- GA4 --}}
                                <div class="col-span-2 flex flex-col rounded-md bg-white/[0.05] ring-1 ring-white/10 p-[3%]">
                                    <div class="flex items-center justify-between text-gray-400"><span class="font-semibold text-[#f9ab00]">GA4</span><span>{{ __('последните 28 дни') }}</span></div>
                                    <div class="mt-1 flex items-end gap-3">
                                        <span><span class="block text-gray-400">{{ __('Потребители') }}</span><strong class="text-[clamp(11px,4cqw,22px)]">2 847</strong> <span class="text-emerald-400">↑ 38%</span></span>
                                        <span><span class="block text-gray-400">{{ __('Резервации') }}</span><strong class="text-[clamp(11px,4cqw,22px)]">164</strong> <span class="text-emerald-400">↑ 52%</span></span>
                                    </div>
                                    <svg viewBox="0 0 200 50" preserveAspectRatio="none" class="mt-auto w-full h-[45%]">
                                        <defs><linearGradient id="ga-fill" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#5b8fd1" stop-opacity="0.55"/><stop offset="1" stop-color="#5b8fd1" stop-opacity="0"/></linearGradient></defs>
                                        <path d="M0 42 L14 38 L28 40 L42 33 L56 35 L70 28 L84 30 L98 22 L112 25 L126 18 L140 20 L154 12 L168 15 L182 7 L200 4 L200 50 L0 50 Z" fill="url(#ga-fill)"/>
                                        <path d="M0 42 L14 38 L28 40 L42 33 L56 35 L70 28 L84 30 L98 22 L112 25 L126 18 L140 20 L154 12 L168 15 L182 7 L200 4" fill="none" stroke="#9cc0eb" stroke-width="1.6"/>
                                    </svg>
                                </div>
                                {{-- Monitoring --}}
                                <div class="flex flex-col rounded-md bg-white/[0.05] ring-1 ring-white/10 p-[5%]">
                                    <span class="font-semibold text-gray-300">{{ __('Мониторинг') }}</span>
                                    <span class="mt-1 flex items-center gap-1 text-emerald-400"><span class="relative flex w-1.5 h-1.5"><span class="absolute inset-0 rounded-full bg-emerald-400 animate-ping motion-reduce:animate-none"></span><span class="relative w-1.5 h-1.5 rounded-full bg-emerald-400"></span></span>{{ __('Онлайн') }}</span>
                                    <span class="mt-auto space-y-1 text-gray-400">
                                        <span class="flex justify-between"><span>Uptime</span><strong class="text-white">99,98%</strong></span>
                                        <span class="flex justify-between"><span>{{ __('Скорост') }}</span><strong class="text-white">0,8 s</strong></span>
                                        <span class="flex justify-between"><span>SSL</span><strong class="text-emerald-400">✓</strong></span>
                                    </span>
                                </div>
                                {{-- Keyword positions --}}
                                <div class="flex flex-col rounded-md bg-white/[0.05] ring-1 ring-white/10 p-[5%]">
                                    <span class="font-semibold text-gray-300">{{ __('Позиции в Google') }}</span>
                                    <span class="mt-auto space-y-1">
                                        @foreach([[__('ресторант Пловдив'), 3, 2], [__('вечеря Стария град'), 1, 4], [__('резервация маса'), 5, 7]] as [$keyword, $pos, $up])
                                            <span class="flex items-center gap-1"><span class="truncate text-gray-300">{{ $keyword }}</span><strong class="ml-auto">#{{ $pos }}</strong><span class="text-emerald-400">↑{{ $up }}</span></span>
                                        @endforeach
                                    </span>
                                </div>
                                {{-- GTM --}}
                                <div class="flex flex-col rounded-md bg-white/[0.05] ring-1 ring-white/10 p-[5%]">
                                    <span class="font-semibold text-[#8ab4f8]">Tag Manager</span>
                                    <span class="mt-auto space-y-1 text-gray-300">
                                        @foreach(['GA4 · ' . __('конфигурация'), __('Конверсия: запитване'), 'Consent Mode v2'] as $tag)
                                            <span class="flex items-center gap-1"><span class="text-emerald-400">✓</span><span class="truncate">{{ $tag }}</span></span>
                                        @endforeach
                                    </span>
                                </div>
                                {{-- Automated emails --}}
                                <div class="flex flex-col rounded-md bg-white/[0.05] ring-1 ring-white/10 p-[5%]">
                                    <span class="font-semibold text-gray-300">{{ __('Автоматични имейли') }}</span>
                                    <span class="mt-auto space-y-1 text-gray-300">
                                        @foreach([[__('Потвърждение'), __('изпратен')], [__('Напомняне'), __('утре 18:00')], [__('Отзив в Google'), __('след визита')]] as [$mail, $state])
                                            <span class="flex items-center gap-1"><x-icon name="envelope" class="text-[0.9em] text-brand-300" /><span class="truncate">{{ $mail }}</span><span class="ml-auto shrink-0 text-gray-400">{{ $state }}</span></span>
                                        @endforeach
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="reveal-handle absolute inset-y-0 w-0.5 bg-white/90 shadow-[0_0_12px_rgb(255_255_255/0.8)]">
                            <span class="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 flex w-6 h-6 items-center justify-center rounded-full bg-white text-[8px] font-black text-brand-950 shadow-lg">‹ ›</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="relative mx-[-6%] h-3 rounded-b-xl bg-gradient-to-b from-[#c9d1dc] to-[#7d8897] shadow-[0_20px_40px_-10px_rgb(0_0_0/0.9)]">
                <div class="absolute left-1/2 top-0 -translate-x-1/2 w-[16%] h-1.5 rounded-b-md bg-[#9aa4b2]"></div>
            </div>
        </div>

        {{-- Phone: scrolls through the demo shop --}}
        <div class="phone-wrap absolute right-0 bottom-0 w-[33%] sm:w-[31%]">
            <div class="animate-float rounded-[2rem] bg-gradient-to-b from-[#3a4558] to-[#121823] p-[5px] ring-1 ring-white/20 shadow-[0_30px_80px_-20px_rgb(0_0_0/0.95)]" style="animation-delay: -2s">
                <div class="relative overflow-hidden rounded-[1.7rem] bg-white aspect-[9/19.5]">
                    <div class="absolute left-1/2 top-1.5 -translate-x-1/2 w-[34%] h-3.5 rounded-full bg-black z-10"></div>
                    <img src="{{ asset('images/demos/onlain-magazin-mobile.webp') }}" srcset="{{ demo_srcset('onlain-magazin-mobile', [240], 375) }}" sizes="(min-width: 1024px) 140px, 120px" alt="" width="375" height="1600" loading="lazy" decoding="async" class="phone-scroll absolute inset-x-0 top-0 w-full h-auto">
                </div>
            </div>
        </div>

        {{-- A notification for each site on the laptop --}}
        <div class="absolute -right-2 sm:-right-6 lg:right-0 2xl:-right-4 top-0 w-56">
            @foreach($slides as [$slug, $domain, $icon, $title, $text])
                <div class="slide-toast absolute right-0 top-0" style="--i: {{ $loop->index }}">
                    <div class="edge glass relative rounded-2xl bg-ink-900/95 px-4 py-3 flex items-center gap-3 shadow-[0_20px_50px_-20px_rgb(0_0_0/0.8)] whitespace-nowrap">
                        <span class="relative flex w-9 h-9 shrink-0 rounded-full bg-emerald-400/15 items-center justify-center">
                            <x-icon :name="$icon" class="text-emerald-400 text-sm" />
                            <span class="absolute top-0.5 right-0.5 w-2 h-2 rounded-full bg-emerald-400 animate-ping motion-reduce:animate-none"></span>
                        </span>
                        <span class="text-sm leading-tight"><strong class="block text-white">{{ $title }}</strong><span class="text-gray-400 text-xs">{{ $text }}</span></span>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- AI assistant recommends a business --}}
        <div class="hidden sm:block absolute sm:-left-8 bottom-0 sm:w-[56%]">
            <div class="edge glass relative rounded-2xl bg-ink-900/95 p-3 shadow-[0_20px_50px_-20px_rgb(0_0_0/0.8)] space-y-2">
                <div class="flex items-center gap-2 text-[10px] font-semibold text-gray-400"><span class="w-5 h-5 rounded-md bg-gradient-to-br from-brand-400 to-emerald-400 flex items-center justify-center"><x-icon name="robot" class="text-[9px] text-white" /></span>{{ __('AI асистент') }}</div>
                <p class="ml-auto w-fit max-w-[90%] rounded-xl rounded-br-sm bg-brand-500/30 px-2.5 py-1.5 text-[10px] text-white">{{ __('Къде да вечерям в Пловдив?') }}</p>
                <div class="grid">
                    <span class="ai-typing col-start-1 row-start-1 flex w-fit gap-1 rounded-xl rounded-bl-sm bg-white/10 px-2.5 py-2"><span class="typing-dot"></span><span class="typing-dot" style="animation-delay: .15s"></span><span class="typing-dot" style="animation-delay: .3s"></span></span>
                    <p class="ai-answer col-start-1 row-start-1 w-fit max-w-[95%] rounded-xl rounded-bl-sm bg-white/10 px-2.5 py-1.5 text-[10px] leading-snug text-gray-200">{!! __('Препоръчвам :name: домашна кухня и резервация онлайн.', ['name' => '<strong class="text-white">' . e(__('ресторант „Лозата“')) . '</strong>']) !!}</p>
                </div>
            </div>
        </div>
    </div>
</div>
