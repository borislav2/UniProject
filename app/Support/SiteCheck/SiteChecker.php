<?php

namespace App\Support\SiteCheck;

use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\Psr7\UriResolver;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

/**
 * Безплатната проверка на сайт (/proverka-na-sait): отваря началната страница и проверява основните неща,
 * които влияят на Google и на клиентите. Връща отчет със статус ok / warn / fail за всяка проверка.
 *
 * Всяка заявка минава през HostResolver (само публични IP адреси) и е закована към намерения адрес,
 * пренасочванията се следват ръчно и се проверяват отново.
 */
class SiteChecker
{
    public const USER_AGENT = 'Mozilla/5.0 (compatible; CreatiumLabSiteCheck/1.0; +https://creatiumlab.com/proverka-na-sait)';

    private const MAX_BYTES = 5_000_000;

    private const MAX_REDIRECTS = 5;

    public function __construct(private HostResolver $resolver)
    {
    }

    /** От „https://www.Example.bg/za-nas“ прави „www.example.bg“. Null, ако не прилича на домейн. */
    public static function normalizeDomain(?string $input): ?string
    {
        $input = mb_strtolower(trim((string) $input));
        if ($input === '') {
            return null;
        }
        if (! preg_match('#^[a-z][a-z0-9+.-]*://#', $input)) {
            $input = 'http://' . $input;
        }

        $host = parse_url($input, PHP_URL_HOST);
        if (! is_string($host) || $host === '') {
            return null;
        }
        $host = rtrim($host, '.');

        if (preg_match('/[^\x20-\x7e]/', $host)) {
            $host = function_exists('idn_to_ascii') ? idn_to_ascii($host, IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46) : false;
            if (! $host) {
                return null;
            }
        }

        if (strlen($host) > 253 || filter_var($host, FILTER_VALIDATE_IP)
            || ! preg_match('/^(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+(?:[a-z]{2,63}|xn--[a-z0-9-]{1,59})$/', $host)) {
            return null;
        }

        $tld = substr($host, strrpos($host, '.') + 1);

        return in_array($tld, ['localhost', 'local', 'internal', 'test', 'example', 'invalid', 'lan', 'home'], true) ? null : $host;
    }

