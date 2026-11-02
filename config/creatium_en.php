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

    // Страница „Често задавани въпроси“ (/vaprosi). „group“ подрежда въпросите в теми; празно = без тема.
    'questions' => [
        ['q' => 'How is the project budget set?', 'a' => 'We work with fixed price frameworks, starting from €450 for a starter website and €850 for a complete system for active marketing and online sales. All parameters, features and deadlines are described in our proposal in advance, so you have full visibility of your budget before the official start.', 'group' => 'Prices and payment'],
        ['q' => 'What is the monthly subscription for?', 'a' => 'To bring real results, a website needs systematic support and development, not just to sit there as a static business card. The monthly subscription covers round-the-clock technical security, hosting, regular backups and system updates, as well as the active marketing work – tracking metrics in Google Analytics, keeping your positions in Google Maps and AI search engines (ChatGPT, Gemini), email communication and ongoing changes.', 'group' => 'Prices and payment'],
        ['q' => 'How does payment work?', 'a' => 'The project starts with a 50% advance payment when the contract is signed and the scope is set. The remaining 50% is paid after you review the finished demo, the final corrections are made and you fully approve the product, before it goes live.', 'group' => 'Prices and payment'],
        ['q' => 'Are hosting and the domain included?', 'a' => 'Yes. The monthly subscription covers setting up and looking after hosting, the domain and business email addresses. If you already have a domain, we connect it to the new site.', 'group' => 'Prices and payment'],
        ['q' => 'How long does a project take?', 'a' => 'The standard time to build the platform, set up the analytics and prepare the initial marketing processes is between 1 and 2 weeks. The schedule is agreed at the start and followed strictly.', 'group' => 'Build and timing'],
        ['q' => 'Do we need ready texts and visuals?', 'a' => 'Not necessarily. As part of the marketing service we structure and write professional, selling texts and articles for your business. For visuals we work with your real photos or choose suitable licensed images.', 'group' => 'Build and timing'],
        ['q' => 'Will I see the site before it goes live?', 'a' => 'Yes. Before launch you get a demo version to review on your computer and phone. We make the changes and launch only after you approve it.', 'group' => 'Build and timing'],
        ['q' => 'Can you update our current site instead of building a new one?', 'a' => 'Yes. We start by reviewing your current site and tell you what is worth keeping and what to rework. Sometimes updating is the faster and cheaper way.', 'group' => 'Build and timing'],
        ['q' => 'Do you build online shops?', 'a' => 'Yes. We build online shops with card payments (Stripe, myPOS, Apple Pay, Google Pay), order tracking and automatic emails after purchase.', 'group' => 'Build and timing'],
        ['q' => 'Do you work with businesses outside Sofia?', 'a' => 'Yes. We work with businesses all over Bulgaria. Calls and reviews happen online, so location does not matter.', 'group' => 'Build and timing'],
        ['q' => 'Why are the website and the marketing done together?', 'a' => 'When the website and the marketing go to different contractors, communication often slows down and things do not line up – the marketing strategy runs into technical limits of the platform, and the website lacks tracking codes and the right structure. When they are designed together, the website is built with analytics, email funnels and SEO architecture in place from the very start.', 'group' => 'Google, ads and AI'],
        ['q' => 'Do you guarantee first place in Google?', 'a' => 'No honest specialist guarantees a ranking. We guarantee the work and a clear monthly report on what has changed.', 'group' => 'Google, ads and AI'],
        ['q' => 'What is visibility in AI assistants?', 'a' => 'More and more people ask ChatGPT or Gemini for a recommendation. AI assistants recommend businesses that clearly describe what they do and where. We structure your site and profiles so they understand them.', 'group' => 'Google, ads and AI'],
        ['q' => 'Can we make changes to the content ourselves?', 'a' => 'Yes. Websites come with an easy-to-use admin panel. On top of that, your subscription includes our regular support – so when you need to change prices, offers or texts, we can quickly do it for you.', 'group' => 'After launch'],
        ['q' => 'What happens if the site goes down?', 'a' => 'We monitor the site around the clock. If it goes down, we get an alert and act at once, and daily backups let us restore it quickly.', 'group' => 'After launch'],
        ['q' => 'Will I know how many enquiries come from the site?', 'a' => 'Yes. We track every enquiry and call, so you see how many customers come from the site and from which channel.', 'group' => 'After launch'],
        ['q' => 'How fast do you reply?', 'a' => 'We reply to every enquiry within one working day at the latest.', 'group' => 'After launch'],
    ],

    'about' => [
        'subtitle' => '',
        'paragraphs' => [],
        'story_title' => 'Why we do it',
        'story' => [
            'We have been building websites and doing digital marketing for 4 years.',
            'We started because we kept seeing the same thing: a business pays one company for the website and another for advertising, and nobody is responsible for whether customers come. The website is "done", the ads are "running", and the phone does not ring.',
            'So we work as one team: Borislav builds the website and the technical side, and Vladimir makes sure people find you and get in touch. We are responsible for the whole way, from the first page to the first enquiry.',
            'We are with you all the way: from the idea to the day the website brings customers, and after that.',
        ],
    ],

    'home_hero' => [
        'title' => 'Digital solutions that move your business forward.',
        'highlight' => 'Custom websites. Branded marketing. Strategic solutions for more customers.',
        'button' => 'Calculate the benefit for your business',
        'accent' => ['forward'],
    ],

    'home_problem' => [
        'eyebrow' => 'The solution',
        'solution_title' => 'A website and marketing that work as one.',
        'solution_text' => "We take on the whole process: from the website and connecting the forms to your email or CRM, to advertising and visibility on Google.\n\nWe are responsible for the whole system, from the first visit to the first enquiry.",
        'stats' => [
            ['icon' => 'location-dot', 'value' => '28%', 'text' => 'of searches for a business nearby on a phone end in a purchase', 'meaning' => 'Being found on Google means sales, not just visits.', 'source' => 'Google', 'url' => 'https://www.thinkwithgoogle.com/data/local-search-conversion-statistics/'],
            ['icon' => 'store', 'value' => '+70%', 'text' => 'more visits for businesses with a complete Google profile, and customers are 50% more likely to buy', 'meaning' => 'A profile with photos, reviews and opening hours brings customers without paid ads.', 'source' => 'Google', 'url' => 'https://support.google.com/business/answer/10515606'],
            ['icon' => 'gauge-high', 'value' => '+8.4%', 'text' => 'more sales when a site gets just 0.1 seconds faster on mobile', 'meaning' => 'Speed is not a detail: every tenth of a second is money.', 'source' => 'Deloitte, 2020', 'url' => 'https://www.deloitte.com/ie/en/services/consulting/research/milliseconds-make-millions.html'],
            ['icon' => 'envelope-open-text', 'value' => '€38', 'text' => 'returned on average for every €1 spent on emails to customers', 'meaning' => 'People who have bought once buy again when you remind them in time.', 'source' => 'Litmus', 'url' => 'https://www.litmus.com/press/litmus-research-finds-email-marketing-roi-is-38-to-1'],
        ],
        'system_title' => 'Website and marketing in one system',
        'system_text' => 'You do not buy a website from one place and ads from another. We do both together and answer for the result.',
        'system_items' => [
            ['icon' => 'rocket', 'title' => 'A fast site with your brand', 'text' => 'Opens instantly on a phone and leads to a call.'],
            ['icon' => 'magnifying-glass', 'title' => 'Visibility on Google and AI', 'text' => 'SEO and a Google profile that get you found.'],
            ['icon' => 'chart-line', 'title' => 'Ads you can measure', 'text' => 'Google and Meta, with every enquiry tracked.'],
            ['icon' => 'phone-volume', 'title' => 'Enquiries straight to you', 'text' => 'Forms and a call button to email, phone or CRM.'],
            ['icon' => 'envelope-open-text', 'title' => 'Emails that bring customers back', 'text' => 'Automatic emails after an enquiry and a purchase.'],
            ['icon' => 'shield-halved', 'title' => 'Care every month', 'text' => 'Security, backups, changes and a report.'],
        ],
        'title' => 'Where are you losing customers?',
        'scenarios' => [
            ['label' => 'Scenario 1', 'title' => 'A good-looking site nobody finds', 'text' => "When one team builds the site and another runs the ads, nobody is responsible for the end result. One side says \"the site is done\", the other says \"the problem is the site\", and you pay both."],
            ['label' => 'Scenario 2', 'title' => 'You pay for ads but lose enquiries', 'text' => 'You pour money into Google and Meta ads, but the contact forms are not connected properly. Customers try to reach you, the enquiries get lost on the way, and the ad budget goes to waste.'],
            ['label' => 'The solution', 'title' => 'Everything in one place. With real results.', 'text' => "We take on the whole process: from coding the site and connecting the forms to your email or CRM to setting up the ads in full.\n\nWe are responsible for the whole system, from the visitor's first click to the first enquiry."],
        ],
    ],

    'home_engine' => [
        'title' => 'The components of your digital engine',
        'tech_title' => 'Technology infrastructure',
        'tech_tag' => 'The web platform',
        'tech_items' => [
            ['title' => 'Operational stability and protection', 'text' => 'Modern web architecture built for fast loading, full mobile adaptation and resilience under heavy traffic.'],
            ['title' => 'Commerce integrations and payments', 'text' => 'Modern payment systems (Stripe, myPOS, Apple Pay, Google Pay) and automated user identification.'],
            ['title' => 'Round-the-clock monitoring and system care', 'text' => 'Continuous technical control and regular updates that keep your business processes running without interruption.'],
            ['title' => 'Business communication and hosting', 'text' => 'Configuration and central maintenance of server infrastructure, domains and business email addresses.'],
            ['title' => 'Independent content management', 'text' => 'An intuitive admin panel where you change prices, texts and media yourself in one click, without depending on outside specialists.'],
            ['title' => 'Cybersecurity and backups', 'text' => 'SSL encryption, protection from hacking attempts and daily backups that keep your site, your data and your enquiries safe at all times.'],
        ],
        'growth_title' => 'Marketing, analysis and positioning',
        'growth_tag' => 'The growth',
        'growth_items' => [
            ['title' => 'Business analysis and data control', 'text' => 'Measurement systems that accurately track every traffic source, real conversion and return.'],
            ['title' => 'Paid ads in the main channels (Google, Meta, TikTok)', 'text' => 'Planning, setting up and managing targeted campaigns on Google, Facebook, Instagram and TikTok, focused on generating enquiries and sales directly.'],
            ['title' => 'Advertising and positioning on AI platforms', 'text' => 'Launching and managing ad formats in the new generation of AI systems (OpenAI, Perplexity) for an early advantage and access to high-intent traffic.'],
            ['title' => 'Search and AI search optimisation (SEO & AIO)', 'text' => 'Precise data structuring for priority organic visibility in Google Search, Google Maps and AI assistants (ChatGPT, Gemini).'],
            ['title' => 'Marketing process automation', 'text' => 'Automated email communication for follow-up engagement, retention and more repeat sales.'],
            ['title' => 'Strategic content and positioning', 'text' => 'Targeted business texts that establish your brand\'s authority and put you ahead of the competition in your sector.'],
        ],
    ],

    'home_process' => [
        'title' => 'A managed, structured path. From concept to market launch',
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

    'home_trust' => [
        'eyebrow' => '',
        'proof_label' => '',
        'proof' => [
            ['value' => 'Google + Meta', 'text' => 'ad tracking is set up: you measure every enquiry and sale', 'link' => ''],
            ['value' => 'Instantly', 'text' => 'every enquiry goes to your email, phone or CRM', 'link' => ''],
            ['value' => '1 click', 'text' => 'you change texts and offers yourself, without waiting for anyone', 'link' => ''],
            ['value' => 'Conversion', 'text' => 'design and structure built to turn visitors into customers', 'link' => ''],
        ],
        'engine_eyebrow' => 'The digital engine',
        'engine_center' => 'Digital engine',
        'funnel' => [
            ['card' => 'geo-seo', 'stage' => 'They find you', 'does' => 'People find you on Google, Google Maps and AI assistants when they search for what you offer.', 'without' => 'Nobody reaches the website. The site, the forms and the emails sit empty, however good they are.'],
            ['card' => 'websites', 'stage' => 'They visit the site', 'does' => 'The website welcomes them: fast, clear and easy on a phone, with prices and answers to their questions.', 'without' => 'People find you but leave. Google pushes slow and awkward sites down, so visibility drops too.'],
            ['card' => 'email', 'stage' => 'They enquire and buy', 'does' => 'A call button, a short form and automatic emails turn a visit into an enquiry and a customer who comes back.', 'without' => 'Visits do not become customers. Visibility and the website run idle, and the advertising is paid for nothing.'],
            ['card' => 'monitoring', 'stage' => 'They reach you', 'does' => 'Maintenance keeps the site online and protected, and every enquiry reaches your phone and email.', 'without' => 'A site that is down or hacked: Google drops it, the forms stop sending and everything above it stops.'],
        ],
        'fit_eyebrow' => 'Our values',
        'fit_title' => 'We work for the growth of your business.',
        'fit_text' => 'We take on projects where we can guarantee a direct effect on the growth of the business. We work with businesses that have a product, a service or a dream, but need to be seen by more people.',
        // Надпис над списъка (по желание).
        'fit_list_label' => 'We partner with:',
        'fit_items' => [
            ['title' => 'Businesses with proven quality and limited online reach', 'text' => 'Companies with an excellent reputation on the ground that lose market share to competitors only because they lack precise digital visibility in search engines and AI platforms.'],
            ['title' => 'Sales-driven teams focused on pure conversion', 'text' => 'Organisations for which the website is the main tool for generating qualified deals, quotes and direct sales.'],
            ['title' => 'Leaders who invest against a clear return (ROI)', 'text' => 'Owners who require an exact report on every euro invested, full control of the data and strict measurement of the final profit.'],
            ['title' => 'E-commerce brands seeking maximum margin', 'text' => 'Online shops that aim to raise the average basket value and the revenue from every customer.'],
        ],
        'principles_eyebrow' => 'How we think',
        'principles_title' => 'One system instead of five contractors.',
        'principles_text' => 'We have been doing this for 4 years. The website, Google, emails and maintenance work together when one team builds them.',
        'principle_items' => [
            ['title' => 'Customer first, decoration second', 'text' => 'Every page leads to one action: a call, an inquiry or a purchase. The design serves that.'],
            ['title' => 'We measure, we don\'t guess', 'text' => 'Every website starts with Google Analytics and Search Console. You see where inquiries come from.'],
            ['title' => 'We are with you all the way to launch', 'text' => 'You work directly with the people who build your website and marketing. Your questions don\'t go through account managers.'],
            ['title' => 'Everything is in your name', 'text' => 'The domain, profiles and access are yours from day one. If you ever leave, you take everything.'],
        ],
        'demos_eyebrow' => 'Industries',
        'demos_title' => 'We have thought about your industry.',
        'demos_text' => 'We built websites for businesses from different industries. Each of them solves the problems of its industry.',
        'blog_eyebrow' => 'Insights and advice',
        'blog_title' => 'We write about what works.',
        'blog_text' => 'Practical articles about websites, Google, AI search and email.',
        'contact_eyebrow' => 'The next step',
    ],

    'service_pages' => [
        'website' => [
            'slug' => 'website-design', 'icon' => 'code', 'menu' => 'Website design',
            'title' => 'A website that sells',
            'seo_title' => 'Website design for your business | Creatium Lab',
            'seo_description' => 'A fast, modern website with enquiry forms, bookings and online payments. Demo preview before launch, fixed price from €450.',
            'lead' => 'A fast, modern website that turns visitors into enquiries. You see it on a demo before it goes live.',
            'problems' => ['The site is slow or breaks on phones', 'People can\'t find how to call or write to you', 'Every change waits weeks for a developer'],
            'includes' => [
                'Mobile-first design that loads in seconds',
                'Enquiry forms, call button and Google Maps',
                'Bookings and online payments (Stripe, myPOS, Apple Pay)',
                'Business email on your own domain',
                'Easy editing of text, prices and photos',
                'Demo preview: we launch after your approval',
            ],
            'result' => 'A website that works like a salesperson 24/7, collecting enquiries while you work.',
            'package' => 0,
            'faq' => [
                ['How long does it take?', 'Usually 1–2 weeks, depending on the content. The timeline is in the proposal before we start.'],
                ['Can I change the text myself?', 'Yes, from a simple panel. If you have no time, changes are included in the monthly care plan.'],
                ['What if I don\'t like it?', 'That is what the demo is for: we make the changes and launch only after you approve.'],
            ],
        ],
        'seo' => [
            'slug' => 'seo-and-ai-visibility', 'icon' => 'magnifying-glass', 'menu' => 'SEO and AI visibility',
            'title' => 'Visibility in Google, Google Maps and AI assistants',
            'seo_title' => 'SEO, local SEO and visibility in ChatGPT | Creatium Lab',
            'seo_description' => 'Show up in Google, Google Maps and in ChatGPT and Gemini answers when customers search for what you offer.',
            'lead' => 'People now ask Google and ChatGPT for "the best one nearby". We make sure the answer is you.',
            'problems' => ['Competitors show up before you on Google Maps', 'You have a website, but nobody finds it', 'You don\'t know where customers come from'],
            'includes' => [
                'Local SEO: Google Maps and "near me" searches',
                'Optimisation for AI search (ChatGPT, Gemini)',
                'Structured business data (schema)',
                'Google Analytics and enquiry tracking',
                'Content and articles that bring buyers',
                'Monthly report: rankings, enquiries and how AI assistants describe you',
            ],
            'result' => 'More people find you on their own, exactly when they look for your service.',
            'package' => 1,
            'faq' => [
                ['When will I see results?', 'The first Google Maps improvements often show within 1–2 months. SEO compounds: each month builds on the last.'],
                ['What is AI visibility?', 'AI assistants recommend businesses that clearly say what they do and where. We structure your site and profiles so they understand it.'],
                ['Do you guarantee first place?', 'No honest specialist guarantees a ranking. We guarantee the work and a clear monthly report of what changed.'],
            ],
        ],
        'google' => [
            'slug' => 'google-business-profile', 'icon' => 'location-dot', 'menu' => 'Google profile and reviews',
            'title' => 'Google Business Profile and reviews',
            'seo_title' => 'Google Business Profile and review management | Creatium Lab',
            'seo_description' => 'A complete Google Business Profile, a reply to every review and more new reviews. Rank higher on Google Maps.',
            'lead' => 'For many customers the first impression is not your website but your Google Maps profile. We make it win.',
            'problems' => ['Reviews go unanswered and bad ones scare new customers away', 'Opening hours, photos or phone are out of date', 'A competitor with a better profile shows up first'],
            'includes' => [
                'Full Google Business Profile setup',
                'A reply to every review, good or bad, within 1–2 days',
                'Photos, services, prices and hours always current',
                'Profile posts with offers and news',
                'A QR code and link for leaving a review',
                'Monthly report: views, calls, directions',
            ],
            'result' => 'A profile that looks active and trustworthy, ranks higher on Google Maps and brings calls.',
            'package' => 1,
            'note' => 'Also available as a separate monthly service; pricing after a short call.',
            'faq' => [
                ['Why reply to reviews?', 'Google and people see which businesses reply. A calm reply to a bad review shows the next customer that you care.'],
                ['Will you write on my behalf?', 'Yes, in your business\'s tone. First we agree on what we can and cannot promise.'],
                ['Can you buy reviews?', 'No. Fake reviews break Google\'s rules. We help you collect real reviews from happy customers.'],
            ],
        ],
        'email' => [
            'slug' => 'email-marketing', 'icon' => 'envelope-open-text', 'menu' => 'Email marketing',
            'title' => 'Email marketing and automation',
            'seo_title' => 'Email marketing and automation for your business | Creatium Lab',
            'seo_description' => 'Automatic emails after an enquiry, booking or purchase. Bring customers back without manual work.',
            'lead' => 'Finding a new customer is expensive. Email brings back the ones who already know you, automatically.',
            'problems' => ['Customers come once and forget you', 'You answer enquiries by hand, and late', 'Your contact list sits unused'],
            'includes' => [
                'Forms and a pop-up that collect subscribers',
                'Welcome series and an automatic reply to enquiries',
                'Abandoned cart reminders',
                'Post-purchase emails: tips, add-ons, reviews',
                'Campaigns in Brevo, Mailchimp or Klaviyo',
                'Inbox, not spam, and a sales report',
            ],
            'result' => 'Customers who come back, and enquiries answered right away, even on a day off.',
            'package' => 1,
            'faq' => [
                ['I have no email list. Is it worth it?', 'Yes. We start by collecting: a form on the site, a sign-up discount, an email after purchase.'],
                ['Won\'t we annoy customers?', 'We write rarely and usefully, with an unsubscribe link in every email. That is why people stay.'],
                ['Is it GDPR compliant?', 'Yes: consent, one-click unsubscribe and only the data that is needed.'],
            ],
        ],
        'support' => [
            'slug' => 'maintenance-and-hosting', 'icon' => 'shield-halved', 'menu' => 'Maintenance and hosting',
            'title' => 'Maintenance, hosting and monitoring',
            'seo_title' => 'Website maintenance, hosting and 24/7 monitoring | Creatium Lab',
            'seo_description' => '24/7 monitoring, daily backups, security and fast response. Hosting, domain and site changes in one monthly plan.',
            'lead' => 'Your website works every day, and we look after it. You don\'t have to think about the tech.',
            'problems' => ['The site goes down and you hear it from a customer', 'An expired domain or certificate', 'Prices need changing and nobody can do it'],
            'includes' => [
                '24/7 monitoring and fast response to outages',
                'Daily backups',
                'Protection from attacks and spam',
                'Updates and speed upkeep',
                'Hosting, domain and SSL certificate',
                'Changes to prices, offers and text',
            ],
            'result' => 'Peace of mind: the site is fast, secure and current, and you have someone to call.',
            'package' => 0,
            'faq' => [
                ['Do you maintain sites you didn\'t build?', 'Yes, after a short review. If there are problems, we say so openly before taking it over.'],
                ['How fast do you respond?', 'For an outage, as soon as monitoring alerts us. Content changes usually within 1–2 working days.'],
                ['What does the monthly price include?', 'Monitoring, backups, updates and regular content changes. The exact scope is in the package.'],
            ],
        ],
    ],

    'services_page' => [
        'title' => 'The architecture of your digital success',
        'intro' => 'We do not sell fragmented services. We put in place the 4 essential elements that turn your online presence into a working sales channel.',
        'cards' => [
            [
                'slug' => 'websites', 'icon' => 'code', 'label' => 'Web architecture and infrastructure (the web foundation)',
                'title' => 'A web platform designed to sell',
                'tag' => 'Speed, looks and mobile compatibility without compromise.',
                'description' => 'We build modern websites shaped around how your buyers behave. Lightning-fast loading, intuitive design and easy one-click payment.',
                'includes' => ['Online payment integrations (Stripe, myPOS, Apple Pay)', 'One-click sign-in with Google and Facebook', 'Mobile optimisation for maximum conversion'],
            ],
            [
                'slug' => 'geo-seo', 'icon' => 'magnifying-glass', 'label' => 'Business analysis and SEO/GEO positioning (visibility and discoverability)',
                'title' => 'GEO, SEO and AI positioning',
                'tag' => 'Be the first choice in Google and the new AI search engines.',
                'description' => 'Search is changing. We structure your website so it is easy to find locally (Google Maps) and recommended by AI assistants (ChatGPT, Gemini).',
                'includes' => ['Google Analytics setup and full traffic tracking', 'Local SEO and a Google Business profile', 'Optimisation for AI-driven search (AIO)'],
            ],
            [
                'slug' => 'email', 'icon' => 'envelope-open-text', 'label' => 'Marketing automation and conversions (retention and sales)',
                'title' => 'Sales automation',
                'tag' => 'Turn one-time visitors into loyal customers.',
                'description' => 'We build smart email systems that talk to your customers at the moments that matter – automatically, without manual work from you.',
                'includes' => ['Automated email funnels after an enquiry or purchase', 'Setup of leading platforms (Klaviyo, Mailchimp, Brevo)', 'Guaranteed deliverability straight to the inbox'],
            ],
            [
                'slug' => 'monitoring', 'icon' => 'shield-halved', 'label' => 'Operational security and continuity (support)',
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
    'contact_goals' => [
        'website' => 'A new or better website',
        'google' => 'More customers from Google and Google Maps',
        'reviews' => 'Google profile and reviews',
        'ai' => 'Visibility in AI assistants (ChatGPT, Gemini)',
        'email' => 'Emails to customers',
        'support' => 'Maintenance, security and speed',
    ],
    'contact_timelines' => [
        'asap' => 'As soon as possible',
        'month' => 'Within a month',
        'quarter' => 'In the next 3 months',
        'exploring' => 'Just exploring',
    ],

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
        'intro' => 'You are not just buying a website. You get a team that grows it and brings customers every month.',
        'items' => [
            [
                'name' => 'Starter Engine',
                'badge' => '',
                'highlighted' => false,
                'audience' => 'A professional online presence that brings your first enquiries.',
                'prices' => ['€450 website build', '€150 / month support, security and basic marketing'],
                'build_title' => 'Initial build',
                'build_items' => [
                    'A website with your brand: Design built on your identity, fast on every phone.',
                    'Ready for customers: Call button, enquiry form, map and business email.',
                    'Demo before launch: You see the website and approve it before it goes live.',
                ],
                'monthly_title' => 'Every month after that we take care of',
                'monthly_items' => [
                    'Care and security: Monitoring, backups and protection every day.',
                    'Changes when you need them: We update prices, offers and photos for you.',
                    'Monthly report: How many people come and from where.',
                ],
            ],
            [
                'name' => 'Active Marketing & Growth',
                'badge' => 'Most popular',
                'highlighted' => true,
                'audience' => 'For a business that wants to grow: marketing, content and online sales.',
                'prices' => ['€850 first month: website and marketing launch', '€400 / month active marketing and support'],
                'build_title' => 'In the first month we build and launch',
                'build_items' => [
                    'Everything in Starter Engine: Plus online payments and bookings.',
                    'Every customer measured: You see which channel brings enquiries and sales.',
                    'Visibility on Google and AI: A Google Maps profile and content that search engines and ChatGPT understand.',
                ],
                'monthly_title' => 'Every month after that we do for your business',
                'monthly_items' => [
                    'Strategy and brand: A campaign plan and brand development.',
                    'Content that sells: Texts, articles and posts for your customers.',
                    'Emails that bring customers back: Automatic emails after an enquiry or purchase.',
                    'Google profile and reviews: An up-to-date profile and a reply to every review.',
                    'Full website care: Security, monitoring and fast response.',
                ],
            ],
            [
                'name' => 'Custom Marketing Scale',
                'badge' => '',
                'highlighted' => false,
                'audience' => 'For companies with more processes, systems and marketing volume.',
                'prices' => ['A custom quote and a subscription based on scale'],
                'build_title' => 'Includes',
                'build_items' => [
                    'A tailored solution: A website connected to your CRM, warehouse or ERP.',
                    'A full marketing team: Strategy, content, advertising and analytics.',
                    'Priority care: Regular meetings and a direct line to us.',
                ],
                'monthly_title' => '',
                'monthly_items' => [],
            ],
        ],
        'button' => 'Get a quote',
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
