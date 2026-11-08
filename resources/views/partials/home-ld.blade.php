@php
    $contact = config('creatium.contact');
    $ld = [
        '@context' => 'https://schema.org',
        '@type' => 'ProfessionalService',
        'name' => 'Creatium Lab',
        'url' => lroute('home'),
        'logo' => asset('images/logo.webp'),
        'image' => asset('images/og-image.png'),
        'description' => __('Правим сайтове за бизнеси и се грижим хората да ги намират в Google.'),
        'email' => $contact['email'],
        'telephone' => $contact['phones'][0] ?? null,
        'address' => ['@type' => 'PostalAddress', 'addressLocality' => __($contact['city']), 'addressCountry' => 'BG'],
        'areaServed' => ['@type' => 'Country', 'name' => __('България')],
        'sameAs' => array_values(array_filter([$contact['facebook'] ?? null])),
        'priceRange' => '€400 – €1200+',
        'knowsAbout' => [__('Изработка на сайт'), 'SEO', __('Google Business профил'), __('Имейл маркетинг'), __('Видимост в AI асистенти')],
    ];
    // WebSite: името на сайта, което Google показва над резултата.
    $site = ['@context' => 'https://schema.org', '@type' => 'WebSite', 'name' => 'Creatium Lab', 'url' => lroute('home'), 'inLanguage' => app()->getLocale() === 'en' ? 'en' : 'bg'];
@endphp
<script type="application/ld+json">{!! json_encode(array_filter($ld), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}</script>
<script type="application/ld+json">{!! json_encode($site, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}</script>
