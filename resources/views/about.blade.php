@extends('layouts.public')

@section('title', 'За нас')
@section('meta_description', 'Creatium Lab е малък екип от уеб разработчик и маркетолог, който помага на български бизнеси да се намират и избират онлайн.')

@section('content')
@include('partials/page-header', ['heading' => 'За нас', 'sub' => 'Малък екип, един отговорен човек за всяка задача.'])

<section class="py-16 bg-white">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6 text-lg text-gray-700">
        <p>Creatium Lab обединява два умения, които малкият бизнес рядко има под един покрив: <strong>уеб разработка</strong> и <strong>маркетинг</strong>.</p>
        <p>Повечето сайтове се правят от един човек, а рекламата се води от друг, и двамата не си говорят. При нас сайтът се прави така, че да работи с рекламата и да води запитвания, а не просто да „стои онлайн“.</p>
        <p>Работим директно с вас, без посредници и без неразбираем жаргон. Знаете с кого говорите, какво се прави и какво ще получите.</p>
    </div>
</section>

<section class="py-16 md:py-20 bg-brand-50/60">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-2xl md:text-3xl font-extrabold tracking-tight text-brand-950 text-center mb-10">Екипът</h2>
        <div class="grid md:grid-cols-2 gap-8">
            @foreach($team as $member)
                <div class="bg-white rounded-2xl border border-gray-200 p-7 card-hover reveal">
                    @if(!empty($member['name']))
                        <h3 class="text-xl font-bold">{{ $member['name'] }}</h3>
                        <p class="text-brand-600 font-semibold mb-3">{{ $member['role'] }}</p>
                    @else
                        <h3 class="text-xl font-bold text-brand-600 mb-3">{{ $member['role'] }}</h3>
                    @endif
                    <p class="text-gray-600">{{ $member['description'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

<section class="py-16 bg-white">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-2xl md:text-3xl font-extrabold tracking-tight text-brand-950 text-center mb-10">Как работим</h2>
        <div class="grid sm:grid-cols-3 gap-8 text-center">
            <div><i class="fas fa-comments text-brand-600 text-2xl mb-3"></i><h3 class="font-semibold mb-1">Ясна комуникация</h3><p class="text-sm text-gray-600">Говорим на разбираем език и винаги знаете на какъв етап сме.</p></div>
            <div><i class="fas fa-bullseye text-brand-600 text-2xl mb-3"></i><h3 class="font-semibold mb-1">Фокус върху резултат</h3><p class="text-sm text-gray-600">Мерим успеха по запитванията и клиентите, не по „красиви“ метрики.</p></div>
            <div><i class="fas fa-handshake text-brand-600 text-2xl mb-3"></i><h3 class="font-semibold mb-1">Дългосрочно партньорство</h3><p class="text-sm text-gray-600">Не изчезваме след пускането на сайта. Поддържаме го и го развиваме заедно с вас.</p></div>
        </div>
    </div>
</section>

@include('partials/cta')
@endsection
