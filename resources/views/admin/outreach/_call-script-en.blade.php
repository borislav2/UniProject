{{-- Short call script in English for businesses in the UK, Ireland and the Netherlands. Not read word for word; the goal is the right person's email for the free website check, or a booked call. --}}
@php
    $kind = mb_strtolower($call->industryLabel() ?? 'business');
    $city = $call->city ?: 'your area';
    $me = auth()->user()?->name ?? 'Borislav';
    $finding = preg_match('/^Finding (.+)$/mu', (string) $call->note, $m) ? rtrim($m[1], '.') : null;
@endphp
<div class="rounded-lg bg-white p-6 text-sm leading-relaxed text-gray-800 shadow" lang="en">
    <h3 class="mb-4 text-base font-bold text-gray-900">Call script in English</h3>

    <ol class="space-y-4">
        <li>
            <p class="font-semibold text-gray-900">1. Opening (10 seconds)</p>
            <p class="mt-1 rounded bg-gray-50 p-3">"Hello, is that {{ $call->name }}? My name is {{ $me }}, I am calling from Creatium Lab. Do you have a minute?"</p>
            <p class="mt-1 text-xs text-gray-500">If it is not a good time, ask "When would be better for me to call back?" and write the date on the left.</p>
        </li>
        <li>
            <p class="font-semibold text-gray-900">2. One finding</p>
            @if($finding)
                <p class="mt-1 rounded bg-gray-50 p-3">"I looked at your website and noticed one thing. {{ $finding }}. It is usually quick to fix."</p>
            @else
                <p class="mt-1 rounded bg-gray-50 p-3">"I was looking at {{ $kind }} businesses in {{ $city }} on Google and I could not find a website for you. Most people now check Google before they call, and they go to whoever they find there."</p>
                <p class="mt-1 text-xs text-gray-500">If they have a website, open it first and name one real thing you see, for example that it is hard to use on a phone or that it has no description in Google.</p>
            @endif
        </li>
        <li>
            <p class="font-semibold text-gray-900">3. Who we are (one sentence)</p>
            <p class="mt-1 rounded bg-gray-50 p-3">"We are a small agency. We build and look after websites for small businesses and help them show up on Google and Google Maps."</p>
        </li>
        <li>
            <p class="font-semibold text-gray-900">4. The offer (small and free)</p>
            <p class="mt-1 rounded bg-gray-50 p-3">"I can send you a short free website check that shows what could be improved, with no obligation. Which email address should I send it to?"</p>
            <p class="mt-1 text-xs text-gray-500">Or "We could set up a 30 minute call whenever suits you." Write the email address in the note.</p>
        </li>
        <li>
            <p class="font-semibold text-gray-900">5. The right person</p>
            <p class="mt-1 rounded bg-gray-50 p-3">"Who looks after the website for you, and what is the best email address for them?"</p>
        </li>
        <li>
            <p class="font-semibold text-gray-900">6. Close</p>
            <p class="mt-1 rounded bg-gray-50 p-3">"Thank you for your time. I will send it over today."</p>
        </li>
    </ol>

    <h4 class="mb-2 mt-6 font-bold text-gray-900">Common answers</h4>
    <dl class="space-y-2">
        <div><dt class="font-semibold">"We are fine, we have enough customers."</dt><dd class="text-gray-600">"Good to hear. A website is for the days when you want more. I can send the check for information only."</dd></div>
        <div><dt class="font-semibold">"How much does it cost?"</dt><dd class="text-gray-600">"The check is free. Building a website starts from 400 euros, including the first month of care, then 200 euros a month. The check will show whether it is worth it for you."</dd></div>
        <div><dt class="font-semibold">"We already have someone for that."</dt><dd class="text-gray-600">"That is fine, the check may be useful to them too."</dd></div>
        <div><dt class="font-semibold">"Not interested."</dt><dd class="text-gray-600">"Understood, thank you for your time." Mark "Без интерес" so they do not come up again.</dd></div>
    </dl>
</div>
