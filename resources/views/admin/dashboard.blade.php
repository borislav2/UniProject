@extends('admin.layout')

@section('title', 'Табло')

@section('content')
@if($mailNotConfigured)
    <div class="mb-6 rounded-lg border border-yellow-300 bg-yellow-50 px-4 py-3 text-sm text-yellow-900">
        <strong>Запитванията не идват по имейл.</strong> Сървърът е с <code>MAIL_MAILER={{ config('mail.default') }}</code>, затова ги гледайте тук, в „Запитвания“. Настройка: <code>bash deploy/configure-mail.sh</code> на сървъра.
    </div>
@endif

{{-- Какво чака днес: всяка карта води направо към работата. --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <a href="{{ route('admin.inquiries.index', $unread ? ['filter' => 'unread'] : []) }}" class="rounded-lg bg-white p-5 shadow hover:ring-2 hover:ring-blue-200">
        <p class="text-sm font-medium text-gray-500">Непрочетени запитвания</p>
        <p class="mt-1 text-3xl font-bold {{ $unread ? 'text-blue-600' : 'text-gray-900' }}">{{ $unread }}</p>
        <p class="mt-1 text-xs text-gray-500">{{ $unread ? 'Отворете и отговорете' : 'Всичко е прочетено' }}</p>
    </a>
    <a href="{{ route('admin.outreach.calls.next') }}" class="rounded-lg bg-white p-5 shadow hover:ring-2 hover:ring-blue-200">
        <p class="text-sm font-medium text-gray-500">Обаждания днес</p>
        <p class="mt-1 text-3xl font-bold text-gray-900">{{ $calls['total'] }} <span class="text-base font-medium text-gray-400">/ {{ $calls['goal'] }}</span></p>
        <div class="mt-2 h-1.5 rounded-full bg-gray-100"><div class="h-full rounded-full bg-green-500" style="width: {{ min(100, round($calls['total'] / $calls['goal'] * 100)) }}%"></div></div>
        @if($callbacks)<p class="mt-1 text-xs font-semibold text-orange-600">{{ $callbacks }} за обаждане отново днес</p>@endif
    </a>
    <a href="{{ route('admin.outreach.index') }}" class="rounded-lg bg-white p-5 shadow hover:ring-2 hover:ring-blue-200">
        <p class="text-sm font-medium text-gray-500">Първи писма днес</p>
        <p class="mt-1 text-3xl font-bold text-gray-900">{{ $outreach['sent'] }} <span class="text-base font-medium text-gray-400">/ {{ $outreach['limit'] }}</span></p>
        <p class="mt-1 text-xs {{ $outreach['configured'] ? 'text-gray-500' : 'text-red-600' }}">{{ $outreach['configured'] ? 'Потенциални клиенти' : 'Пощата за писма не е настроена' }}</p>
        @if($outreach['clicked'])<p class="mt-1 text-xs font-semibold text-green-700">{{ $outreach['clicked'] }} кликнали за 7 дни</p>@endif
    </a>
    <a href="{{ route('admin.chats.index') }}" class="rounded-lg bg-white p-5 shadow hover:ring-2 hover:ring-blue-200">
        <p class="text-sm font-medium text-gray-500">Разговори с Крео</p>
        <p class="mt-1 text-3xl font-bold text-gray-900">{{ $chats }}</p>
        <p class="mt-1 text-xs text-gray-500">последните 7 дни</p>
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    <div class="lg:col-span-2 rounded-lg bg-white shadow">
        <div class="flex items-center justify-between border-b px-5 py-4">
            <h3 class="font-semibold text-gray-800">Последни запитвания</h3>
            <a href="{{ route('admin.inquiries.index') }}" class="text-sm text-blue-600 hover:underline">Всички</a>
        </div>
        <ul class="divide-y">
            @forelse($recentLeads as $lead)
                <li class="flex flex-col gap-1 px-5 py-3 sm:flex-row sm:items-center sm:justify-between">
                    <div class="min-w-0">
                        <a href="{{ route('admin.projects.show', $lead) }}" class="font-medium text-gray-900 hover:text-blue-600">{{ $lead->name }}</a>
                        @unless($lead->read_at)<span class="ml-1 rounded-full bg-blue-100 px-2 py-0.5 text-xs font-semibold text-blue-700">Ново</span>@endunless
                        <p class="truncate text-sm text-gray-500">{{ collect([$lead->serviceLabel() ? \Illuminate\Support\Str::limit($lead->serviceLabel(), 45) : null, $lead->lead_channel])->filter()->join(' · ') }}</p>
                    </div>
                    <div class="flex shrink-0 items-center gap-3 text-sm">
                        @if($lead->client_phone)<a href="tel:{{ preg_replace('/[^\d+]/', '', $lead->client_phone) }}" class="font-semibold text-blue-600 hover:underline">{{ $lead->client_phone }}</a>@endif
                        <span class="text-gray-400">{{ $lead->created_at->timezone('Europe/Sofia')->format('d.m H:i') }}</span>
                    </div>
                </li>
            @empty
                <li class="px-5 py-6 text-gray-500">Още няма запитвания от сайта.</li>
            @endforelse
        </ul>
    </div>

    <div class="space-y-6">
        <div class="rounded-lg bg-white shadow">
            <h3 class="border-b px-5 py-4 font-semibold text-gray-800">Консултации</h3>
            <ul class="divide-y">
                @forelse($consultations as $item)
                    @php $at = $item->consultation_at->timezone('Europe/Sofia'); @endphp
                    <li class="px-5 py-3">
                        <p class="text-sm font-semibold {{ $at->isToday() ? 'text-orange-600' : 'text-gray-900' }}">{{ $at->isToday() ? 'Днес' : ($at->isTomorrow() ? 'Утре' : $at->locale('bg')->isoFormat('dd, D MMM')) }}, {{ $at->format('H:i') }}</p>
                        <a href="{{ route('admin.projects.show', $item) }}" class="text-sm text-gray-700 hover:text-blue-600">{{ $item->name }}</a>
                        @if($item->client_phone)<a href="tel:{{ preg_replace('/[^\d+]/', '', $item->client_phone) }}" class="block text-sm text-blue-600 hover:underline">{{ $item->client_phone }}</a>@endif
                    </li>
                @empty
                    <li class="px-5 py-4 text-sm text-gray-500">Няма записани консултации за следващите 2 седмици.</li>
                @endforelse
            </ul>
        </div>

        <div class="rounded-lg bg-white p-5 shadow text-sm">
            <p class="text-gray-500">Проекти в работа</p>
            <a href="{{ route('admin.projects.index', ['filter' => 'active']) }}" class="text-2xl font-bold text-gray-900 hover:text-blue-600">{{ $activeProjects }}</a>
            <p class="mt-3 text-gray-500">Следваща статия в блога</p>
            @if($nextPost)
                <a href="{{ route('admin.posts.edit', $nextPost) }}" class="font-medium text-gray-900 hover:text-blue-600">{{ $nextPost->trOrBg('title') }}</a>
                <p class="text-gray-500">{{ $nextPost->published_at->timezone('Europe/Sofia')->format('d.m.Y H:i') }}</p>
            @else
                <p class="font-medium text-orange-600">Няма насрочена. <a href="{{ route('admin.posts.create') }}" class="text-blue-600 hover:underline">Напишете нова</a></p>
            @endif
        </div>
    </div>
</div>

<div class="rounded-lg bg-white p-5 shadow">
    <div class="flex items-center justify-between mb-4">
        <h3 class="font-semibold text-gray-800">Запитвания от сайта по канал</h3>
        <span class="text-sm text-gray-500">последните 30 дни</span>
    </div>
    @if($leadChannels->isEmpty())
        <p class="text-gray-500">Още няма запитвания от сайта през последните 30 дни.</p>
    @else
        @php $maxLeads = $leadChannels->max('total'); @endphp
        <div class="space-y-3">
            @foreach($leadChannels as $row)
                <div class="flex items-center gap-4">
                    <span class="w-28 sm:w-40 shrink-0 text-sm text-gray-700 truncate">{{ $row->channel }}</span>
                    <div class="flex-1 h-3 rounded-full bg-gray-100 overflow-hidden">
                        <div class="h-full rounded-full bg-brand-500" style="width: {{ max(4, round($row->total / $maxLeads * 100)) }}%"></div>
                    </div>
                    <span class="w-10 text-right text-sm font-semibold text-gray-900">{{ $row->total }}</span>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
