<nav class="mb-6 flex gap-1 border-b border-gray-200 text-sm font-semibold" aria-label="Потенциални клиенти">
    @foreach(['admin.outreach.index' => ['С имейл', 'envelope'], 'admin.outreach.calls.index' => ['Без сайт (за обаждане)', 'phone']] as $route => [$label, $icon])
        <a href="{{ route($route) }}" class="-mb-px inline-flex items-center gap-2 border-b-2 px-4 py-2 {{ request()->routeIs($route) ? 'border-brand-950 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-800' }}" @if(request()->routeIs($route)) aria-current="page" @endif><x-icon :name="$icon" /> {{ $label }}</a>
    @endforeach
</nav>