    public function check(string $domain): array
    {
        $https = $this->fetch("https://$domain/");
        if (! $this->isOk($https) && ! str_starts_with($domain, 'www.')) {
            $www = $this->fetch("https://www.$domain/");
            if ($this->isOk($www)) {
                $https = $www;
            }
        }
        $http = $this->fetch("http://$domain/", follow: false);
        $page = $this->isOk($https) ? $https : $this->fetch("http://$domain/");

        $report = ['domain' => $domain, 'checked_at' => now()->toIso8601String(), 'reachable' => $this->isOk($page), 'checks' => []];

        if (! $report['reachable']) {
            $report['checks'][] = $this->item('fail', __('Сайтът не се отваря'), $page['response']
                ? __('Сървърът отговори с код :code вместо със страницата. Посетителите вероятно виждат грешка.', ['code' => $page['response']->status()])
                : __('Не успяхме да се свържем със сайта. Проверете дали домейнът е платен и дали хостингът работи.'));
            $report['score'] = 0;
            $report['url'] = "https://$domain/";

            return $report;
        }

        $report['url'] = $page['url'];
        $html = $page['response']->body();
        $doc = $this->parse($html);
        $xpath = new \DOMXPath($doc);
        $origin = $this->origin($page['url']);

        // 1. HTTPS
        if ($this->isOk($https)) {
            $report['checks'][] = $this->item('ok', __('Защитена връзка (HTTPS)'), __('Сайтът се отваря през HTTPS и браузърът показва катинарче.'));
        } else {
            $report['checks'][] = $this->item('fail', __('Сайтът не работи през HTTPS'), $this->isCertificateError($https['error'])
                ? __('Сертификатът за защитена връзка е невалиден или изтекъл. Браузърите показват голямо предупреждение и повечето хора си тръгват.')
                : __('Браузърите показват „Не е защитено“ до адреса, а данните от формите се изпращат без защита. Сертификатът обикновено е безплатен.'));
        }

        // 2. http:// -> https://
        if ($this->isOk($https)) {
            $location = $http['response']?->header('Location') ?? '';
            $report['checks'][] = $http['response'] && $http['response']->redirect() && str_starts_with(strtolower($location), 'https://')
                ? $this->item('ok', __('Адресът без „s“ води към защитената версия'), __('Който напише http://, автоматично отива на https://.'))
                : $this->item('warn', __('Адресът с http:// не води към https://'), __('Хората, които стигат до сайта по стар линк, го виждат като незащитен. Нужно е едно пренасочване в настройките на сървъра.'));
        }

        // 3. Скорост на отговора
        $seconds = number_format($page['ms'] / 1000, 1, ',', '');
        $report['checks'][] = match (true) {
            $page['ms'] < 800 => $this->item('ok', __('Сървърът отговаря бързо (:s s)', ['s' => $seconds]), __('Първият отговор идва бързо. Цялата скорост на телефон проверете в pagespeed.web.dev.')),
            $page['ms'] < 1800 => $this->item('warn', __('Сървърът отговаря бавно (:s s)', ['s' => $seconds]), __('Страницата започва да се зарежда със закъснение. Причината обикновено е хостингът или тежка тема и добавки.')),
            default => $this->item('fail', __('Сървърът отговаря много бавно (:s s)', ['s' => $seconds]), __('Хората чакат, преди да видят каквото и да е, и част от тях си тръгват. Струва си да проверите хостинга и добавките.')),
        };

        // 4. Мета заглавие
        $title = trim(preg_replace('/\s+/u', ' ', (string) $xpath->evaluate('string(//title)')));
        $titleLength = mb_strlen($title);
        $report['checks'][] = match (true) {
            $titleLength === 0 => $this->item('fail', __('Няма заглавие за Google'), __('Страницата няма мета заглавие (title). Това е синият ред в резултатите на Google.')),
            $titleLength < 15 || $titleLength > 65 => $this->item('warn', __('Заглавието за Google е с неподходяща дължина (:n знака)', ['n' => $titleLength]), __('„:title“. Добрата дължина е около 30–60 знака: какво правите и къде, плюс името на бизнеса.', ['title' => mb_strimwidth($title, 0, 80, '…')])),
            default => $this->item('ok', __('Заглавие за Google'), '„' . $title . '“'),
        };

        // 5. Мета описание
        $description = trim(preg_replace('/\s+/u', ' ', (string) $xpath->evaluate('string(//meta[translate(@name,"DESCRIPTION","description")="description"]/@content)')));
        $descriptionLength = mb_strlen($description);
        $report['checks'][] = match (true) {
            $descriptionLength === 0 => $this->item('fail', __('Няма описание за Google'), __('Липсва мета описание: двата реда текст под заглавието в Google. Без него Google избира откъс сам, често неудачен.')),
            $descriptionLength < 50 || $descriptionLength > 165 => $this->item('warn', __('Описанието за Google е с неподходяща дължина (:n знака)', ['n' => $descriptionLength]), __('Добрата дължина е около 120–155 знака: какво получава клиентът и какво да направи.')),
            default => $this->item('ok', __('Описание за Google'), '„' . mb_strimwidth($description, 0, 170, '…') . '“'),
        };

        // 6. Заглавие H1
        $h1 = $xpath->query('//h1')->length;
        $report['checks'][] = match (true) {
            $h1 === 0 => $this->item('warn', __('Няма основно заглавие (H1)'), __('Страницата няма главно заглавие. То казва на Google и на хората за какво е страницата.')),
            $h1 > 1 => $this->item('warn', __('Няколко основни заглавия (:n × H1)', ['n' => $h1]), __('Препоръчва се едно главно заглавие на страница, а останалите да са подзаглавия.')),
            default => $this->item('ok', __('Основно заглавие (H1)'), __('Страницата има едно главно заглавие.')),
        };

        // 7. Телефон
        $report['checks'][] = $xpath->query('//meta[@name="viewport"]')->length
            ? $this->item('ok', __('Настроен за телефон'), __('Сайтът казва на телефона как да го покаже (viewport).'))
            : $this->item('fail', __('Не е настроен за телефон'), __('Липсва настройката viewport, така че на телефон сайтът вероятно се показва смален и се чете трудно.'));

        // 8. Индексиране
        $robotsMeta = strtolower((string) $xpath->evaluate('string(//meta[translate(@name,"ROBOTS","robots")="robots"]/@content)') . ' ' . $page['response']->header('X-Robots-Tag'));
        $robotsTxt = $this->fetch($origin . '/robots.txt');
        $robotsBody = $this->isOk($robotsTxt) ? $robotsTxt['response']->body() : '';
        $report['checks'][] = str_contains($robotsMeta, 'noindex') || $this->disallowsEverything($robotsBody)
            ? $this->item('fail', __('Сайтът е скрит от Google'), __('Сайтът казва на Google да не го показва (noindex или забрана в robots.txt). Често остава така по погрешка след изработката.'))
            : $this->item('ok', __('Google може да показва сайта'), __('Няма забрана за показване в търсачките.'));

        // 8б. AI асистентите (ChatGPT, Perplexity, Claude): дали robots.txt не ги спира изрично
        $aiBlocked = $this->blockedAgents($robotsBody, self::AI_SEARCH_BOTS);
        $report['checks'][] = $aiBlocked
            ? $this->item('fail', __('AI асистентите са спрени'), __('robots.txt не пуска :bots. Така ChatGPT и подобните не могат да препоръчат бизнеса, когато някой ги пита.', ['bots' => implode(', ', $aiBlocked)]))
            : $this->item('ok', __('AI асистентите имат достъп'), __('ChatGPT, Perplexity и Claude могат да четат сайта и да го препоръчват.'));

        // 9. Карта на сайта
        $hasSitemap = (bool) preg_match('/^\s*sitemap\s*:/im', $robotsBody) || $this->isOk($this->fetch($origin . '/sitemap.xml'));
        $report['checks'][] = $hasSitemap
            ? $this->item('ok', __('Карта на сайта (sitemap)'), __('Google има списък с всички страници.'))
            : $this->item('warn', __('Няма карта на сайта (sitemap)'), __('Картата помага на Google да намери всички страници по-бързо.'));

        // 10. Данни за бизнеса
        $report['checks'][] = $xpath->query('//script[@type="application/ld+json"]')->length
            ? $this->item('ok', __('Данни за бизнеса за Google и AI (schema)'), __('Сайтът описва бизнеса по начин, който търсачките и AI асистентите разбират.'))
            : $this->item('warn', __('Няма данни за бизнеса за Google и AI (schema)'), __('Специален код казва на машините какво сте, къде сте и кога работите. Помага в Google и в отговорите на AI асистентите.'));

        // 11. Споделяне
        $hasOg = $xpath->query('//meta[@property="og:title"]')->length && $xpath->query('//meta[@property="og:image"]')->length;
        $report['checks'][] = $hasOg
            ? $this->item('ok', __('Изглежда добре при споделяне'), __('Когато някой изпрати линка, се показват заглавие и снимка.'))
            : $this->item('warn', __('Не е настроено как изглежда при споделяне'), __('Когато някой изпрати линка в чат или съобщение, няма снимка и подбрано заглавие.'));

        // 12. Телефон за обаждане
        $report['checks'][] = $xpath->query('//a[starts-with(translate(@href,"TEL","tel"),"tel:")]')->length
            ? $this->item('ok', __('Телефонът се набира с едно докосване'), __('На началната страница има телефон като връзка за обаждане.'))
            : $this->item('warn', __('Няма телефон за обаждане с едно докосване'), __('На телефон хората не преписват номера. Сложете го като връзка горе на страницата.'));

        // 13. Описания на снимките
        $images = $xpath->query('//img')->length;
        if ($images > 0) {
            $missing = $xpath->query('//img[not(@alt)]')->length;
            $report['checks'][] = $missing === 0
                ? $this->item('ok', __('Снимките имат описания (alt)'), __('Google и незрящите посетители разбират какво има на снимките.'))
                : $this->item('warn', __('Снимки без описание (:n от :total)', ['n' => $missing, 'total' => $images]), __('Описанието (alt) казва на Google и на незрящите посетители какво има на снимката.'));
        }

        $points = collect($report['checks'])->sum(fn ($c) => ['ok' => 1, 'warn' => 0.5, 'fail' => 0][$c['status']]);
        $report['score'] = (int) round(100 * $points / count($report['checks']));

        return $report;
    }

