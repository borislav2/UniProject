<?php

// English content for /en. Same keys as config/creatium.php; anything missing here falls back to the Bulgarian
// config (see the site() helper). Contact details, legal data and tracking settings live only in creatium.php.

return [
    'team' => [
        ['name' => '', 'role' => 'Web development', 'description' => 'Builds the site from the first sketch to the day it goes live. Then looks after it when you want a change or something stops working.'],
        ['name' => '', 'role' => 'Marketing and SEO', 'description' => 'Makes sure people find you on Google: keywords, your Google Business Profile, page copy. And checks whether that actually brings in calls.'],
    ],

    'faq' => [
        ['q' => 'How long does a website take?', 'a' => 'A simple business site is ready in a few weeks. An online shop or a site with many pages takes longer. We give you an exact timeline once we have talked and know what you need.'],
        ['q' => 'What do I need to prepare?', 'a' => 'Nothing special. If you have a logo, photos and text, we will use them. If not, we will help. You can also leave the domain and hosting to us.'],
        ['q' => 'Can I change things on the site myself?', 'a' => 'We agree on this at the start. You can edit text and photos yourself, or just send us a message and we will do it.'],
        ['q' => 'Do you work with businesses outside Sofia?', 'a' => 'Yes, from anywhere in Bulgaria and abroad. Most things are sorted out by phone and email, so we do not need to be in the same city.'],
    ],

    'industries' => [
        ['name' => 'Restaurants', 'icon' => 'fa-utensils'],
        ['name' => 'Beauty salons', 'icon' => 'fa-spa'],
        ['name' => 'Online shops', 'icon' => 'fa-bag-shopping'],
        ['name' => 'Craftspeople', 'icon' => 'fa-hammer'],
        ['name' => 'Medical practices', 'icon' => 'fa-stethoscope'],
        ['name' => 'Construction companies', 'icon' => 'fa-helmet-safety'],
        ['name' => 'Car repair shops', 'icon' => 'fa-car'],
    ],

    'hero' => [
        'title' => 'Be recognizable.',
        'highlight' => 'Be Digital.',
        'subtitle' => 'A website Google loves, and marketing that actually sells.',
        'text' => '',
        'image' => null,
    ],

    'home_sections' => [
        'services_eyebrow' => 'Services',
        'services_title' => 'How we can help you stand out',
        'process_eyebrow' => 'From Concept to Implementation',
        'process_title' => 'How we work',
        'contact_title' => "Let's talk.",
        'contact_text' => 'Leave your name and phone number and pick what you need help with. We will call you within one business day, and the first call is free.',
    ],

    'about' => [
        'subtitle' => 'Two people who build websites for small businesses.',
        'paragraphs' => [
            'There are two of us. One builds the websites, the other handles marketing and SEO. That is why we think about how people will find you on Google while we are building the site, not after it is done.',
            'We work with you directly. If you have a question, write or call and you will talk to the person building your site.',
            'Creatium Lab is new and we are working on our first projects. That means every client gets our full attention.',
        ],
    ],

    'audience' => [
        'title' => 'For small and medium-sized businesses that want more inquiries',
        'intro' => '',
        'cards' => [
            ['icon' => 'store', 'title' => 'New website from scratch', 'text' => 'We build a modern, fast website optimized for Google and mobile devices. Complete with inquiry forms and full Google Maps integration.'],
            ['icon' => 'screwdriver-wrench', 'title' => 'Optimize your existing website', 'text' => 'We analyze your site, boost it in Google rankings, fix technical issues, and keep it maintained regularly.'],
        ],
    ],

    'services' => [
        [
            'slug' => 'monitoring',
            'icon' => 'heart-pulse',
            'title' => 'Website monitoring & health',
            'tag' => 'Web Health & QA Support',
            'description' => 'We watch your site, find problems before your customers do, and fix them fast.',
            'details' => 'Even good websites need regular care. We monitor your site every day: checking if it is fast, if there are any errors, if all forms work correctly. If we find a problem, we fix it right away. You sleep soundly, your site works.',
            'includes' => ['Daily monitoring of site performance', 'Speed and performance checks', 'Testing of all forms and features', 'Mobile compatibility checks on every page', 'Quick fixes when problems are found', 'Regular data backups'],
        ],
        [
            'slug' => 'geo-seo',
            'icon' => 'map-location-dot',
            'title' => 'GEO & SEO visibility',
            'tag' => 'Local SEO and AI search',
            'description' => 'Customers find you when they search on Google, especially in your city.',
            'details' => 'When someone searches for "beauty salon Sofia" or "car repair near me", you want to be near the top. We optimize your website and your Google profile for exactly those searches. More and more people also ask ChatGPT or Gemini for recommendations, so we structure the information about your business in a way AI assistants understand too.',
            'includes' => ['Keyword research for your city', 'Google Business Profile and Google Maps', 'Technical audit and fixes on the site', 'Search-optimized content', 'Visibility in AI search engines and assistants', 'Ranking tracking'],
        ],
        [
            'slug' => 'email',
            'icon' => 'envelope-open-text',
            'title' => 'Email campaigns',
            'tag' => 'Automated emails',
            'description' => 'Emails that go out on their own at the right moment, without you writing each one by hand.',
            'details' => 'For example a welcome email for new subscribers, a reminder about a booked appointment, or an offer for a customer who has not bought anything in a few months. We plan the sequence, set it up and make sure the emails land in the inbox, not in spam.',
            'includes' => ['A plan for which emails go out and when', 'Setup of the platform and the sign-up form', 'Domain setup so emails do not end up in spam', 'Changes and support after launch'],
        ],
        [
            'slug' => 'websites',
            'icon' => 'code',
            'title' => 'Website design & development',
            'tag' => 'New site or improvements',
            'description' => 'A fast, modern website built from scratch: a business card site, a multi-page site or an online shop. Or we improve the site you already have.',
            'details' => 'Most people will open your site on their phone while looking for where to go or whom to call. So we make it fast, clear, and put your phone number and address where people can see them. It works for a restaurant, a salon, a clinic, a repair shop or a small store.',
            'includes' => ['Design with your logo and colors', 'Inquiry form, tap-to-call phone number and a map', 'Basic Google setup', 'Help with the domain, hosting and email', 'Changes and support after the site goes live'],
        ],
    ],

    // Labels only: the keys must match config/creatium.php.
    'contact_topics' => [
        'website' => 'Website design & development',
        'monitoring' => 'Website monitoring & health',
        'geo-seo' => 'GEO & SEO visibility',
    ],

    'process' => [
        ['icon' => 'envelope', 'title' => 'Inquiry', 'description' => 'Send us the form. Your name, phone number and what you need help with are enough.'],
        ['icon' => 'phone', 'title' => 'Phone call', 'description' => 'We call you back within one business day and ask about your business, your customers and what exactly you want.'],
        ['icon' => 'clipboard-list', 'title' => 'Plan', 'description' => 'We write down what we will do, how long it will take and what it will cost, and send it to you.'],
        ['icon' => 'handshake', 'title' => 'Agreement', 'description' => 'We go through the plan together and change it as much as needed. We only start once you say yes.'],
        ['icon' => 'code', 'title' => 'Build', 'description' => 'We do what we agreed on and show you how it is going before it is finished.'],
        ['icon' => 'rocket', 'title' => 'Launch', 'description' => 'We launch the site or campaign, check that everything works, and stay available for changes.'],
    ],

    'process_promise' => [
        'title' => 'The customer is king',
        'text' => 'We reply quickly, you always know where things stand, and we do exactly what we agreed on. No surprises, no going off script.',
    ],

    'packages' => [
        [
            'name' => 'Start',
            'description' => 'When you simply need to be online.',
            'price_note' => 'Price on request',
            'features' => ['One page with the essentials', 'Works on mobile', 'Inquiry form', 'Basic Google setup'],
            'highlighted' => false,
        ],
        [
            'name' => 'Business',
            'description' => 'When you have more to show: services, prices, photos.',
            'price_note' => 'Price on request',
            'features' => ['Up to 5 pages', 'Design made for you', 'SEO setup', 'Google Business Profile'],
            'highlighted' => true,
        ],
        [
            'name' => 'Growth',
            'description' => 'A website plus monthly work to climb in Google.',
            'price_note' => 'Price on request / month',
            'features' => ['Everything in Business', 'Monthly SEO work', 'New copy for the site', 'Ranking tracking on Google', 'A short monthly report'],
            'highlighted' => false,
        ],
    ],

    'package_notes' => [
        ['icon' => 'phone', 'title' => 'The first call is free', 'text' => 'You tell us what you need, and we tell you which package fits and roughly what it would cost.'],
        ['icon' => 'clipboard-list', 'title' => 'A written offer', 'text' => 'Before we start, you get a plan with the exact price, the timeline and what is included.'],
        ['icon' => 'handshake', 'title' => 'Packages are flexible', 'text' => 'You can add or remove things. You pay for what you actually need.'],
    ],

    'cookies' => [
        'analytics' => [
            ['name' => '_ga, _ga_*', 'provider' => 'Google Analytics 4 (Google Ireland Ltd.)', 'purpose' => 'Counts visits and shows which pages are read and where visitors come from', 'duration' => 'up to 2 years'],
            ['name' => '_clck, _clsk', 'provider' => 'Microsoft Clarity (Microsoft Ireland Operations Ltd.)', 'purpose' => 'Shows how the page is used (clicks, scrolling) so we can fix what is awkward', 'duration' => 'up to 1 year / 1 day'],
        ],
        'marketing' => [
            ['name' => '_fbp, _fbc', 'provider' => 'Meta Pixel (Meta Platforms Ireland Ltd.)', 'purpose' => 'Measures visits and inquiries coming from our ads', 'duration' => 'up to 90 days'],
            ['name' => '_gcl_au', 'provider' => 'Google Ads (Google Ireland Ltd.)', 'purpose' => 'Measures inquiries coming from our Google ads', 'duration' => 'up to 90 days'],
        ],
    ],
];
