{{-- Forbidden, design G. --}}
@php
    $en = request()->segment(1) === 'en';
    echo view('errors._g-standalone', [
        'code' => '403', 'en' => $en,
        'heading' => $en ? 'You do not have access to this page' : 'Нямате достъп до тази страница',
        'text' => $en ? 'If you think this is a mistake, email us at' : 'Ако мислите, че това е грешка, ни пишете на',
    ])->render();
@endphp