    private function item(string $status, string $title, string $detail): array
    {
        return compact('status', 'title', 'detail');
    }

    private function isOk(array $result): bool
    {
        return $result['response'] !== null && $result['response']->successful();
    }

    private function isCertificateError(?string $error): bool
    {
        return $error !== null && (bool) preg_match('/ssl|certificate|tls/i', $error);
    }

    private function origin(string $url): string
    {
        $parts = parse_url($url);

        return $parts['scheme'] . '://' . $parts['host'] . (isset($parts['port']) ? ':' . $parts['port'] : '');
    }

    /** Ботовете, които търсят и четат за AI асистентите (не тези за обучение на модели). */
    public const AI_SEARCH_BOTS = ['OAI-SearchBot', 'ChatGPT-User', 'PerplexityBot', 'Perplexity-User', 'Claude-SearchBot', 'Claude-User'];

    /** robots.txt със „Disallow: /“ за всички ботове. */
    private function disallowsEverything(string $robots): bool
    {
        return in_array('*', $this->blockedAgents($robots, ['*']), true);
    }

    /**
     * Кои от дадените ботове robots.txt спира изцяло („Disallow: /“ в тяхната група).
     * Няколко поредни реда User-agent образуват една група с общи правила.
     */
    public function blockedAgents(string $robots, array $agents): array
    {
        $blocked = [];
        $group = [];
        $inRules = false;
        foreach (preg_split('/\R/', $robots) as $line) {
            $line = trim(preg_replace('/#.*/', '', $line));
            if (preg_match('/^user-agent\s*:\s*(.+)$/i', $line, $m)) {
                if ($inRules) {
                    $group = [];
                    $inRules = false;
                }
                $group[] = strtolower(trim($m[1]));
            } elseif (preg_match('/^(dis)?allow\s*:/i', $line)) {
                $inRules = true;
                if (preg_match('/^disallow\s*:\s*\/\s*$/i', $line)) {
                    foreach ($agents as $agent) {
                        if (in_array(strtolower($agent), $group, true)) {
                            $blocked[$agent] = $agent;
                        }
                    }
                }
            }
        }

        return array_values($blocked);
    }

