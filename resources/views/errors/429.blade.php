{{-- Too many requests, design G. --}}
@php
    $en = request()->segment(1) === 'en';
    echo view('errors._g-standalone', [
        'code' => '429', 'en' => $en,
        'heading' => $en ? 'Too many requests' : 'Твърде много заявки',
        'text' => $en ? 'Please try again in a minute. If it is urgent, email us at' : 'Опитайте отново след минута. Ако е спешно, ни пишете на',
    ])->render();
@endphp
