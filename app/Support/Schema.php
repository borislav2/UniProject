<?php

namespace App\Support;

use App\Models\Post;
use Illuminate\Support\Str;

/**
 * Structured data (schema.org JSON-LD) shared by the public pages, so every page describes the same business and the same people.
 * One entity per thing, linked by @id: the Organization (ProfessionalService) on the home page, the WebSite, a Person for each team member
 * on /za-nas, and the Service, BlogPosting and BreadcrumbList nodes that point back to them.
 *
 * The phone, the price range and the contact details are the ones the visitor sees: design G shows one phone (`g_phones()`),
 * the current design lists all of them. Nothing here claims reviews, ratings, awards or clients.
 */
class Schema
{
    /** The default share image (Open Graph, Twitter card, JSON-LD image): design G, 1200 x 630, English text on /en. Made by tools/og. */
    public static function shareImage(): string
    {
        return asset(app()->getLocale() === 'en' ? 'images/og-g-en.png' : 'images/og-g.png');
    }

    /** The logo for JSON-LD: the G logo (PNG, crawlers do not read WebP logos reliably); the old design keeps its own file. */
    public static function logoUrl(): string
    {
        return asset(Design::is('g') ? 'images/logo-g.png' : 'images/logo.webp');
    }

    /** The language-neutral address of the business entity (the Bulgarian home page). */
    public static function organizationId(): string
    {
        return rtrim(lroute('home', [], 'bg'), '/') . '/#organization';
    }

    public static function websiteId(): string
    {
        return rtrim(lroute('home', [], 'bg'), '/') . '/#website';
    }

    /** The phones shown on the site: one in design G (owners, 13.11.2026), every phone of `contact.phones` in the current design. */
    public static function phones(): array
    {
        return \App\Support\Design::is('g') ? g_phones() : array_values((array) config('creatium.contact.phones'));
    }

    /** „€500 – €1200+“ in both designs (the monthly figures of the packages), taken from the package figures themselves. */
    public static function priceRange(): string
    {
        $plans = app(Content::class)->withDefaults('plans');
        if (\App\Support\Design::is('g')) {
            $from = collect((array) ($plans['g_items'] ?? []))->map(fn ($p) => self::number($p['monthly'] ?? null))->filter()->values();
        } else {
            $from = collect(array_values((array) ($plans['items'] ?? [])))->map(fn ($p) => PlanBilling::monthlyPrice((array) $p))->filter()->values();
        }
        if ($from->isEmpty()) {
            return '';
        }

        return '€' . $from->min() . ' – €' . $from->max() . '+';
    }

    public static function organization(): array
    {
        $contact = (array) config('creatium.contact');
        $legal = (array) config('creatium.legal');
        $phones = self::phones();
        $address = array_filter([
            '@type' => 'PostalAddress',
            'streetAddress' => $legal['address'] ?? null,
            'addressLocality' => __($contact['city']),
            'addressCountry' => 'BG',
        ]);

        return array_filter([
            '@' . 'context' => 'https://schema.org',
            '@type' => 'ProfessionalService',
            '@id' => self::organizationId(),
            'name' => 'Creatium Lab',
            'url' => lroute('home'),
            'logo' => self::logoUrl(),
            'image' => self::shareImage(),
            'alternateName' => __('Creatium Lab агенция'),
            'description' => __('Creatium Lab е агенция за уеб сайтове и дигитален маркетинг в София, България. Правим сайтове за бизнеси и се грижим хората да ги намират в Google.'),
            'email' => $contact['email'] ?? null,
            'telephone' => $phones[0] ?? null,
            'address' => $address,
            'areaServed' => ['@type' => 'Country', 'name' => __('България')],
            'contactPoint' => array_filter([
                '@type' => 'ContactPoint',
                'contactType' => 'customer service',
                'email' => $contact['email'] ?? null,
                'telephone' => $phones[0] ?? null,
                'areaServed' => 'BG',
                'availableLanguage' => ['bg', 'en'],
            ]),
            'sameAs' => array_values(array_filter([$contact['facebook'] ?? null, $contact['linkedin'] ?? null])),
            'priceRange' => self::priceRange(),
            'knowsAbout' => [
                __('Изработка на сайт'), 'SEO', __('Техническо SEO'), __('Google Business профил'), __('Имейл маркетинг'), __('Имейл автоматизации'),
                __('Видимост в AI асистенти'), __('Оптимизация за AI търсене (GEO)'), 'Google Ads', 'Meta Ads', 'Google Shopping', __('AI агенти'),
            ],
            'slogan' => filled(site('home_hero')['title'] ?? null) ? trim((string) site('home_hero')['title']) : null,
            'founder' => self::teamRefs(array_slice(self::teamMembers(), 0, 2)),
            'employee' => self::teamRefs(array_slice(self::teamMembers(), 2)),
            'hasOfferCatalog' => self::offerCatalog(),
            'numberOfEmployees' => self::numberOfEmployees(),
        ]);
    }