    private function parse(string $html): \DOMDocument
    {
        $doc = new \DOMDocument;
        $previous = libxml_use_internal_errors(true);
        $doc->loadHTML('<?xml encoding="UTF-8">' . $html, LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        return $doc;
    }

    /**
     * Безопасно изтегляне: само публични IPv4 адреси, портове 80/443, пренасочванията се проверяват наново.
     *
     * @return array{response: ?Response, url: string, ms: int, error: ?string}
     */
    public function fetch(string $url, bool $follow = true): array
    {
        for ($redirects = 0; ; $redirects++) {
            $parts = parse_url($url);
            $scheme = strtolower($parts['scheme'] ?? '');
            $host = strtolower($parts['host'] ?? '');
            $port = $parts['port'] ?? ($scheme === 'https' ? 443 : 80);

            if (! in_array($scheme, ['http', 'https'], true) || ! in_array($port, [80, 443], true) || ! self::normalizeDomain($host)) {
                return ['response' => null, 'url' => $url, 'ms' => 0, 'error' => 'blocked'];
            }
            $ip = $this->resolver->publicIp($host);
            if (! $ip) {
                return ['response' => null, 'url' => $url, 'ms' => 0, 'error' => 'dns'];
            }

            $started = hrtime(true);
            try {
                $response = Http::withUserAgent(self::USER_AGENT)
                    ->withOptions([
                        'allow_redirects' => false,
                        'curl' => [CURLOPT_RESOLVE => ["$host:$port:$ip"]],
                        'on_headers' => function ($response) {
                            if ((int) $response->getHeaderLine('Content-Length') > self::MAX_BYTES) {
                                throw new \RuntimeException('Response too large');
                            }
                        },
                    ])
                    ->connectTimeout(5)
                    ->timeout(10)
                    ->get($url);
            } catch (\Throwable $e) {
                return ['response' => null, 'url' => $url, 'ms' => (int) ((hrtime(true) - $started) / 1e6), 'error' => $e->getMessage()];
            }
            $ms = (int) ((hrtime(true) - $started) / 1e6);

            $location = $response->header('Location');
            if (! $follow || ! $response->redirect() || $location === '' || $redirects >= self::MAX_REDIRECTS) {
                return ['response' => $response, 'url' => $url, 'ms' => $ms, 'error' => null];
            }
            $url = (string) UriResolver::resolve(new Uri($url), new Uri($location));
        }
    }
}
