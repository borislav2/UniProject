@php($contact = config('creatium.contact'))
@php($legal = config('creatium.legal'))
@php($sender = $data['sender'])
@php($teamSignature = str_contains($sender, 'Creatium'))
Здравейте{!! filled($data['first_name'] ?? null) ? ', ' . $data['first_name'] : '' !!},

{!! $teamSignature ? 'Пишем ви от Creatium Lab.' : 'Казвам се ' . $sender . ' и съм от Creatium Lab.' !!} Изработваме и поддържаме сайтове за малки и средни бизнеси в България и се грижим да бъдат намирани в Google, в Google Maps и в AI асистентите.

@foreach($parts['intro'] as $paragraph)
{!! $paragraph !!}

@endforeach
@foreach($parts['items'] as $i => $item)
{{ $i + 1 }}. {!! $item['title'] !!}
@if($item['detail'] !== '')   {!! $item['detail'] !!}
@endif

@endforeach
@foreach($parts['outro'] as $paragraph)
{!! $paragraph !!}

@endforeach
@if($parts['items'])
Това са проблеми, които собственикът на сайта обикновено не забелязва, защото не се виждат при нормално отваряне. Те обаче влияят пряко на броя запитвания и обаждания. Повечето се отстраняват за няколко дни, без нов сайт.

Можете да видите пълния резултат и сами. Безплатната ни проверка изпраща подробен доклад на имейла ви за около минута:
{!! $links['check'] !!}
@else
Ако искате да видите техническия доклад за сайта си, безплатната ни проверка го изпраща на имейла ви за около минута:
{!! $links['check'] !!}
@endif

Повече за услугите ни, начина ни на работа и цените: {!! $links['site'] !!}

{!! $parts['items'] ? 'Ако желаете, ще ви предложа конкретен план за отстраняването им.' : 'Ако желаете, ще ви кажа какво конкретно бих направил за ' . $prospect->displayName() . '.' !!} Изберете удобен час за безплатен разговор ({{ $links['minutes'] }} минути) или просто отговорете на този имейл:
{!! $links['booking'] !!}

С уважение,
{!! $sender !!}
@unless($teamSignature)Creatium Lab
@endunless
@if(! empty($contact['phones'][0])){!! $contact['phones'][0] !!} · @endif{!! url('/') !!}

--
Пишем ви, защото адресът {!! $prospect->email !!} е публикуван като контакт на {!! $prospect->displayName() !!}.
Ако не искате да получавате писма от нас, отпишете се тук: {!! $unsubscribeUrl !!}
{!! $legal['company'] !!}@if($legal['eik']), ЕИК {!! $legal['eik'] !!}@endif @if($legal['address'])· {!! $legal['address'] !!}@endif
