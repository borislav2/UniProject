@php($contact = config('creatium.contact'))
@php($legal = config('creatium.legal'))
@php($sender = $data['sender'])
@php($teamSignature = str_contains($sender, 'Creatium'))
Hello{!! filled($data['first_name'] ?? null) ? ' ' . $data['first_name'] : '' !!},

{!! $teamSignature ? 'This is a short follow up to our email' : 'This is a short follow up to my email' !!} of {!! $data['first_date'] !!} about the website of {!! $prospect->displayName() !!}.

@if(filled($data['finding'] ?? null))
The most important point from the check was "{!! $data['finding'] !!}". It can usually be fixed within a few days, without a new website.

@endif
If this is relevant for you, pick a time for a short call ({{ $links['minutes'] }} minutes) or simply reply to this email. If it is not, {!! $teamSignature ? 'we will not write to you again about it' : 'I will not write to you again about it' !!}.
{!! $plain['booking'] !!}

Kind regards,
{!! $sender !!}
@unless($teamSignature)Creatium Lab
@endunless
@if(! empty($contact['phones'][0])){!! $contact['phones'][0] !!} · @endif{!! url('/') !!}

--
We are writing because {!! $prospect->email !!} is published as a contact for {!! $prospect->displayName() !!}.
If you would rather not hear from us, you can unsubscribe at {!! $unsubscribeUrl !!} and we will not write again. You can read how we handle data in our privacy policy at {!! route('en.privacy') !!}
{!! $legal['company'] !!}@if($legal['eik']), company ID (EIK) {!! $legal['eik'] !!}@endif @if($legal['address'])· {!! $legal['address'] !!}@endif
