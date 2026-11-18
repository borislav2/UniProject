{{--
    Личният преглед към безплатната проверка на сайт ($inquiry = Project): срок, просрочие в червено, основната цел на човека и бутон „Изпрати личния преглед“
    (екранът за писмо с шаблон site-review, предварително попълнен от отчета). Показва се само за проверки със срок (новите от формата в дизайн Г и старите, които имат такъв).
--}}
@if($inquiry->review_due_at)
    @php
        $reviewState = $inquiry->reviewState();
        $tone = ['done' => 'border-green-300 bg-green-50 text-green-900', 'overdue' => 'border-red-300 bg-red-50 text-red-800', 'due' => 'border-amber-300 bg-amber-50 text-amber-900'][$reviewState];
        $goalLabel = \App\Support\SiteCheck\ReviewDue::goalLabel($inquiry->check_goal);
    @endphp
    <div class="mt-4 flex flex-wrap items-center justify-between gap-3 rounded-lg border px-4 py-3 text-sm {{ $tone }}" data-review="{{ $reviewState }}">
        <div class="min-w-0">
            @if($reviewState === 'done')
                <p class="font-semibold">Личният преглед е изпратен на {{ $inquiry->reviewed_at->timezone('Europe/Sofia')->format('d.m.Y H:i') }}</p>
            @else
                <p class="font-semibold">Личен преглед до {{ \App\Support\SiteCheck\ReviewDue::label($inquiry->review_due_at) }}@if($reviewState === 'overdue') · просрочен@endif</p>
                <p class="text-xs opacity-80">Обещахме на клиента личен преглед от специалист до два работни дни.</p>
            @endif
            @if($goalLabel)<p class="mt-1 text-xs">Основна цел на клиента <strong>{{ $goalLabel }}</strong></p>@endif
        </div>
        <a href="{{ route('admin.client-emails.create', ['template' => 'site-review', 'project' => $inquiry->id]) }}" class="adm-btn {{ $reviewState === 'done' ? 'ghost' : '' }} sm"><x-icon name="paper-plane" class="text-xs" /> {{ $reviewState === 'done' ? 'Изпрати нов преглед' : 'Изпрати личния преглед' }}</a>
    </div>
@endif
