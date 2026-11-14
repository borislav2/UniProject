<?php

namespace App\Support\Admin;

/**
 * Профилите в админа: един източник за менюто, „Днес“ и долното меню на телефона.
 *
 * Профилът решава само какво е в основното меню и какво показва „Днес“. Нищо не е заключено: разделите извън профила
 * са в сгънатата група „Всички раздели“ и в търсенето (Ctrl+K), а правата си остават както са (потребителите са само за роля admin).
 *
 * Елемент от менюто: ['route', 'match' => [маски за routeIs], 'icon', 'label', 'badge' => ключ от Badges или null].
 */
class Profiles
{
    public const DEFAULT = 'all';

    public const PROFILES = [
        'all' => [
            'label' => 'Админ',
            'description' => 'Общият акаунт: вижда всичко, всички раздели в менюто и целия екип в „Днес“.',
        ],
        'dev' => [
            'label' => 'Сайтове и техника',
            'description' => 'Проекти, абонаменти и наблюдение на сайтовете на клиентите, проверки на сайт и здраве на системата.',
        ],
        'sales' => [
            'label' => 'Маркетинг и продажби',
            'description' => 'Запитвания, потенциални клиенти и обаждания, чат разговори, статистика и SEO.',
        ],
        'brand' => [
            'label' => 'Бранд и съдържание',
            'description' => 'Блог, имейли до клиенти, LinkedIn, отговори на отзиви и текстовете по страниците.',
        ],
    ];

    /** Всички раздели на админа по групи; профилите избират от тях. Ключът е името на маршрута. */
    private static function catalog(): array
    {
        $item = fn (string $route, $match, string $icon, string $label, ?string $badge = null) => [
            'route' => $route, 'match' => (array) $match, 'icon' => $icon, 'label' => $label, 'badge' => $badge,
        ];

        return [
            'today' => $item('admin.today', 'admin.today', 'calendar', 'Днес', 'today'),
            'dashboard' => $item('admin.dashboard', 'admin.dashboard', 'tachometer-alt', 'Табло'),
            'inquiries' => $item('admin.inquiries.index', 'admin.inquiries.*', 'envelope', 'Запитвания', 'inquiries'),
            'site-checks' => $item('admin.site-checks.index', 'admin.site-checks.*', 'globe', 'Проверки на сайт', 'site_checks'),
            'projects' => $item('admin.projects.index', 'admin.projects.*', 'project-diagram', 'Проекти'),
            'clients' => $item('admin.clients.index', 'admin.clients.*', 'users', 'Абонаменти', 'late_payments'),
            'client-emails' => $item('admin.client-emails.index', 'admin.client-emails.*', 'paper-plane', 'Имейли до клиенти'),
            'chats' => $item('admin.chats.index', 'admin.chats.*', 'quote-left', 'Чат разговори'),
            'outreach' => $item('admin.outreach.index', ['admin.outreach.index', 'admin.outreach.compose', 'admin.outreach.import*', 'admin.outreach.check', 'admin.outreach.follow-up*', 'admin.outreach.send*', 'admin.outreach.lead'], 'magnifying-glass', 'Потенциални клиенти'),
            'outreach-search' => $item('admin.outreach.search.index', 'admin.outreach.search.*', 'search', 'Търсене на бизнеси'),
            'outreach-calls' => $item('admin.outreach.calls.index', ['admin.outreach.calls.index', 'admin.outreach.calls.import', 'admin.outreach.calls.update', 'admin.outreach.calls.lead'], 'phone', 'Без сайт, за обаждане'),
            'calls-next' => $item('admin.outreach.calls.next', ['admin.outreach.calls.next', 'admin.outreach.calls.back', 'admin.outreach.calls.skip'], 'phone-volume', 'Следващо обаждане', 'callbacks'),
            'linkedin' => $item('admin.linkedin.index', 'admin.linkedin.*', 'linkedin', 'LinkedIn', 'linkedin_due'),
            'stats' => $item('admin.stats', 'admin.stats', 'chart-line', 'Статистика'),
            'reviews' => $item('admin.reviews.index', 'admin.reviews.*', 'reply', 'Отговори на отзиви'),
            'posts' => $item('admin.posts.index', 'admin.posts.*', 'newspaper', 'Блог'),
            'post-categories' => $item('admin.post-categories.index', 'admin.post-categories.*', 'tags', 'Категории на блога'),
            'content' => $item('admin.content.index', 'admin.content.*', 'pen-to-square', 'Текстове по страниците'),
            'seo' => $item('admin.seo.index', 'admin.seo.*', 'magnifying-glass', 'SEO'),
            'categories' => $item('admin.categories.index', 'admin.categories.*', 'tags', 'Категории на проекти'),
            'technologies' => $item('admin.technologies.index', 'admin.technologies.*', 'cogs', 'Технологии'),
            'users' => $item('admin.users.index', 'admin.users.*', 'users', 'Потребители'),
            'health' => $item('admin.health', 'admin.health', 'heart-pulse', 'Здраве на системата'),
            'profile' => $item('admin.profile.edit', 'admin.profile.*', 'user-gear', 'Моят профил'),
        ];
    }

