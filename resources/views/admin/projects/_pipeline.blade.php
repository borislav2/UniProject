{{--
    Етап на продажбата и следваща стъпка за едно запитване ($project). Ползва се в „Запитвания“, „Днес“ и на страницата на проекта.
    Бутоните „Утре / След 3 дни / След седмица“ попълват датата; „Спечелен“ и „Загубен“ затварят запитването.
--}}
@php
    $nextAt = old('next_action_at', $project->next_action_at?->timezone('Europe/Sofia')->format('Y-m-d\TH:i'));
@endphp
<form action="{{ route('admin.projects.pipeline', $project) }}" method="POST" class="rounded-lg border border-gray-200 bg-gray-50 p-3 text-sm" data-pipeline>
    @csrf
    @method('PATCH')
    <div class="flex flex-wrap gap-1.5" role="radiogroup" aria-label="Етап">
        @foreach(\App\Models\Project::STAGES as $key => [$label])
            <label class="cursor-pointer">
                <input type="radio" name="stage" value="{{ $key }}" class="peer sr-only" @checked(($project->stage ?? 'new') === $key)>
                <span class="inline-block rounded-full border border-gray-300 bg-white px-3 py-1 font-semibold text-gray-700 peer-checked:border-brand-950 peer-checked:bg-brand-950 peer-checked:text-white peer-focus-visible:ring-2 peer-focus-visible:ring-brand-400">{{ $label }}</span>
            </label>
        @endforeach
    </div>

    <div class="mt-3 grid gap-2 sm:grid-cols-[1fr_auto]" data-next>
        <input type="text" name="next_action" value="{{ old('next_action', $project->next_action) }}" maxlength="255" placeholder="Следваща стъпка: напр. „обади се за офертата“" class="w-full rounded-lg border border-gray-300 px-3 py-2">
        <input type="datetime-local" name="next_action_at" value="{{ $nextAt }}" class="rounded-lg border border-gray-300 px-3 py-2" aria-label="Кога">
    </div>
    <div class="mt-2 flex flex-wrap items-center gap-2" data-next>
        <span class="text-xs text-gray-500">Кога:</span>
        @foreach(['Утре' => 1, 'След 3 дни' => 3, 'След седмица' => 7] as $label => $days)
            <button type="button" class="rounded-full border border-gray-300 bg-white px-2.5 py-0.5 text-xs hover:bg-gray-100" data-days="{{ $days }}">{{ $label }}</button>
        @endforeach
    </div>
    <textarea name="note" rows="2" maxlength="2000" placeholder="Бележка от разговора (по желание): какво каза, какво иска, бюджет…" class="mt-2 w-full rounded-lg border border-gray-300 px-3 py-2">{{ old('note') }}</textarea>
    <input type="text" name="lost_reason" value="{{ old('lost_reason', $project->lost_reason) }}" maxlength="255" placeholder="Защо загубихме? (цена, избра друг, не отговаря…)" class="mt-2 w-full rounded-lg border border-gray-300 px-3 py-2 {{ $project->stage === 'lost' ? '' : 'hidden' }}" data-lost>

    <div class="mt-3 flex flex-wrap items-center justify-between gap-2">
        @if($project->stage_changed_at)
            <span class="text-xs text-gray-500">Етапът е сменен {{ $project->stage_changed_at->timezone('Europe/Sofia')->format('d.m.Y H:i') }}</span>
        @else
            <span></span>
        @endif
        <button type="submit" class="rounded-lg bg-gray-800 px-4 py-1.5 font-semibold text-white hover:bg-gray-700">Запази</button>
    </div>
</form>

@once
    @push('scripts')
        <script>
            document.querySelectorAll('[data-pipeline]').forEach(function (form) {
                var date = form.querySelector('[name=next_action_at]');
                var lost = form.querySelector('[data-lost]');
                function pad(n) { return String(n).padStart(2, '0'); }
                form.querySelectorAll('[data-days]').forEach(function (button) {
                    button.addEventListener('click', function () {
                        var d = new Date();
                        d.setDate(d.getDate() + Number(button.dataset.days));
                        if (d.getDay() === 6) d.setDate(d.getDate() + 2);
                        if (d.getDay() === 0) d.setDate(d.getDate() + 1);
                        date.value = d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate()) + 'T10:00';
                    });
                });
                function sync() {
                    var stage = (form.querySelector('[name=stage]:checked') || {}).value;
                    var closed = stage === 'won' || stage === 'lost';
                    form.querySelectorAll('[data-next]').forEach(function (el) { el.classList.toggle('hidden', closed); });
                    lost.classList.toggle('hidden', stage !== 'lost');
                }
                form.addEventListener('change', sync);
                sync();
            });
        </script>
    @endpush
@endonce
