{{-- Software service, visuals of the brief. $slot = hero (one core with four modules), v2 (before and after automation). $v = g_vis[$slot]. --}}
@switch($slot)
    @case('hero')
        @include('partials.g.svc.hub', ['v' => $v])
        @break
    @case('v2')
        @include('partials.g.svc.cmp', ['v' => $v])
        @break
@endswitch
