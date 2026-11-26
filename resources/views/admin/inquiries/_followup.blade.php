{{--
    Писмата след безплатната проверка и избраната платена оферта ($inquiry = Project от проверка на сайт): стъпка 0 до 3, спрени, избрана оферта
    и бутон „Спри писмата“ (App\Support\SiteCheck\FollowUps, Offers).
--}}
@if($inquiry->leadKind() === 'site-check')
    @php
        [$fuLabel, $fuClass] = \App\Support\SiteCheck\FollowUps::status($inquiry);
        $canStop = ! $inquiry->followup_stopped_at && (int) $inquiry->followup_step < \App\Support\SiteCheck\FollowUps::STEPS && ! $inquiry->site_check_offer;
    @endphp
    <div class="mt-3 flex flex-wrap items-center justify-between gap-3 rounded-lg border border-gray-200 bg-white px-4 py-3 text-sm" data-followup>
        <div class="min-w-0 space-y-1">
            <p class="font-semibold text-gray-800">Писма след проверката <span class="ml-1 rounded-full px-2 py-0.5 text-xs font-semibold {{ $fuClass }}">{{ $fuLabel }}</span></p>
            @if($inquiry->site_check_offer)
                <p class="text-xs text-gray-600">Поиска „{{ config('creatium.site_check_offers.' . $inquiry->site_check_offer . '.name') }}“ на {{ $inquiry->site_check_offer_at?->timezone('Europe/Sofia')->format('d.m.Y H:i') }}</p>
            @endif
            @if($inquiry->followup_stopped_at && ! $inquiry->site_check_offer)
                <p class="text-xs text-gray-500">Спрени на {{ $inquiry->followup_stopped_at->timezone('Europe/Sofia')->format('d.m.Y H:i') }}</p>
            @endif
            @if((int) $inquiry->followup_failures > 0 && ! $inquiry->followup_stopped_at)
                <p class="text-xs text-red-600">Неуспешни изпращания {{ $inquiry->followup_failures }}</p>
            @endif
        </div>
        @if($canStop)
            <form method="POST" action="{{ route('admin.projects.followup-stop', $inquiry) }}">
                @csrf
                <button type="submit" class="adm-btn ghost sm">Спри писмата</button>
            </form>
        @endif
    </div>
@endif
