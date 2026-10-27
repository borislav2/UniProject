{{-- Телефон с бутон за обаждане и бързи връзки към Viber и WhatsApp (в България много бизнеси отговарят по-бързо там). --}}
@props(['number', 'button' => false])
@if($number)
    <span {{ $attributes->merge(['class' => 'inline-flex flex-wrap items-center gap-1.5']) }}>
        <a href="{{ \App\Support\Phone::tel($number) }}" class="{{ $button ? 'rounded-lg bg-green-600 px-3 py-1.5 font-semibold text-white hover:bg-green-700' : 'font-semibold text-blue-600 hover:underline' }}">{{ $number }}</a>
        <a href="{{ \App\Support\Phone::viber($number) }}" class="rounded-md bg-purple-100 px-1.5 py-0.5 text-xs font-semibold text-purple-800 hover:bg-purple-200" title="Пиши във Viber">Viber</a>
        <a href="{{ \App\Support\Phone::whatsapp($number) }}" target="_blank" rel="noopener" class="rounded-md bg-emerald-100 px-1.5 py-0.5 text-xs font-semibold text-emerald-800 hover:bg-emerald-200" title="Пиши в WhatsApp">WhatsApp</a>
    </span>
@endif
