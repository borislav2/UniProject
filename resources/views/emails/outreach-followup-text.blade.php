@php($contact = config('creatium.contact'))
@php($legal = config('creatium.legal'))
@php($sender = $data['sender'])
@php($teamSignature = str_contains($sender, 'Creatium'))
Здравейте{!! filled($data['first_name'] ?? null) ? ', ' . $data['first_name'] : '' !!},

{!! $teamSignature ? 'Пишем ви отново' : 'Пиша ви отново' !!} накратко във връзка с писмото от {!! $data['first_date'] !!} за сайта на {!! $prospect->displayName() !!}.

@if(filled($data['finding'] ?? null))
Най-важното от проверката беше: {!! $data['finding'] !!}. Обикновено се оправя за няколко дни, без нов сайт.

@endif
Ако темата е актуална за вас, изберете удобен час за кратък разговор ({{ $links['minutes'] }} минути) или просто отговорете на този имейл. Ако не е, няма да {!! $teamSignature ? 'ви пишем' : 'ви пиша' !!} повече по нея.
{!! $plain['booking'] !!}

С уважение,
{!! $sender !!}
@unless($teamSignature)Creatium Lab
@endunless
@if(! empty($contact['phones'][0])){!! $contact['phones'][0] !!} · @endif{!! url('/') !!}

--
Пишем ви, защото адресът {!! $prospect->email !!} е публикуван като контакт на {!! $prospect->displayName() !!}.
Ако не искате да получавате писма от нас, отпишете се тук: {!! $unsubscribeUrl !!}
{!! $legal['company'] !!}@if($legal['eik']), ЕИК {!! $legal['eik'] !!}@endif @if($legal['address'])· {!! $legal['address'] !!}@endif
