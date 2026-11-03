@extends('admin.layout')

@section('title', 'Табло')

@section('content')
@if($mailNotConfigured)
    <div class="mb-6 rounded-lg border border-yellow-300 bg-yellow-50 px-4 py-3 text-sm text-yellow-900">
        <strong>Запитванията не идват по имейл.</strong> Сървърът е с <code>MAIL_MAILER={{ config('mail.default') }}</code>, затова ги гледайте тук, в „Запитвания“. Настройка: <code>bash deploy/configure-mail.sh</code> на сървъра.
    </div>
@endif

@if($overdue)
    <a href="{{ route('admin.today') }}" class="mb-6 flex items-center justify-between rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-800 hover:bg-red-100">
        <span>{{ $overdue }} {{ $overdue === 1 ? 'запитване е' : 'запитвания са' }} с просрочена следваща стъпка</span><span>Към „Днес“ →</span>
    </a>
@endif

{{-- Какво чака днес: всяка карта води направо към работата. --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="rounded-lg bg-white p-5 shadow">
        <p class="text-sm font-medium text-gray-500">Непрочетени</p>
        <div class="mt-1 flex items-baseline gap-4">
            <a href="{{ route('admin.inquiries.index', $unread ? ['filter' => 'unread'] : []) }}" class="hover:opacity-80"><span class="text-3xl font-bold {{ $unread ? 'text-blue-600' : 'text-gray-900' }}">{{ $unread }}</span> <span class="text-xs text-gray-500">запитвания</span></a>
            <a href="{{ route('admin.site-checks.index', $unreadChecks ? ['filter' => 'unread'] : []) }}" class="hover:opacity-80"><span class="text-3xl font-bold {{ $unreadChecks ? 'text-amber-600' : 'text-gray-900' }}">{{ $unreadChecks }}</span> <span class="text-xs text-gray-500">проверки</span></a>
        </div>
        <p class="mt-1 text-xs text-gray-500">{{ $unread || $unreadChecks ? 'Отворете и отговорете' : 'Всичко е прочетено' }}</p>
    </div>
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

{{-- Продажбите: този месец спрямо миналия (до същия ден), етапите и колко бързо отговаряме. --}}
@php
    $trend = function (string $key) use ($months) {
        $now = $months['this'][$key]; $before = $months['last'][$key];
        if ($now === $before) return ['=', 'text-gray-400', 'колкото миналия месец'];
        return $now > $before ? ['↑', 'text-green-600', '+' . ($now - $before) . ' спрямо миналия месец'] : ['↓', 'text-red-600', ($now - $before) . ' спрямо миналия месец'];
    };
    $closed = $months['this']['won'] + $months['this']['lost'];
@endphp
<section class="mb-6 rounded-lg bg-white p-5 shadow">
    <div class="mb-4 flex flex-wrap items-baseline justify-between gap-2">
        <h3 class="font-semibold text-gray-800">Продажбите този месец</h3>
        <a href="{{ route('admin.stats') }}" class="text-sm text-blue-600 hover:underline">Подробна статистика</a>
    </div>
    <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6">
        @foreach([['leads', 'Нови запитвания'], ['contacted', 'Свързахме се'], ['offer', 'Изпратени оферти'], ['won', 'Спечелени'], ['lost', 'Загубени']] as [$key, $label])
            @php [$arrow, $color, $hint] = $trend($key); @endphp
            <div title="{{ $hint }}">
                <p class="text-sm text-gray-500">{{ $label }}</p>
                <p class="text-2xl font-bold text-gray-900">{{ $months['this'][$key] }} <span class="text-sm font-semibold {{ $color }}">{{ $arrow }}</span></p>
                <p class="text-xs text-gray-400">миналия: {{ $months['last'][$key] }}</p>
            </div>
        @endforeach
        <div>
            <p class="text-sm text-gray-500">Успеваемост</p>
            <p class="text-2xl font-bold text-gray-900">{{ $closed ? round($months['this']['won'] / $closed * 100) . '%' : '–' }}</p>
            <p class="text-xs text-gray-400">спечелени от приключените</p>
        </div>
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        <div>
            <p class="mb-2 text-sm font-semibold text-gray-700">Отворени запитвания сега</p>
            @php $openTotal = array_sum($pipeline['stages']); @endphp
            <div class="flex h-4 overflow-hidden rounded-full bg-gray-100">
                @foreach(['new' => 'bg-blue-500', 'contacted' => 'bg-indigo-500', 'offer' => 'bg-purple-500'] as $stage => $color)
                    @if($pipeline['stages'][$stage])<div class="{{ $color }}" style="width: {{ $pipeline['stages'][$stage] / max(1, $openTotal) * 100 }}%"></div>@endif
                @endforeach
            </div>
            <div class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-sm">
                @foreach(['new' => 'bg-blue-500', 'contacted' => 'bg-indigo-500', 'offer' => 'bg-purple-500'] as $stage => $color)
                    <a href="{{ route('admin.inquiries.index', ['tab' => $stage]) }}" class="inline-flex items-center gap-1.5 hover:text-blue-600"><span class="h-2.5 w-2.5 rounded-full {{ $color }}"></span>{{ \App\Models\Project::STAGES[$stage][0] }}: <strong>{{ $pipeline['stages'][$stage] }}</strong></a>
                @endforeach
            </div>
            <p class="mt-2 text-sm {{ $pipeline['no_next_step'] || $pipeline['unassigned'] ? 'text-orange-700' : 'text-gray-500' }}">
                {{ $pipeline['no_next_step'] }} без следваща стъпка · {{ $pipeline['unassigned'] }} без човек
            </p>
        </div>
        <div>
            <p class="mb-2 text-sm font-semibold text-gray-700">Колко бързо отговаряме <span class="font-normal text-gray-400">(последните 30 дни)</span></p>
            <p class="text-2xl font-bold {{ $response['average_hours'] !== null && $response['average_hours'] > 24 ? 'text-red-600' : 'text-gray-900' }}">
                @if($response['average_hours'] === null) – @elseif($response['average_hours'] < 1) под 1 ч @else {{ str_replace('.', ',', $response['average_hours']) }} ч @endif
                <span class="text-sm font-normal text-gray-500">средно до първия разговор или имейл</span>
            </p>
            <p class="mt-1 text-sm text-gray-600">{{ $response['within_hour'] }} от {{ $response['answered'] }} в рамките на час@if($response['waiting']) · <span class="font-semibold text-orange-700">{{ $response['waiting'] }} без записан отговор</span>@endif</p>
            <p class="mt-3 mb-1 text-sm font-semibold text-gray-700">По вид <span class="font-normal text-gray-400">(30 дни)</span></p>
            <div class="flex flex-wrap gap-2 text-xs">
                @foreach(\App\Models\Project::LEAD_KINDS as $key => [$label, $class])
                    <span class="rounded-full px-2.5 py-1 font-semibold {{ $class }}">{{ $label }}: {{ $kinds[$key] }}</span>
                @endforeach
            </div>
        </div>
    </div>
</section>

{{-- Екипът: кой какво води и какво е свършил тази седмица. --}}
<section class="mb-6 overflow-hidden rounded-lg bg-white shadow">
    <div class="flex flex-wrap items-baseline justify-between gap-2 border-b px-5 py-4">
        <h3 class="font-semibold text-gray-800">Екипът</h3>
        <span class="text-sm text-gray-500">води сега · свършено от понеделник</span>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500">
                <tr>
                    <th class="px-5 py-2">Човек</th>
                    <th class="px-3 py-2 text-right">Води</th>
                    <th class="px-3 py-2 text-right">Нови</th>
                    <th class="px-3 py-2 text-right">Просрочени</th>
                    <th class="px-3 py-2 text-right">Обаждания</th>
                    <th class="px-3 py-2 text-right">Имейли</th>
                    <th class="px-3 py-2 text-right">Писма до бизнеси</th>
                    <th class="px-3 py-2 text-right">LinkedIn</th>
                    <th class="px-3 py-2 text-right">Етапи</th>
                    <th class="px-3 py-2 text-right">Спечелени (месец)</th>
                    <th class="px-5 py-2 text-right">Днес</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @foreach($team as $row)
                    <tr class="hover:bg-gray-50">
                        <td class="px-5 py-2.5 font-semibold"><a href="{{ route('admin.today', ['user' => $row['user']->id]) }}" class="text-gray-900 hover:text-blue-600">{{ $row['user']->name }}</a></td>
                        <td class="px-3 py-2.5 text-right">{{ $row['work']['open'] }}</td>
                        <td class="px-3 py-2.5 text-right {{ $row['work']['new'] ? 'font-semibold text-blue-600' : 'text-gray-400' }}">{{ $row['work']['new'] }}</td>
                        <td class="px-3 py-2.5 text-right {{ $row['work']['overdue'] ? 'font-semibold text-red-600' : 'text-gray-400' }}">{{ $row['work']['overdue'] }}</td>
                        <td class="px-3 py-2.5 text-right">{{ $row['week']['calls'] }}</td>
                        <td class="px-3 py-2.5 text-right">{{ $row['week']['emails'] }}</td>
                        <td class="px-3 py-2.5 text-right">{{ $row['week']['outreach'] }}</td>
                        <td class="px-3 py-2.5 text-right" title="покани / съобщения">{{ $row['week']['linkedin_invites'] }} / {{ $row['week']['linkedin_messages'] }}</td>
                        <td class="px-3 py-2.5 text-right">{{ $row['week']['stages'] }}</td>
                        <td class="px-3 py-2.5 text-right {{ $row['work']['won_month'] ? 'font-semibold text-green-700' : 'text-gray-400' }}">{{ $row['work']['won_month'] }}</td>
                        <td class="px-5 py-2.5 text-right"><a href="{{ route('admin.today', ['user' => $row['user']->id]) }}" class="text-blue-600 hover:underline">{{ $row['today'] }} действия</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if($pipeline['unassigned'])
        <p class="border-t bg-orange-50 px-5 py-2.5 text-sm text-orange-800">{{ $pipeline['unassigned'] }} отворени запитвания нямат човек. <a href="{{ route('admin.inquiries.index', ['who' => 'none']) }}" class="font-semibold underline">Разпределете ги</a></p>
    @endif
</section>

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
                        @unless($lead->read_at)<span class="ml-1 rounded-full bg-blue-600 px-2 py-0.5 text-xs font-semibold text-white">Ново</span>@endunless
                        @if($lead->leadKind())<span class="ml-1 rounded-full px-2 py-0.5 text-xs font-semibold {{ $lead->leadKindClass() }}">{{ $lead->leadKindLabel() }}</span>@endif
                        @if($lead->assignee)<span class="ml-1 text-xs text-gray-500">· {{ $lead->assignee->name }}</span>@endif
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
            <p class="mt-3 text-gray-500">Месечни приходи от абонаменти</p>
            <a href="{{ route('admin.clients.index') }}" class="text-2xl font-bold text-gray-900 hover:text-blue-600">{{ number_format((float) $mrr, 0, ',', ' ') }} €</a>
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
