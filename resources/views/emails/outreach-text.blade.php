@php($contact = config('creatium.contact'))
@php($legal = config('creatium.legal'))
Здравейте{!! filled($data['first_name'] ?? null) ? ', ' . $data['first_name'] : '' !!},

{!! $data['observation'] !!}

Ние сме Creatium Lab: правим сайтове и маркетинг за малки бизнеси в България. Подобни неща обикновено се оправят бързо, а разликата се усеща в запитванията.

Ако ви е интересно, ще ви изпратим кратък безплатен преглед на сайта с няколко конкретни неща за подобряване. Да ви го пратим ли?

Поздрави,
{!! $data['sender'] !!}
Creatium Lab · {!! url('/') !!}@if(! empty($contact['phones'][0])) · {!! $contact['phones'][0] !!}@endif

--
Пишем ви, защото адресът {!! $prospect->email !!} е публикуван на сайта на {!! $prospect->displayName() !!}.
Ако не искате да получавате писма от нас, отпишете се тук: {!! $unsubscribeUrl !!}
{!! $legal['company'] !!}@if($legal['eik']), ЕИК {!! $legal['eik'] !!}@endif @if($legal['address'])· {!! $legal['address'] !!}@endif