    /** The team size as a QuantitativeValue: the number of team members, the same count the visible team line and llms.txt state. */
    private static function numberOfEmployees(): ?array
    {
        $count = Geo\TeamFacts::count();

        return $count > 0 ? ['@type' => 'QuantitativeValue', 'value' => $count] : null;
    }

    /** Team members with a name, in the order of the team block: the first two are the founders (the same rule as the Kreo knowledge). */
    private static function teamMembers(): array
    {
        return array_values(array_filter((array) site('team'), fn ($m) => filled($m['name'] ?? null)));
    }

    /** References to the Person nodes of /za-nas (same @id), without repeating the data. */
    private static function teamRefs(array $members): array
    {
        return array_map(fn ($m) => ['@type' => 'Person', '@id' => self::personId((array) $m), 'name' => trim((string) $m['name'])], $members);
    }

    /** The services as an OfferCatalog: name, address and lead of each service page. No prices (the packages carry them), no invented offers. */
    public static function offerCatalog(): ?array
    {
        $items = [];
        foreach ((array) site('service_pages') as $key => $service) {
            if (! empty($service['g_only']) && ! Design::is('g')) {
                continue;
            }
            if (! filled($service['title'] ?? null)) {
                continue;
            }
            $items[] = [
                '@type' => 'Offer',
                'itemOffered' => array_filter([
                    '@type' => 'Service',
                    'name' => $service['title'],
                    'url' => \App\Http\Controllers\PageController::serviceUrl($key),
                    'description' => $service['lead'] ?? null,
                    'provider' => ['@id' => self::organizationId()],
                ]),
            ];
        }

        return $items ? ['@type' => 'OfferCatalog', 'name' => __('Услуги'), 'itemListElement' => $items] : null;
    }

    /**
     * The three packages of design G as an OfferCatalog: one Service per plan with its monthly price as a UnitPriceSpecification
     * (EUR, per month, P1M) and the yearly price (10 months, P1Y) as a second one. A plan marked „от“ states `minPrice`, as the
     * page does. The figures are the ones of `plans.g_items`, nothing is added. Null when no plan has a monthly price.
     */
    public static function packages(array $plans): ?array
    {
        $items = [];
        foreach (GPlans::cards($plans) as $card) {
            $item = (array) (($plans['g_items'] ?? [])[$card['index']] ?? []);
            $monthly = self::number($item['monthly'] ?? null);
            if ($monthly === null) {
                continue;
            }
            $key = filled($card['from']) ? 'minPrice' : 'price';
            $specs = [[
                '@type' => 'UnitPriceSpecification', $key => $monthly, 'priceCurrency' => 'EUR', 'unitCode' => 'MON', 'billingDuration' => 'P1M',
            ]];
            if (($yearly = self::number($item['yearly'] ?? null)) !== null) {
                $specs[] = ['@type' => 'UnitPriceSpecification', $key => $yearly, 'priceCurrency' => 'EUR', 'unitCode' => 'ANN', 'billingDuration' => 'P1Y'];
            }
            $items[] = [
                '@type' => 'Service',
                'name' => GPlans::label($card),
                'description' => $card['audience'] !== '' ? $card['audience'] : null,
                'url' => lroute('packages'),
                'provider' => ['@id' => self::organizationId()],
                'areaServed' => ['@type' => 'Country', 'name' => __('България')],
                'offers' => ['@type' => 'Offer', 'priceCurrency' => 'EUR', 'url' => lroute('packages'), 'priceSpecification' => $specs],
            ];
        }
        $items = array_map(fn ($i) => array_filter($i, fn ($v) => $v !== null), $items);

        return $items ? [
            '@' . 'context' => 'https://schema.org',
            '@type' => 'OfferCatalog',
            'name' => (string) ($plans['g_title'] ?? __('План за всеки етап от вашия бизнес')),
            'url' => lroute('packages'),
            'itemListElement' => $items,
        ] : null;
    }

    public static function website(): array
    {
        return [
            '@' . 'context' => 'https://schema.org',
            '@type' => 'WebSite',
            '@id' => self::websiteId(),
            'name' => 'Creatium Lab',
            'url' => lroute('home'),
            'inLanguage' => app()->getLocale() === 'en' ? 'en' : 'bg',
            'publisher' => ['@id' => self::organizationId()],
        ];
    }

    /** The provider of a Service: the same entity as on the home page, with name and address so it stands alone for a reader of this page only. */
    public static function provider(): array
    {
        return ['@type' => 'Organization', '@id' => self::organizationId(), 'name' => 'Creatium Lab', 'url' => lroute('home')];
    }

