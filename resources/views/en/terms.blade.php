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
            <p>Sending an inquiry through the form does not commit you to anything and does not create a contract. After our call we send you a written offer with the scope, price and timeline. Work starts once you confirm it. Packages and prices on the site are indicative; the ones in the confirmed offer apply.</p>
        </div>
        <div>
            <h2 class="text-xl font-bold text-gray-900 mb-2 dark:text-white">3. Intellectual property</h2>
            <p>The text, design, logo and other content on the site belong to Creatium Lab and may not be copied or used without our written permission.</p>
        </div>
        <div>
            <h2 class="text-xl font-bold text-gray-900 mb-2 dark:text-white">4. Liability</h2>
            <p>We try to keep the information accurate and up to date, but we do not guarantee that the site will run without interruptions or errors. We are not responsible for the content of external sites we link to.</p>
        </div>
        <div>
            <h2 class="text-xl font-bold text-gray-900 mb-2 dark:text-white">5. Personal data and cookies</h2>
            <p>How we process your personal data is described in the <a href="{{ lroute('privacy') }}" class="text-brand-600 underline dark:text-brand-300">Privacy policy</a>, and which cookies we use in the <a href="{{ lroute('cookies') }}" class="text-brand-600 underline dark:text-brand-300">Cookie policy</a>.</p>
        </div>
        <div>
            <h2 class="text-xl font-bold text-gray-900 mb-2 dark:text-white">6. Governing law</h2>
            <p>These terms are governed by Bulgarian law. Questions: <a href="mailto:{{ $contact['email'] }}" class="text-brand-600 underline dark:text-brand-300">{{ $contact['email'] }}</a>.</p>
        </div>
        <p class="text-sm text-gray-500 dark:text-gray-400">Last updated: 3 October 2026</p>
    </div>
</section>
@endsection
