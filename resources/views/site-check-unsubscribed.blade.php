{{-- Потвърждение след отписване от писмата след безплатната проверка (общо за двата дизайна, винаги в стила на Г). --}}
@php
    $english = $project->locale === 'en';
    echo view('errors._g-standalone', [
        'code' => '', 'en' => $english, 'links' => true,
        'heading' => $english ? 'You are unsubscribed' : 'Отписахте се',
        'text' => $english
            ? 'The address ' . $project->client_email . ' will not receive any more emails about the website check. Sorry to have bothered you.'
            : 'Адресът ' . $project->client_email . ' няма да получава повече писма за проверката на сайта. Извинете за безпокойството.',
    ])->render();
@endphp