    /**
     * The @id of a Person: /za-nas#{transliterated Bulgarian name}, the same on the Bulgarian and the English page (a photo file name
     * or an English spelling must never change it). An English member is matched to the Bulgarian one at the same place in the team.
     */
    public static function personId(array $member): string
    {
        $name = trim((string) ($member['name'] ?? ''));
        if (app()->getLocale() !== 'bg') {
            $team = array_values((array) site('team'));
            $bulgarian = array_values((array) app(Content::class)->get('team', 'bg'));
            $index = null;
            foreach ($team as $i => $m) {
                if ($name !== '' && trim((string) (((array) $m)['name'] ?? '')) === $name) {
                    $index = $i;
                    break;
                }
            }
            $bgName = trim((string) (((array) ($bulgarian[$index] ?? []))['name'] ?? ''));
            $name = $bgName !== '' ? $bgName : $name;
        }

        // Official Bulgarian transliteration for the letters Str::slug renders differently (Цончев = tsonchev, not conchev).
        $name = strtr($name, ['Ц' => 'Ts', 'ц' => 'ts', 'Щ' => 'Sht', 'щ' => 'sht', 'Ж' => 'Zh', 'ж' => 'zh', 'Ч' => 'Ch', 'ч' => 'ch', 'Ш' => 'Sh', 'ш' => 'sh', 'Ю' => 'Yu', 'ю' => 'yu', 'Я' => 'Ya', 'я' => 'ya', 'Ъ' => 'A', 'ъ' => 'a']);

        return rtrim(lroute('about', [], 'bg'), '/') . '#' . Str::slug($name, '-', 'bg');
    }

    /** A team member as a Person: real name, role, photo and LinkedIn profile (when the team entry has one). */
    public static function person(array $member): ?array
    {
        $name = trim((string) ($member['name'] ?? ''));
        if ($name === '') {
            return null;
        }
        $role = trim((string) (\App\Support\Design::is('g') && filled($member['g_role'] ?? null) ? $member['g_role'] : ($member['role'] ?? '')));

        return array_filter([
            '@type' => 'Person',
            '@id' => self::personId($member),
            'name' => $name,
            'jobTitle' => $role,
            'image' => filled($member['image'] ?? null) && is_file(public_path($member['image'])) ? asset($member['image']) : null,
            'url' => lroute('about'),
            'worksFor' => ['@type' => 'Organization', '@id' => self::organizationId(), 'name' => 'Creatium Lab'],
            'sameAs' => array_values(array_filter([$member['linkedin'] ?? null])),
        ]);
    }

    /** All team members for the About page (one @graph). */
    public static function team(array $team): array
    {
        return [
            '@' . 'context' => 'https://schema.org',
            '@graph' => array_values(array_filter(array_map(fn ($member) => self::person((array) $member), $team))),
        ];
    }

    /**
     * The author of a blog post: the team member whose name matches the user who wrote it. A post written from a shared account
     * (such as „Админ“) has no real person behind it, so it is credited to the company instead of an invented author.
     */
    public static function postAuthor(Post $post): array
    {
        return self::personForUser((string) $post->author?->name)
            ?? self::personForCategory($post)
            ?? ['@type' => 'Organization', '@id' => self::organizationId(), 'name' => 'Creatium Lab', 'url' => lroute('home')];
    }

    /** The team member who writes in the category of the post (config `blog_authors`, by the Bulgarian slug), or null for no or an unknown category. */
    public static function personForCategory(Post $post): ?array
    {
        $slug = (string) $post->category?->tr('slug', 'bg');
        $first = (string) (config('creatium.blog_authors')[$slug] ?? '');

        return $first === '' ? null : self::personForUser($first);
    }

    /** The Person of the team member whose first name matches the user's name, or null (shared accounts such as „Админ“ match nobody). */
    public static function personForUser(string $userName): ?array
    {
        $name = trim($userName);
        if ($name === '') {
            return null;
        }
        $wanted = Str::lower(Str::before($name, ' '));
        $team = (array) site('team');
        // The user names and the blog_authors config are Bulgarian, so the first name is also looked up in the Bulgarian team and the
        // member at the same place in the page language is returned (an English post page shows „Borislav“, not nobody).
        $bulgarian = app()->getLocale() === 'bg' ? $team : (array) app(\App\Support\Content::class)->get('team', 'bg');
        foreach ($bulgarian as $i => $member) {
            $first = Str::lower(Str::before((string) (((array) $member)['name'] ?? ''), ' '));
            if ($first !== '' && $first === $wanted) {
                $person = self::person((array) ($team[$i] ?? $member));
                if ($person) {
                    return $person;
                }
            }
        }
        foreach ($team as $member) {
            $first = Str::lower(Str::before((string) ($member['name'] ?? ''), ' '));
            if ($first !== '' && $first === $wanted) {
                $person = self::person((array) $member);
                if ($person) {
                    return $person;
                }
            }
        }

        return null;
    }

    private static function number(mixed $value): ?int
    {
        if (is_numeric($value)) {
            return (int) $value;
        }
        if (is_string($value) && preg_match('/\d[\d\s\x{00A0}\x{202F}]*/u', $value, $m)) {
            return (int) preg_replace('/\D/', '', $m[0]);
        }

        return null;
    }
}