    /** Меню на профила all (и редът на „Всички раздели“): групи, както са били в админа. */
    private static function full(): array
    {
        return [
            '' => ['today', 'dashboard'],
            'Клиенти' => ['inquiries', 'site-checks', 'projects', 'clients', 'client-emails', 'chats'],
            'Продажби' => ['outreach', 'outreach-search', 'outreach-calls', 'calls-next', 'linkedin', 'stats', 'reviews'],
            'Сайт' => ['posts', 'post-categories', 'content', 'seo'],
            'Настройки' => ['categories', 'technologies', 'users', 'health', 'profile'],
        ];
    }

    private static function own(): array
    {
        return [
            'dev' => [
                '' => ['today'],
                'Проекти' => ['projects', 'clients', 'site-checks'],
                'Система' => ['health'],
            ],
            'sales' => [
                '' => ['today'],
                'Продажби' => ['inquiries', 'outreach', 'outreach-search', 'outreach-calls', 'calls-next', 'chats'],
                'Анализ' => ['stats', 'dashboard', 'seo'],
            ],
            'brand' => [
                '' => ['today'],
                'Съдържание' => ['posts', 'post-categories', 'content'],
                'Комуникация' => ['client-emails', 'linkedin', 'reviews'],
            ],
        ];
    }

    public static function normalize(?string $key): string
    {
        return isset(self::PROFILES[$key]) ? $key : self::DEFAULT;
    }

    public static function keys(): array
    {
        return array_keys(self::PROFILES);
    }

    public static function label(?string $key): string
    {
        return self::PROFILES[self::normalize($key)]['label'];
    }

    public static function description(?string $key): string
    {
        return self::PROFILES[self::normalize($key)]['description'];
    }

    /** Къде отива човекът след вход. */
    public static function landing(?string $key): string
    {
        return 'admin.today';
    }

    /**
     * Основното меню: групи → елементи. Потребителите се виждат само за роля admin ($canManageUsers).
     *
     * @return array<string, list<array>>
     */
    public static function menu(?string $key, bool $canManageUsers = false): array
    {
        $key = self::normalize($key);
        $structure = $key === 'all' ? self::full() : self::own()[$key];

        return self::build($structure, $canManageUsers);
    }

    /** „Всички раздели“: всичко от менюто на all, което го няма в основното меню на профила (празно за all). */
    public static function others(?string $key, bool $canManageUsers = false): array
    {
        $key = self::normalize($key);
        if ($key === 'all') {
            return [];
        }
        $shown = collect(self::own()[$key])->flatten()->all();
        $structure = collect(self::full())->map(fn ($keys) => array_values(array_diff($keys, $shown)))->filter()->all();

        return self::build($structure, $canManageUsers);
    }

    /** Долното меню на телефона: четири бързи връзки (петата е „Меню“). */
    public static function phoneNav(?string $key): array
    {
        $c = self::catalog();
        $search = ['route' => 'admin.search', 'match' => ['admin.search'], 'icon' => 'search', 'label' => 'Търсене', 'badge' => null];
        $calls = ['label' => 'Обаждания'] + $c['calls-next'];

        return match (self::normalize($key)) {
            'dev' => [$c['today'], $c['projects'], ['label' => 'Абонаменти'] + $c['clients'], $search],
            'sales' => [$c['today'], $c['inquiries'], $calls, $search],
            'brand' => [$c['today'], $c['posts'], ['label' => 'Имейли'] + $c['client-emails'], $search],
            default => [$c['today'], $c['inquiries'], $calls, $search],
        };
    }

    /** „+ Ново“ в заглавната лента: бързите действия на профила [маршрут, текст]. */
    public static function quickActions(?string $key): array
    {
        $project = ['admin.projects.create', 'Проект'];
        $post = ['admin.posts.create', 'Статия в блога'];
        $email = ['admin.client-emails.index', 'Имейл до клиент'];
        $linkedin = ['admin.linkedin.index', 'Контакт в LinkedIn'];
        $review = ['admin.reviews.index', 'Отговор на отзив'];

        return match (self::normalize($key)) {
            'dev' => [$project],
            'sales' => [$project, ['admin.outreach.search.index', 'Търсене на бизнеси'], $linkedin],
            'brand' => [$post, $email, $linkedin, $review],
            default => [$project, $post, $email, $linkedin, $review],
        };
    }

    /**
     * Блоковете на „Днес“ по секции [заглавие => ключове] (вижте Admin\TodayBlocks). Профилът all показва и трите
     * профила накратко, всеки от другите само своя.
     */
    public static function todaySections(?string $key): array
    {
        $sales = ['sales-consultations', 'sales-unread', 'sales-due', 'sales-calls', 'sales-hot', 'sales-followups', 'sales-nostep'];
        $dev = ['dev-projects', 'dev-clients', 'dev-site-checks', 'dev-leads', 'dev-health'];
        $brand = ['brand-posts', 'brand-autopost', 'brand-linkedin', 'brand-reviews', 'brand-reports', 'brand-emails'];

        return match (self::normalize($key)) {
            'dev' => ['' => $dev],
            'sales' => ['' => $sales],
            'brand' => ['' => $brand],
            default => ['Продажби' => $sales, 'Клиенти и сайтове' => array_values(array_diff($dev, ['dev-leads'])), 'Бранд и съдържание' => $brand],
        };
    }

    private static function build(array $structure, bool $canManageUsers): array
    {
        $catalog = self::catalog();
        $menu = [];
        foreach ($structure as $group => $keys) {
            foreach ($keys as $k) {
                if ($k === 'users' && ! $canManageUsers) {
                    continue;
                }
                $menu[$group][] = $catalog[$k];
            }
        }

        return $menu;
    }
}
