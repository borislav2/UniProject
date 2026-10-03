@extends('layouts.public')

@section('title', 'Privacy policy')
@section('meta_description', 'How Creatium Lab collects, uses and protects your personal data.')

@section('content')
@include('partials/page-header', ['heading' => 'Privacy policy'])

<section class="py-12 bg-white dark:bg-ink-950">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8 text-gray-700 leading-relaxed dark:text-gray-300">
        <p>This policy explains how we process your personal data when you use creatiumlab.com and send us an inquiry, in line with Regulation (EU) 2016/679 (GDPR) and the Bulgarian Personal Data Protection Act. The Bulgarian version of this policy prevails.</p>

        <div>
            <h2 class="text-xl font-bold text-gray-900 mb-2 dark:text-white">1. Who processes your data</h2>
            <p>The data controller is <strong>{{ $legal['company'] }}</strong>, company ID (EIK) {{ $legal['eik'] }}, address: {{ $legal['address'] }}. For questions about your personal data, write to <a href="mailto:{{ $contact['email'] }}" class="text-brand-600 underline dark:text-brand-300">{{ $contact['email'] }}</a>.</p>
        </div>

        <div>
            <h2 class="text-xl font-bold text-gray-900 mb-2 dark:text-white">2. What data we collect</h2>
            <ul class="list-disc pl-6 space-y-1">
                <li>Data you send us through the contact form: name, phone number, the service you are interested in, and your message if you write one.</li>
                <li>How you got to the site (for example from Google or from an ad), if the site receives it from your browser or from the link you followed. We store it with your inquiry so we know which channels work.</li>
                <li>Technical data needed to run and secure the site: IP address and request data in the server logs.</li>
                @if(config('creatium.gtm_id'))
                    <li>Data about your visit from analytics and advertising tools, only if you have allowed them (see section 6).</li>
                @endif
            </ul>
        </div>

        <div>
            <h2 class="text-xl font-bold text-gray-900 mb-2 dark:text-white">3. Why and on what legal basis</h2>
            <ul class="list-disc pl-6 space-y-1">
                <li>To reply to your inquiry and send you an offer: based on your consent and on taking steps at your request before entering into a contract (Art. 6(1)(a) and (b) GDPR).</li>
                <li>For security and to prevent misuse of the site: based on our legitimate interest (Art. 6(1)(f) GDPR).</li>
            </ul>
        </div>

        <div>
            <h2 class="text-xl font-bold text-gray-900 mb-2 dark:text-white">4. How long we keep your data</h2>
            <p>Inquiries are kept for no more than 12 months after our last contact, unless we sign a contract. In that case the data is kept as long as the contract and accounting law require. Server logs are kept for a limited time.</p>
        </div>

        <div>
            <h2 class="text-xl font-bold text-gray-900 mb-2 dark:text-white">5. Who has access</h2>
            <p>We do not sell your data or share it with third parties for their marketing. Only our team members and the providers that help us operate (hosting, email and, with your consent, the tools in section 6) have access.</p>
        </div>

        <div id="cookies" class="scroll-mt-28">
            <h2 class="text-xl font-bold text-gray-900 mb-2 dark:text-white">6. Cookies and measurement</h2>
            <p>The site uses strictly necessary cookies (session and form protection against abuse). These do not require consent.</p>
            @if(config('creatium.gtm_id'))
                <p class="mt-3">We use <strong>Google Tag Manager</strong> to load analytics tools (such as Google Analytics 4 and Microsoft Clarity) and ad measurement tools (such as Meta Pixel). They set cookies and send their providers information about your visit (pages, device, approximate location from your IP address, submitted inquiry) <strong>only if you allow them</strong> in the cookie banner, separately for analytics and marketing. Legal basis: your consent (Art. 6(1)(a) GDPR).</p>
                <p class="mt-3">Google, Microsoft and Meta may transfer data to the United States. They are certified under the EU-US Data Privacy Framework and use standard contractual clauses.</p>
                <p class="mt-3">You can withdraw or change your consent at any time in <a href="#" data-cookie-settings class="text-brand-700 underline dark:text-brand-300">Cookie settings</a>.</p>
            @else
                <p class="mt-3">We do not use analytics or advertising cookies. If we start using them, we will ask for your consent and update our policies.</p>
            @endif
            <p class="mt-3">You will find the full list in the <a href="{{ lroute('cookies') }}" class="text-brand-700 underline dark:text-brand-300">Cookie policy</a>.</p>
        </div>

        <div>
            <h2 class="text-xl font-bold text-gray-900 mb-2 dark:text-white">7. Your rights</h2>
            <p>You have the right to access, rectify and erase your data, to restrict processing, to data portability and to object, and to withdraw your consent at any time. Write to us at the email above. You also have the right to lodge a complaint with the Bulgarian Commission for Personal Data Protection (<a href="https://www.cpdp.bg" class="text-brand-600 underline dark:text-brand-300" target="_blank" rel="noopener">www.cpdp.bg</a>).</p>
        </div>

        <p class="text-sm text-gray-500 dark:text-gray-400">Last updated: 3 October 2026</p>
    </div>
</section>
@endsection
