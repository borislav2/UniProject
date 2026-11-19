{{-- Maintenance page (php artisan down) and any other 503, design G. --}}
@php
    $en = request()->segment(1) === 'en';
    echo view('errors._g-standalone', [
        'code' => '503', 'en' => $en,
        'heading' => $en ? 'We are improving the site' : 'Правим сайта по-добър',
        'text' => $en ? 'The site is down for a short maintenance. Please try again in a moment.' : 'Сайтът е в кратка профилактика. Опитайте отново след малко.',
    ])->render();
@endphp
