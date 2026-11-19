{{-- Confirmation after the one-click unsubscribe of an outreach email (shared by both designs, always in the G style: it is the recipient's first sight of us). --}}
@php
    $english = $prospect->mailLanguage() === 'en';
    echo view('errors._g-standalone', [
        'code' => '', 'en' => $english, 'links' => true,
        'heading' => $english ? 'You are unsubscribed' : 'Отписахте се',
        'text' => $english
            ? 'The address ' . $prospect->email . ' has been removed and will not receive any more emails from Creatium Lab. Sorry to have bothered you.'
            : 'Адресът ' . $prospect->email . ' е премахнат и няма да получава повече писма от Creatium Lab. Извинете за безпокойството.',
    ])->render();
@endphp
