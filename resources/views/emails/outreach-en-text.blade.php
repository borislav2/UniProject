@php($contact = config('creatium.contact'))
@php($legal = config('creatium.legal'))
@php($sender = $data['sender'])
@php($teamSignature = str_contains($sender, 'Creatium'))
Hello{!! filled($data['first_name'] ?? null) ? ' ' . $data['first_name'] : '' !!},

{!! $teamSignature ? 'We are Creatium Lab, a small digital agency.' : 'My name is ' . $sender . ' and I am with Creatium Lab, a small digital agency.' !!} We build and look after websites for small businesses, and we help them get found on Google, on Google Maps and in AI assistants.

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
These are things a business owner rarely notices, because they do not show when you open your own site. They do affect how many enquiries and calls come in, and most can be fixed within a few days without a new website.

You can see the full result yourself. Our free website check emails a detailed report in about a minute. You can try it at
{!! $plain['check'] !!}
@else
If you would like a technical report for your own site, our free website check emails one in about a minute. You can try it at
{!! $plain['check'] !!}
@endif

You can read about our services and how we work at {!! $plain['site'] !!}

{!! $parts['items'] ? ($teamSignature ? 'If it would be useful, we can suggest a concrete plan for fixing them.' : 'If it would be useful, I can suggest a concrete plan for fixing them.') : ($teamSignature ? 'If you like, we can tell you what we would do for ' . $prospect->displayName() . '.' : 'If you like, I can tell you what I would do for ' . $prospect->displayName() . '.') !!} Pick a time for a short call ({{ $links['minutes'] }} minutes) or simply reply to this email. The available times are at
{!! $plain['booking'] !!}

Kind regards,
{!! $sender !!}
@unless($teamSignature)Creatium Lab
@endunless
@if(! empty($contact['phones'][0])){!! $contact['phones'][0] !!} · @endif{!! url('/') !!}

--
We are writing because {!! $prospect->email !!} is published as a contact for {!! $prospect->displayName() !!}. We found the business on Google Maps and on its public website, and we think a short note about its website may be useful to you, which is our legitimate interest as a business writing to another business.
If you would rather not hear from us, you can unsubscribe at {!! $unsubscribeUrl !!} and we will not write again. You can read how we handle data in our privacy policy at {!! route('en.privacy') !!}
{!! $legal['company'] !!}@if($legal['eik']), company ID (EIK) {!! $legal['eik'] !!}@endif @if($legal['address'])· {!! $legal['address'] !!}@endif
