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

    'questions' => [
        ['q' => 'How is the project budget set?', 'a' => 'We work with fixed price frameworks, starting from €450 for a starter website and €850 for a complete system for active marketing and online sales. All parameters, features and deadlines are described in our proposal in advance, so you have full visibility of your budget before the official start.'],
        ['q' => 'What is the monthly subscription for?', 'a' => 'To bring real results, a website needs systematic support and development, not just to sit there as a static business card. The monthly subscription covers round-the-clock technical security, hosting, regular backups and system updates, as well as the active marketing work – tracking metrics in Google Analytics, keeping your positions in Google Maps and AI search engines (ChatGPT, Gemini), email communication and ongoing changes.'],
        ['q' => 'Why are the website and the marketing done together?', 'a' => 'When the website and the marketing go to different contractors, communication often slows down and things do not line up – the marketing strategy runs into technical limits of the platform, and the website lacks tracking codes and the right structure. When they are designed together, the website is built with analytics, email funnels and SEO architecture in place from the very start.'],
        ['q' => 'How long does a project take?', 'a' => 'The standard time to build the platform, set up the analytics and prepare the initial marketing processes is between 2 and 4 weeks. The schedule is agreed at the start and followed strictly.'],
        ['q' => 'Do we need ready texts and visuals?', 'a' => 'Not necessarily. As part of the marketing service we structure and write professional, selling texts and articles for your business. For visuals we work with your real photos or choose suitable licensed images.'],
        ['q' => 'Can we make changes to the content ourselves?', 'a' => 'Yes. Websites come with an easy-to-use admin panel. On top of that, your subscription includes our regular support – so when you need to change prices, offers or texts, we can quickly do it for you.'],
        ['q' => 'How does payment work?', 'a' => 'The project starts with a 50% advance payment when the contract is signed and the scope is set. The remaining 50% is paid after you review the finished demo, the final corrections are made and you fully approve the product, before it goes live.'],
    ],

    'about' => [
        'subtitle' => '',
        'paragraphs' => [],
    ],

    'home_hero' => [
        'title' => 'A website that sells. Marketing that brings customers.',
        'highlight' => 'Your whole digital engine – in one working system.',
        'subtitle' => 'Most businesses make a fundamental mistake: they invest in a website with one team and then look for an outside agency to rescue it with marketing. We design the technology platform and the marketing strategy fully integrated, so your business can generate results from day one.',
        'standards_title' => 'How we work',
        'standards' => [
            ['title' => 'Integrated approach', 'text' => 'The website is built around the marketing strategy, not the other way round.'],
            ['title' => 'Central control', 'text' => 'Code, analytics, SEO/GEO/AEO optimisation and content are managed under one roof.'],
            ['title' => 'Transparent frameworks', 'text' => 'A fixed budget, a clearly defined scope and full predictability.'],
        ],
        'button' => 'Book a consultation',
    ],

    'home_problem' => [
        'title' => 'Why do fragmented digital services waste resources?',
        'scenarios' => [
            ['label' => 'Scenario A', 'title' => 'You have a website but no marketing infrastructure', 'text' => 'You have a presentable digital shop window that stays completely invisible to the market. There are no analytics, Google does not index it properly, and potential customers leave without making an enquiry.'],
            ['label' => 'Scenario B', 'title' => 'You invest in marketing, but the website stops the sales', 'text' => 'You send paid traffic and resources to a platform that loads slowly, has no properly configured tracking (Google Analytics 4, Bing Webmaster Tools) and breaks the user journey.'],
            ['label' => 'The solution', 'title' => 'One system for digital growth', 'text' => 'We combine the technical build, high speed and security with precise analytics, email automation, brand content and optimisation for Google and AI search engines. Technology provides stability; marketing provides growth.'],
        ],
    ],

    'home_engine' => [
        'title' => 'The components of your digital engine',
        'tech_title' => 'Technology infrastructure',
        'tech_tag' => 'The web platform',
        'tech_items' => [
            ['title' => 'Speed and security', 'text' => 'Modern web architecture, built for mobile devices and protected against outages.'],
            ['title' => 'Commerce integrations', 'text' => 'Modern payment methods (Stripe, myPOS, Apple Pay, Google Pay) and easy sign-in.'],
            ['title' => '24/7 monitoring and support', 'text' => 'Ongoing technical care and system updates so your processes never stop.'],
        ],
        'growth_title' => 'Marketing, analytics and positioning',
        'growth_tag' => 'The growth',
        'growth_items' => [
            ['title' => 'Google Analytics & metrics', 'text' => 'Business analytics that track every traffic source and conversion.'],
            ['title' => 'GEO, SEO & AI optimisation (AIO)', 'text' => 'Structuring your data so your brand is the first choice both in Google Maps and in AI assistants (ChatGPT, Gemini and others).'],
            ['title' => 'Automated email marketing', 'text' => 'Automated communication sequences that retain and convert your users.'],
            ['title' => 'Brand content and expert blogs', 'text' => 'Targeted texts that build authority in your niche and put your business ahead of the competition.'],
        ],
    ],

    'home_process' => [
        'title' => 'A managed, structured path from concept to market launch',
        'steps' => [
            ['title' => 'Strategic analysis', 'text' => 'A deep dive into the business model, the target audience and the competitive positioning.'],
            ['title' => 'Architecture and marketing plan', 'text' => 'Designing the website structure, the marketing funnels and the analytics points in parallel.'],
            ['title' => 'Demo and approval', 'text' => 'Reviewing an interactive prototype and approving the concept before the technical build.'],
            ['title' => 'Development and integration', 'text' => 'Coding the platform, configuring security and setting up analytics, SEO parameters and email platforms.'],
            ['title' => 'Technical tests and launch', 'text' => 'Checking system stability, mobile compatibility and integrations before the official launch.'],
            ['title' => 'Regular support and growth', 'text' => 'Daily care for technical health and steady scaling of your marketing presence.'],
        ],
    ],

    'home_contact' => [
        'title' => 'Ready for a digital system that works for your sales?',
        'text' => 'Tell us about your business goals. We will get back to you within one working day.',
    ],

    'services_page' => [
        'title' => 'The architecture of your digital success',
        'intro' => 'We do not sell fragmented services. We put in place the 4 essential elements that turn your online presence into a working sales channel.',
        'cards' => [
            [
                'slug' => 'websites', 'icon' => 'code', 'label' => 'Development and design (the web foundation)',
                'title' => 'A web platform designed to sell',
                'tag' => 'Speed, looks and mobile compatibility without compromise.',
                'description' => 'We build modern websites shaped around how your buyers behave. Lightning-fast loading, intuitive design and easy one-click payment.',
                'includes' => ['Online payment integrations (Stripe, myPOS, Apple Pay)', 'One-click sign-in with Google and Facebook', 'Mobile optimisation for maximum conversion'],
            ],
            [
                'slug' => 'geo-seo', 'icon' => 'magnifying-glass', 'label' => 'Analytics and SEO (visibility and discoverability)',
                'title' => 'GEO, SEO and AI positioning',
                'tag' => 'Be the first choice in Google and the new AI search engines.',
                'description' => 'Search is changing. We structure your website so it is easy to find locally (Google Maps) and recommended by AI assistants (ChatGPT, Gemini).',
                'includes' => ['Google Analytics setup and full traffic tracking', 'Local SEO and a Google Business profile', 'Optimisation for AI-driven search (AIO)'],
            ],
            [
                'slug' => 'email', 'icon' => 'envelope-open-text', 'label' => 'Email marketing (retention and sales)',
                'title' => 'Sales automation',
                'tag' => 'Turn one-time visitors into loyal customers.',
                'description' => 'We build smart email systems that talk to your customers at the moments that matter – automatically, without manual work from you.',
                'includes' => ['Automated email funnels after an enquiry or purchase', 'Setup of leading platforms (Klaviyo, Mailchimp, Brevo)', 'Guaranteed deliverability straight to the inbox'],
            ],
            [
                'slug' => 'monitoring', 'icon' => 'shield-halved', 'label' => 'Monitoring and security (support)',
                'title' => '24/7 technical care and security',
                'tag' => 'Peace of mind that your website works flawlessly every day.',
                'description' => 'We take care of the full technical health of your platform, so you do not lose customers and revenue to outages, slow speed or hacker attacks.',
                'includes' => ['24/7 uptime monitoring and immediate response', 'Regular backups and spam protection', 'Ongoing updates and speed optimisation'],
            ],
        ],
        'cta_title' => 'Do you need the complete system or a single component?',
        'cta_button' => 'Discuss your project with us',
    ],

    // Card texts for the demo sites (same slugs as config/creatium.php; the demo pages themselves are in Bulgarian).
    'demos' => [
        ['slug' => 'restorant', 'name' => 'Lozata Restaurant', 'industry' => 'Restaurant', 'description' => 'A restaurant website with a menu, table booking and opening hours where people can see them.'],
        ['slug' => 'salon', 'name' => 'Lavandula Studio', 'industry' => 'Beauty salon', 'description' => 'An elegant salon website with services, prices and appointment booking.'],
        ['slug' => 'dentalen-centar', 'name' => 'Byala Usmivka Dental Centre', 'industry' => 'Dental clinic', 'description' => 'A calm, trustworthy clinic website with services, the team and a check-up request form.'],
        ['slug' => 'avtoservis', 'name' => 'Motor Pro Car Service', 'industry' => 'Car repair shop', 'description' => 'A car service website with services, diagnostics prices and a quick call to action.'],
        ['slug' => 'pokrivi', 'name' => 'Zdrav Pokriv', 'industry' => 'Construction and roofing', 'description' => 'A construction company website with services, guarantees and a free on-site inspection.'],
        ['slug' => 'onlain-magazin', 'name' => 'Medena Pita', 'industry' => 'Online shop', 'description' => 'A small online shop for honey products with a catalogue and a basket.'],
    ],

    // Labels only: the keys must match config/creatium.php.
    'contact_topics' => [
        'new-system' => 'Building a new digital system (website + marketing)',
        'modernization' => 'Modernising an existing website and marketing optimisation',
        'support' => 'Technical support, analytics and digital growth',
    ],

    'business_sizes' => [
        'small' => 'Small',
        'medium' => 'Medium',
        'large' => 'Large',
    ],

    'plans' => [
        'intro' => 'You are not just buying a "website". You get a full marketing department and technical team that grow your business every day.',
        'items' => [
            [
                'name' => 'Starter Engine',
                'badge' => '',
                'highlighted' => false,
                'audience' => 'For businesses that want a clean, modern look, perfect technical health and a secure foundation for attracting their first customers.',
                'prices' => ['€450 initial investment (website build and structure)', '€150 / month (support, security and basic marketing oversight)'],
                'build_title' => 'Initial build',
                'build_items' => [
                    'A fast website that sells: Modern UI/UX design, optimised for mobile devices and instant loading.',
                    'Sales elements: Enquiry forms, quick-contact buttons, Google Maps integration and business email addresses.',
                    'Demo review: We make corrections and launch only after your full approval.',
                ],
                'monthly_title' => 'Every month after that we take care of',
                'monthly_items' => [
                    'Technical security: 24/7 monitoring, protection against hacker attacks, daily backups and fast response.',
                    'Content updates: Regular changes to prices, offers, photos and texts are included.',
                    'Results tracking: Analysis of incoming traffic and a monthly report on visitor behaviour.',
                ],
            ],
            [
                'name' => 'Active Marketing & Growth',
                'badge' => 'Most popular',
                'highlighted' => true,
                'audience' => 'For businesses ready to win their market. A full marketing strategy, automation, content and online sales.',
                'prices' => ['€850 first month (website build + launch of the complete marketing machine)', '€400 / month (active marketing management, positioning and support)'],
                'build_title' => 'In the first month we build and launch',
                'build_items' => [
                    'A high-conversion website: Everything in Package 1 + card payment integrations (Stripe / myPOS), Apple Pay, Google Pay and online booking systems.',
                    'Marketing architecture: Tracking pixels, Google Analytics, conversion funnels and retargeting preparation.',
                    'GEO & AI positioning: Optimisation for Google Maps and the new AI search engines (ChatGPT, Gemini), so they recommend you first.',
                ],
                'monthly_title' => 'Every month after that we do for your business',
                'monthly_items' => [
                    'Marketing strategy & branding: Campaign planning, building authority and developing your brand identity.',
                    'Content & copywriting: Strong sales texts, blogs and articles that attract customers ready to buy.',
                    'Automated email marketing: Building and sending automated email sequences that keep existing customers and win new ones.',
                    'Local SEO & AI growth: Keeping your leading positions in Google and AI assistants.',
                    'Full technical care: Round-the-clock monitoring, security, priority updates and lightning-fast response when needed.',
                ],
            ],
            [
                'name' => 'Custom Marketing Scale',
                'badge' => '',
                'highlighted' => false,
                'audience' => 'For companies with specific business processes, complex software integrations and a need for large-scale marketing resources.',
                'prices' => ['A custom quote for the build + a subscription based on scale'],
                'build_title' => 'Includes',
                'build_items' => [
                    'Custom web architecture: Coded entirely around your processes (CRM, warehouse systems, ERP).',
                    'Complete marketing expertise: Full sales funnels, content, visual strategy, real-time analytics and email systems.',
                    'Priority team: Round-the-clock monitoring, regular strategy meetings and a guaranteed priority contact channel.',
                ],
                'monthly_title' => '',
                'monthly_items' => [],
            ],
        ],
        'button' => 'Request a quote for this package',
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
