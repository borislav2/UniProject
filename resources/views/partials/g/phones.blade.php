{{-- Phones as text, with call, Viber and WhatsApp links. $phones: list of numbers. --}}
<ul class="g-phones">
    @foreach($phones as $phone)
        <li>
            <a class="g-phone-num" href="{{ \App\Support\Phone::tel($phone) }}">{{ $phone }}</a>
            <span class="g-phone-apps g-mono">
                <a href="{{ \App\Support\Phone::tel($phone) }}">{{ __('Обаждане') }}</a>
                @if($viber = \App\Support\Phone::viber($phone))<a href="{{ $viber }}">Viber</a>@endif
                @if($whatsapp = \App\Support\Phone::whatsapp($phone))<a href="{{ $whatsapp }}" target="_blank" rel="noopener">WhatsApp</a>@endif
            </span>
        </li>
    @endforeach
</ul>
