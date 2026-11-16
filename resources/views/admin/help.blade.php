@extends('admin.layout')

@section('title', 'Как работи админът')

@section('content')
<div class="adm-page-head">
    <div>
        <h2>Как работи админът</h2>
        <p class="adm-eyebrow mt-1">Всеки раздел накратко, подреден като менюто</p>
    </div>
</div>

<p class="adm-help-intro">Тук е събрано какво прави всеки раздел, защо го има и какво да се прави в него. Същото обяснение се показва най-горе във всеки раздел под името „Какво е това?“. Менюто отляво зависи от вашия профил, но нищо не е заключено, другите раздели са в „Всички раздели“ и в търсенето (Ctrl и K).</p>

@foreach($sections as $group => $items)
    <section class="adm-help-group">
        <h3>{{ $group !== '' ? $group : 'Начало' }}</h3>
        <div class="adm-help-grid">
            @foreach($items as $item)
                <article class="adm-help-card">
                    <h4><a href="{{ route($item['route']) }}">{{ $item['label'] }}</a></h4>
                    <p class="what">{{ $item['help']['what'] }}</p>
                    <p><b>Защо е важно</b> {{ $item['help']['why'] }}</p>
                    <ol>
                        @foreach($item['help']['steps'] as $step)<li>{{ $step }}</li>@endforeach
                    </ol>
                </article>
            @endforeach
        </div>
    </section>
@endforeach
@endsection
