<?php

// English content for /en. Same keys as config/creatium.php; anything missing here falls back to the Bulgarian
// config (see the site() helper). Contact details, legal data and tracking settings live only in creatium.php.

return [
    'team' => [
        [
            'name' => 'Borislav Kostadinov',
            'role' => 'Full-Stack Developer',
            'description' => 'Architecture and clean code that turn your idea into a secure, high-quality digital platform.',
            'bio' => [
                'I am responsible for the overall architecture, security and technical implementation of projects at Creatium Lab. My focus is building secure and scalable systems that deliver high performance, stable operation and a pleasant user experience.',
                'Every web platform is designed from the start with the business in mind — with clean code, ready-made integration for analytics, conversion tracking and search engine optimization.',
                'I have a degree in Computer Programming from MSU Lomonosov, a C# certificate from Software University, and I am currently in my third year of a Bachelor\'s degree in Software Engineering at St. Cyril and St. Methodius University of Veliko Tarnovo, where I am deepening my knowledge of software architecture and engineering best practices.',
            ],
            'skills' => ['Laravel & Filament', 'MVC Architecture', 'MySQL & RESTful APIs', 'HTML, CSS & Bootstrap', 'jQuery', 'Git & Agile', 'Postman'],
            'image' => 'images/team/borislav-kostadinov.webp',
            'linkedin' => 'https://www.linkedin.com/in/borislav-kostadinov-7ba990285/',
        ],
        [
            'name' => 'Vladimir Tsonchev',
            'role' => 'Marketing and SEO',
            'description' => 'I make sure people find you on Google: keywords, your Google Business Profile, page copy. And I check whether that actually brings in calls.',
            'bio' => [
                'I have been interested in marketing and computers since childhood and often wondered how to bring them together. I started at the National School of Management, then moved into digital marketing at SoftUni. I earned certificates in Marketing Basics, Content Marketing, Facebook Marketing, Google Ads, Google Analytics and Email Marketing, and I am now finishing the whole course to become a Performance Marketing Expert.',
                'I believe this is the future, and it is where I see mine. I stay motivated because I love learning, and in this field you learn something new every day.',
                'I would describe myself as positive, composed, modest and hardworking, and I do not easily give up on the things I love.',
            ],
            'skills' => ['Google Ads', 'Google Analytics', 'Facebook marketing', 'Content marketing', 'Email marketing', 'Performance marketing'],
            'image' => 'images/team/vladimir-tsonchev.webp',
            'linkedin' => 'https://www.linkedin.com/in/vladimirtsonchev/',
        ],
    ],

    'faq' => [
        ['q' => 'How much does it cost?', 'a' => 'The price depends on how many pages and how much content you need. Tell us what you want and before we start you will get a written offer with the exact price, the timeline and what is included.'],
        ['q' => 'How long does a website take?', 'a' => 'A simple business site is ready in a few weeks. An online shop or a site with many pages takes longer. We give you an exact timeline once we have talked and know what you need.'],
        ['q' => 'What do I need to prepare?', 'a' => 'Nothing special. If you have a logo, photos and text, we will use them. If not, we will help. You can also leave the domain and hosting to us.'],
        ['q' => 'Can I change things on the site myself?', 'a' => 'We agree on this at the start. You can edit text and photos yourself, or just send us a message and we will do it.'],
        ['q' => 'Do you work with businesses outside Sofia?', 'a' => 'Yes, from anywhere in Bulgaria and abroad. Most things are sorted out by phone and email, so we do not need to be in the same city.'],
        ['q' => 'I have a website, but it brings no customers. Can you help?', 'a' => 'Yes. We look at what is in the way: a slow site, no presence on Google, a form that does not work. We fix it and then keep it in shape.'],
        ['q' => 'What happens once the site is finished?', 'a' => 'We stay available. If you want a change or something stops working, write to us. We can also keep an eye on the site regularly, so problems are caught early.'],
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
        'contact_text' => 'Tell us about your business and what we can help with.',
    ],

    'about' => [
        'subtitle' => '',
        'paragraphs' => [],
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
            'slug' => 'websites',
            'icon' => 'code',
            'title' => 'Website design & maintenance',
            'tag' => 'New site or improvements',
            'description' => 'A website that presents you professionally and turns visitors into inquiries. We build a new one from scratch or improve the one you already have.',
            'details' => 'Most people will open your site on their phone while looking for where to go or whom to call. So we make it fast, clear, and put your phone number and address where people can see them, so they can find you and reach you in seconds. Before we start, we show you a demo version, so you know what you are getting.',
            'includes' => ['Design with your logo and colors', 'Inquiry form, tap-to-call phone number and a map', 'Google indexing', 'Help with the domain, hosting and email', 'A site audit once it is live', 'Changes and maintenance'],
        ],
        [
            'slug' => 'monitoring',
            'icon' => 'heart-pulse',
            'title' => 'Website monitoring & health',
            'tag' => 'Web Health & QA Support',
            'description' => 'We keep an eye on your site and tell you what is wrong before you lose customers.',
            'details' => 'A website can stop working without you knowing: the form does not send inquiries, a page does not open on a phone, or the site becomes very slow. Every one of those problems is a lost customer. We check your site regularly, test how it works and tell you what needs fixing.',
            'includes' => ['Regular checks that the site is online', 'Functionality testing', 'Page speed check', 'Mobile version check', 'Updates and backups'],
        ],
        [
            'slug' => 'geo-seo',
            'icon' => 'map-location-dot',
            'title' => 'GEO & SEO visibility',
            'tag' => 'Local SEO and AI search',
            'description' => 'When people in your city search for what you offer, they find you.',
            'details' => 'When someone searches for "beauty salon Sofia" or "car repair near me", you want to be among the first results. We optimize your website and your Google profile for exactly those searches. More and more people also ask AI assistants like ChatGPT or Gemini for recommendations, so we structure the information about your business so the assistant can find you.',
            'includes' => ['Keyword research for your city', 'Google Business Profile', 'Technical audit and fixes on the site', 'Google Analytics implementation', 'Content optimized for AI and search engines'],
        ],
        [
            'slug' => 'email',
            'icon' => 'envelope-open-text',
            'title' => 'Emails & campaigns',
            'tag' => 'Automated emails',
            'description' => 'Emails at exactly the right moment, without you writing each one by hand.',
            'details' => 'For example a welcome email for new subscribers, a reminder about a booked appointment, or an offer for a customer who has not bought anything in a few months. We plan the campaign, set it up and make sure the emails reach the right person.',
            'includes' => ['A plan for sending the campaign', 'Setup of the platform and the sign-up form', 'Emails reach the right person', 'Changes and support after launch'],
        ],
    ],

    // Labels only: the keys must match config/creatium.php.
    'contact_topics' => [
        'website' => 'Website design',
        'monitoring' => 'Monitoring',
        'marketing' => 'Marketing',
    ],

    'business_sizes' => [
        'small' => 'Small',
        'medium' => 'Medium',
        'large' => 'Large',
    ],

    'process' => [
        ['icon' => 'envelope', 'title' => 'Inquiry', 'description' => 'Send us an email and tell us what you need help with.'],
        ['icon' => 'phone', 'title' => 'Phone call', 'description' => 'We call you back within one business day and ask about your business, your customers and what you are struggling with.'],
        ['icon' => 'clipboard-list', 'title' => 'Offer', 'description' => 'We describe what we will do, how long it will take and what it will cost, and show you a demo version.'],
        ['icon' => 'handshake', 'title' => 'Agreement', 'description' => 'We go through the offer together and change it as much as needed. We only start once you say yes.'],
        ['icon' => 'code', 'title' => 'Build', 'description' => 'We do what we agreed on and show you how it is going before it is finished.'],
        ['icon' => 'rocket', 'title' => 'Launch', 'description' => 'We launch your product (site or campaign), monitor it and keep developing it together with you.'],
    ],

    'packages' => [
        [
            'name' => 'Start',
            'description' => 'A web presence for your business.',
            'price_note' => '',
            'features' => ['One page with the essentials', 'Works on mobile', 'Inquiry form', 'Basic Google setup'],
            'highlighted' => false,
        ],
        [
            'name' => 'Business',
            'description' => 'When you have more to show: services, prices, photos.',
            'price_note' => '',
            'features' => ['Everything in Start', 'Up to 5 pages', 'SEO setup', 'Google Business Profile'],
            'highlighted' => true,
        ],
        [
            'name' => 'Growth',
            'description' => 'A website plus monthly work to climb in Google.',
            'price_note' => '',
            'features' => ['Everything in Business', 'Monthly SEO work', 'New copy for the site', 'Ranking tracking on Google', 'A short monthly report'],
            'highlighted' => false,
        ],
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
