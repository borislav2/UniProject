<?php

namespace App\Support\Seo;

use Illuminate\Container\Container;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Vite;
use Illuminate\Http\Request;
use Illuminate\Pagination\PaginationState;
use Illuminate\Support\Facades\Facade;
use Throwable;

/**
 * Нашият сайт, питан отвътре: без нито една заявка по мрежата. Всяка страница минава през същото ядро (маршрути, middleware,
 * шаблони), както при истинско посещение, но в отделна копие на приложението („пясъчник“), така че заявката, сесията,
 * потребителят и езикът на посетителя, който задейства проверката, не се пипат. Базата данни е същата връзка.
 *
 * Фоновите стъпки, които правят неща навън (публикуване във Facebook и LinkedIn, автопилот, възможности, наблюдение на
 * сайтове), са махнати от пясъчника, а сесиите му са в паметта. Ползвайте open() и close() (или with()).
 */
class InternalSite
{
    /** Middleware, които правят нещо навън или пишат в базата след отговора: не са нужни за четене на страница. */
    private const SKIP_MIDDLEWARE = [
        \App\Http\Middleware\PublishBlogToFacebook::class,
        \App\Http\Middleware\PublishBlogToLinkedIn::class,
        \App\Http\Middleware\MonitorClientSites::class,
        \App\Http\Middleware\RunOutreachAutopilot::class,
        \App\Http\Middleware\CollectOpportunities::class,
        \App\Http\Middleware\RunSeoHealth::class,
        \App\Http\Middleware\SubmitToIndexNow::class,
        \App\Http\Middleware\SendSiteCheckFollowUps::class,
    ];

    public const USER_AGENT = 'CreatiumSeoHealth/1.0 (internal)';

    private ?Application $sandbox = null;

    private ?Application $origin = null;

    private ?Kernel $kernel = null;

    private $origResolver;

    private $origDispatcher;

    /** @template T @param callable(self): T $callback @return T */
    public static function with(callable $callback): mixed
    {
        $site = new self;
        $site->open();
        try {
            return $callback($site);
        } finally {
            $site->close();
        }
    }

    public function open(): static
    {
        if ($this->sandbox) {
            return $this;
        }

        $this->origin = Container::getInstance();
        $this->origResolver = \Illuminate\Database\Eloquent\Model::getConnectionResolver();
        $this->origDispatcher = \Illuminate\Database\Eloquent\Model::getEventDispatcher();

        try {
            $app = require base_path('bootstrap/app.php');
            $this->kernel = $app->make(Kernel::class);
            // В production AppServiceProvider::boot() вика URL::forceRootUrl(), а генераторът на адреси иска заявка.
            // Второто приложение още няма такава (handle() не е викан), затова му даваме една с адреса на сайта.
            $app->instance('request', Request::create((string) ($this->origin['config']->get('app.url') ?: 'http://localhost')));
            $this->kernel->bootstrap();
            $this->sandbox = $app;

            // Същите настройки (в тестове се сменят в движение), сесии в паметта, същата връзка с базата и същият Vite.
            $app['config']->set($this->origin['config']->all());
            $app['config']->set('session.driver', 'array');
            $app['config']->set('session.lifetime', 5);
            $app->instance(Vite::class, $this->origin->make(Vite::class));
            $mine = $app['db']->connection();
            $theirs = $this->origin['db']->connection();
            $mine->setPdo($theirs->getPdo());
            $mine->setReadPdo($theirs->getReadPdo());

            $router = $app['router'];
            $groups = $router->getMiddlewareGroups();
            if (isset($groups['web'])) {
                $router->middlewareGroup('web', array_values(array_diff($groups['web'], self::SKIP_MIDDLEWARE)));
            }
        } catch (Throwable $e) {
            $this->close();
            throw $e;
        }

        return $this;
    }

    /** Връща всичко, което второто приложение е пренаписало: контейнера, фасадите, моделите и страницирането. */
    public function close(): void
    {
        if (! $this->origin) {
            return;
        }
        Container::setInstance($this->origin);
        Facade::setFacadeApplication($this->origin);
        Facade::clearResolvedInstances();
        \Illuminate\Database\Eloquent\Model::setConnectionResolver($this->origResolver);
        \Illuminate\Database\Eloquent\Model::setEventDispatcher($this->origDispatcher);
        PaginationState::resolveUsing($this->origin);
        $this->sandbox = $this->kernel = $this->origin = null;
    }

    /**
     * Една страница: ['status', 'location', 'type', 'body'] (без да следва пренасочвания). Адресът е пълен (с APP_URL)
     * или път; чужд хост не се пита, а връща status 0.
     */
    public function get(string $url): array
    {
        $this->open();
        $url = $this->absolute($url);
        if (! $this->isOurs($url)) {
            return ['status' => 0, 'location' => null, 'type' => '', 'body' => ''];
        }

        try {
            $request = Request::create($url, 'GET', [], [], [], [
                'HTTP_ACCEPT' => 'text/html,application/xhtml+xml,application/xml;q=0.9',
                'HTTP_USER_AGENT' => self::USER_AGENT,
            ]);
            $response = $this->kernel->handle($request);
            $body = $response instanceof \Symfony\Component\HttpFoundation\BinaryFileResponse || $response instanceof \Symfony\Component\HttpFoundation\StreamedResponse ? '' : (string) $response->getContent();

            return [
                'status' => $response->getStatusCode(),
                'location' => $response->headers->get('Location'),
                'type' => (string) $response->headers->get('Content-Type'),
                'robots' => (string) $response->headers->get('X-Robots-Tag'),
                'body' => $body,
            ];
        } catch (Throwable $e) {
            return ['status' => 500, 'location' => null, 'type' => '', 'body' => '', 'error' => $e->getMessage()];
        }
    }

    public function root(): string
    {
        return rtrim((string) ($this->origin ?? Container::getInstance())['config']->get('app.url'), '/');
    }

    public function absolute(string $url): string
    {
        if (preg_match('#^https?://#i', $url)) {
            return $url;
        }

        return $this->root() . '/' . ltrim($url, '/');
    }

    public function isOurs(string $url): bool
    {
        $host = strtolower((string) parse_url($url, PHP_URL_HOST));
        $mine = strtolower((string) parse_url($this->root(), PHP_URL_HOST));

        return $host !== '' && ($host === $mine || $host === 'www.' . $mine || 'www.' . $host === $mine);
    }
}
