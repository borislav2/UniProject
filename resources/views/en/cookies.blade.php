@extends('layouts.public')

@section('title', 'Cookie policy')
@section('meta_description', 'Which cookies the Creatium Lab website uses, what they are for and how to manage your consent.')

@section('content')
@php($tracking = (bool) config('creatium.gtm_id'))
@php($cookies = site('cookies'))
@include('partials/page-header', ['heading' => 'Cookie policy'])

<section class="py-12 bg-white dark:bg-ink-950">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8 text-gray-700 leading-relaxed dark:text-gray-300">
        <p>This policy explains which cookies creatiumlab.com uses, what they are for and how you can change your choice. How we process your personal data in general is described in the <a href="{{ lroute('privacy') }}" class="text-brand-600 underline dark:text-brand-300">Privacy policy</a>.</p>

        <div>
            <h2 class="text-xl font-bold text-gray-900 mb-2 dark:text-white">1. What cookies are</h2>
            <p>Cookies are small text files a website stores in your browser. Some are needed for the site to work. Others help us understand how the site is used or how well our ads perform. Similar data can be kept in your browser's local storage (localStorage), and this policy covers that too.</p>
        </div>

        <div>
            <h2 class="text-xl font-bold text-gray-900 mb-2 dark:text-white">2. Which cookies we use</h2>

            <h3 class="font-semibold text-gray-900 mt-4 mb-2 dark:text-white">Strictly necessary</h3>
            <p>The site cannot work properly without them, so they do not require consent (Art. 4a(3) of the Bulgarian Electronic Communications Act).</p>
            <div class="mt-3 overflow-x-auto rounded-xl border border-gray-200 dark:border-white/10">
                <table class="w-full text-sm text-left">
                    <thead class="bg-gray-50 text-gray-900 dark:bg-white/5 dark:text-white">
                        <tr><th class="px-4 py-2.5 font-semibold">Name</th><th class="px-4 py-2.5 font-semibold">Purpose</th><th class="px-4 py-2.5 font-semibold">Duration</th></tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/10">
                        <tr><td class="px-4 py-2.5 font-mono text-xs">{{ config('session.cookie') }}</td><td class="px-4 py-2.5">Session: remembers that the form was sent and shows its errors</td><td class="px-4 py-2.5 whitespace-nowrap">{{ config('session.lifetime') }} min</td></tr>
                        <tr><td class="px-4 py-2.5 font-mono text-xs">XSRF-TOKEN</td><td class="px-4 py-2.5">Protects forms against abuse</td><td class="px-4 py-2.5 whitespace-nowrap">{{ config('session.lifetime') }} min</td></tr>
                        @if($tracking)
                            <tr><td class="px-4 py-2.5 font-mono text-xs">creatium-consent</td><td class="px-4 py-2.5">Remembers your choice in the cookie banner (localStorage)</td><td class="px-4 py-2.5 whitespace-nowrap">1 year</td></tr>
                        @endif
                    </tbody>
                </table>
            </div>

            @if($tracking)
                <p class="mt-6">Analytics and marketing cookies are loaded through <strong>Google Tag Manager</strong> (Google Ireland Ltd.). Tag Manager itself does not set cookies. Until you give consent, the tools below do not store cookies (we use Google Consent Mode v2).</p>
            @endif

            @foreach(['analytics' => 'Analytics', 'marketing' => 'Marketing'] as $category => $label)
                <h3 class="font-semibold text-gray-900 mt-6 mb-2 dark:text-white">{{ $label }}</h3>
                @if($tracking && count($cookies[$category]))
                    <p>These load only if you allow them in the banner.</p>
                    <div class="mt-3 overflow-x-auto rounded-xl border border-gray-200 dark:border-white/10">
                <table class="w-full text-sm text-left">
                    <thead class="bg-gray-50 text-gray-900 dark:bg-white/5 dark:text-white">
                        <tr><th class="px-4 py-2.5 font-semibold">Name</th><th class="px-4 py-2.5 font-semibold">Provider and purpose</th><th class="px-4 py-2.5 font-semibold">Duration</th></tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/10">
                            @foreach($cookies[$category] as $cookie)
                                <tr><td class="px-4 py-2.5 font-mono text-xs whitespace-nowrap">{{ $cookie['name'] }}</td><td class="px-4 py-2.5"><strong class="font-semibold">{{ $cookie['provider'] }}</strong>: {{ $cookie['purpose'] }}</td><td class="px-4 py-2.5 whitespace-nowrap">{{ $cookie['duration'] }}</td></tr>
                            @endforeach
                    </tbody>
                </table>
            </div>
                @else
                    <p>We currently do not use {{ mb_strtolower($label) }} cookies. If we start, we will load them only with your consent and describe them here.</p>
                @endif
            @endforeach
        </div>

        <div>
            <h2 class="text-xl font-bold text-gray-900 mb-2 dark:text-white">3. How to change your choice</h2>
            @if($tracking)
                <p>You can change or withdraw your consent at any time in <a href="#" data-cookie-settings class="text-brand-700 underline dark:text-brand-300">Cookie settings</a> (the link is also at the bottom of every page). Your choice is valid for 12 months, after which we will ask again.</p>
            @endif
            <p class="mt-3">You can also delete or block cookies in your browser settings. If you block the strictly necessary ones, the contact form may not work.</p>
        </div>

        <div>
            <h2 class="text-xl font-bold text-gray-900 mb-2 dark:text-white">4. Questions</h2>
            <p>Write to us at <a href="mailto:{{ config('creatium.contact.email') }}" class="text-brand-600 underline dark:text-brand-300">{{ config('creatium.contact.email') }}</a>.</p>
        </div>

        <p class="text-sm text-gray-500 dark:text-gray-400">Last updated: 3 October 2026</p>
    </div>
</section>
@endsection
