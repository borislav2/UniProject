{{-- Скриптът за първото обаждане (одобрен от собствениците, октомври 2026). Не се чете дума по дума; целта е имейл за безплатния преглед или час за разговор. --}}
@php
    $kind = mb_strtolower($call->industryLabel() ?? 'бизнес');
    $city = $call->city ?: 'града ви';
    $me = auth()->user()?->name ?? 'Борислав';
@endphp
<div class="rounded-lg bg-white p-6 text-sm leading-relaxed text-gray-800 shadow">
    <h3 class="mb-4 text-base font-bold text-gray-900">Скрипт за разговора</h3>

    <ol class="space-y-4">
        <li>
            <p class="font-semibold text-gray-900">1. Представяне (10 секунди)</p>
            <p class="mt-1 rounded bg-gray-50 p-3">„Здравейте, казвам се {{ $me }}, от Creatium Lab. Обаждам се на {{ $call->name }}, правилно ли съм попаднал? Удобно ли ви е за минута?“</p>
            <p class="mt-1 text-xs text-gray-500">Ако не е удобно: „Кога да ви се обадя?“ и запишете датата отляво.</p>
        </li>
        <li>
            <p class="font-semibold text-gray-900">2. Защо се обаждате</p>
            <p class="mt-1 rounded bg-gray-50 p-3">„Търсих {{ $kind }} в {{ $city }} в Google и видях, че нямате сайт. Повечето хора днес първо търсят в Google, преди да се обадят, и отиват при тези, които намерят там.“</p>
        </li>
        <li>
            <p class="font-semibold text-gray-900">3. Кои сме (едно изречение)</p>
            <p class="mt-1 rounded bg-gray-50 p-3">„Изработваме и поддържаме сайтове за малки бизнеси в България и се грижим да ги намират в Google и Google Maps.“</p>
        </li>
        <li>
            <p class="font-semibold text-gray-900">4. Молбата (малка и безплатна)</p>
            <p class="mt-1 rounded bg-gray-50 p-3">„Мога да ви изпратя безплатно кратък преглед какво бих направил за {{ $call->name }}, без ангажимент. На кой имейл да го пратя?“</p>
            <p class="mt-1 text-xs text-gray-500">Или: „Ако искате, да си запазим 30 минути за разговор, когато ви е удобно.“ Имейла запишете в бележката.</p>
        </li>
        <li>
            <p class="font-semibold text-gray-900">5. Край</p>
            <p class="mt-1 rounded bg-gray-50 p-3">„Благодаря ви. Ще ви изпратя прегледа днес.“</p>
        </li>
    </ol>

    <h4 class="mb-2 mt-6 font-bold text-gray-900">Чести възражения</h4>
    <dl class="space-y-2">
        <div><dt class="font-semibold">„Нямаме нужда, имаме клиенти.“</dt><dd class="text-gray-600">„Чудесно. Сайтът е за дните, в които искате повече. Мога да ви пратя прегледа само за информация.“</dd></div>
        <div><dt class="font-semibold">„Скъпо е.“</dt><dd class="text-gray-600">„Започваме от €450 за сайт. Прегледът е безплатен и ще видите дали изобщо си струва.“</dd></div>
        <div><dt class="font-semibold">„Имаме човек за това.“</dt><dd class="text-gray-600">„Добре, прегледът може да е полезен и на него.“</dd></div>
        <div><dt class="font-semibold">„Не се интересувам.“</dt><dd class="text-gray-600">„Разбирам, благодаря за времето.“ Отбележете „Без интерес“: няма да излезе пак.</dd></div>
    </dl>
</div>
