@extends('layouts.public')

@section('title', 'Terms and conditions')
@section('meta_description', 'Terms for using the Creatium Lab website and for inquiries sent through it.')

@section('content')
@include('partials/page-header', ['heading' => 'Terms and conditions'])

<section class="py-12 bg-white dark:bg-ink-950">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8 text-gray-700 leading-relaxed dark:text-gray-300">
        <div>
            <h2 class="text-xl font-bold text-gray-900 mb-2 dark:text-white">1. General</h2>
            <p>By using creatiumlab.com you accept these terms. The information on the site is for general information only and is not a binding offer. Specific services, prices and timelines are agreed in a separate contract or a confirmed offer. The Bulgarian version of these terms prevails.</p>
        </div>
        <div>
            <h2 class="text-xl font-bold text-gray-900 mb-2 dark:text-white">2. Inquiries and offers</h2>
            <p>Sending an inquiry through the form does not commit you to anything and does not create a contract. After our call we send you a written offer with the scope, price and timeline. We start preparing the offer and the demo after the first part of the start price (section 3). Work starts once you confirm the offer. Packages and prices on the site are indicative; the ones in the confirmed offer apply.</p>
        </div>
        <div>
            <h2 class="text-xl font-bold text-gray-900 mb-2 dark:text-white">3. Price, payment, subscription, guarantee and ads</h2>
            <ul class="list-disc pl-5 space-y-2">
                <li><strong class="text-gray-900 dark:text-white">Paying the start price.</strong> The start price is paid in two parts. For the Starter Engine package (€400) the first part is €100 when you request an offer and demo, and the remaining €300 is paid when you agree. For the Active Marketing and Growth package (€800) the first part is €200, and the remaining €600 is paid when you agree. The first part is paid before we prepare the offer and the demo and is not refunded if you decide not to continue. Custom Marketing (from €1,200 a month) also has a deposit before the offer and the demo. The amount is set in the custom offer and is not refunded either.</li>
                <li><strong class="text-gray-900 dark:text-white">Subscription from the second month.</strong> The first month is included in the start price. After that the subscription is €200 a month for Starter Engine and €650 a month for Active Marketing and Growth. With yearly billing 2 months are free, so €2,000 instead of €2,400 and €6,500 instead of €7,800 for 12 months.</li>
                <li><strong class="text-gray-900 dark:text-white">Subscription price.</strong> The monthly price in the confirmed offer is fixed for the first 6 months. After that it may change, and we tell you in advance before the new price applies. With yearly billing the price holds for the whole paid year.</li>
                <li><strong class="text-gray-900 dark:text-white">Guarantee.</strong> If the website has no traffic 60 days after launch, we refund what you have paid. Visits are measured with Google Analytics, which we set up at the start and you have access to. The exact terms of the guarantee are set out in the contract.</li>
                <li><strong class="text-gray-900 dark:text-white">Ad budget.</strong> The ad budget for Facebook and Instagram is not included in the price. You pay it directly to Meta. We monitor the ads and tell you what they bring.</li>
            </ul>
        </div>
        <div>
            <h2 class="text-xl font-bold text-gray-900 mb-2 dark:text-white">4. Intellectual property</h2>
            <p>The text, design, logo and other content on the site belong to Creatium Lab and may not be copied or used without our written permission.</p>
        </div>
        <div>
            <h2 class="text-xl font-bold text-gray-900 mb-2 dark:text-white">5. Liability</h2>
            <p>We try to keep the information accurate and up to date, but we do not guarantee that the site will run without interruptions or errors. We are not responsible for the content of external sites we link to.</p>
        </div>
        <div>
            <h2 class="text-xl font-bold text-gray-900 mb-2 dark:text-white">6. Personal data and cookies</h2>
            <p>How we process your personal data is described in the <a href="{{ lroute('privacy') }}" class="text-brand-600 underline dark:text-brand-300">Privacy policy</a>, and which cookies we use in the <a href="{{ lroute('cookies') }}" class="text-brand-600 underline dark:text-brand-300">Cookie policy</a>.</p>
        </div>
        <div>
            <h2 class="text-xl font-bold text-gray-900 mb-2 dark:text-white">7. Governing law</h2>
            <p>These terms are governed by Bulgarian law. For questions write to <a href="mailto:{{ $contact['email'] }}" class="text-brand-600 underline dark:text-brand-300">{{ $contact['email'] }}</a>.</p>
        </div>
        <p class="text-sm text-gray-500 dark:text-gray-400">Last updated 8 November 2026</p>
    </div>
</section>
@endsection
