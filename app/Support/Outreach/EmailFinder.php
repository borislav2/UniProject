<?php

namespace App\Support\Outreach;

use App\Support\SiteCheck\SiteChecker;

/**
 * Намира имейла, който бизнесът сам е публикувал на сайта си: началната страница и до три страници
 * „Контакти“ / „За нас“. Взима само адрес на неговия домейн или безплатна поща (abv.bg, gmail…),
 * никога чужд домейн (например на уеб агенцията във футъра).
 */
class EmailFinder
{
    public const FREE_MAIL = [
        'abv.bg', 'gmail.com', 'mail.bg', 'dir.bg', 'yahoo.com', 'hotmail.com', 'outlook.com',
        'live.com', 'icloud.com', 'googlemail.com', 'gbg.bg',
    ];

    private const PREFERRED = ['info', 'office', 'contact', 'contacts', 'kontakt', 'kontakti', 'hello', 'reservations',
        'reservation', 'booking', 'rezervacii', 'reception', 'sales'];

    private const SKIP_LOCAL = ['noreply', 'no-reply', 'donotreply', 'webmaster', 'postmaster', 'abuse', 'privacy', 'gdpr', 'dpo'];

    private const CONTACT_WORDS = ['kontakt', 'контакт', 'contact', 'za-nas', 'за нас', 'about', 'svarzhete', 'свържете'];

    private const EMAIL = '[A-Za-z0-9._%+-]+@[A-Za-z0-9-]+(?:\.[A-Za-z0-9-]+)*\.[A-Za-z]{2,}';

    private const MAX_EXTRA_PAGES = 3;

    public function __construct(private SiteChecker $checker)
    {
    }

    /** @return array{email: string, page: string}|null */
    public function find(string $website): ?array
    {
        $domain = SiteChecker::normalizeDomain($website);
        if (! $domain) {
            return null;
        }

        if (! parse_url($website, PHP_URL_PATH)) {
            $website = rtrim($website, '/') . '/';
        }
        $start = $this->checker->fetch($website);
        if (! $this->ok($start) && str_starts_with($website, 'https://')) {
            $start = $this->checker->fetch('http://' . substr($website, 8));
        }
        if (! $this->ok($start)) {
            return null;
        }

        $pages = [[$start['url'], $start['response']->body()]];
        foreach (self::contactLinks($pages[0][1], $start['url']) as $link) {
            $page = $this->checker->fetch($link);
            $pages[] = [$page['url'], $this->ok($page) ? $page['response']->body() : ''];
        }

        // Първо страниците за контакт, после началната.
        foreach (array_reverse($pages) as [$url, $html]) {
            $email = self::best(array_filter(self::extract($html), fn ($e) => self::acceptable($e, $domain)), $domain);
            if ($email) {
                return ['email' => $email, 'page' => $url];
            }
        }

        return null;
    }

    /** Всички адреси в страницата: mailto:, обикновен текст и скритите от Cloudflare. */
    public static function extract(string $html): array
    {
        $found = [];
        if (preg_match_all('/href\s*=\s*["\']mailto:([^"\'?]+)/i', $html, $m)) {
            $found = array_map('rawurldecode', $m[1]);
        }
        if (preg_match_all('/data-cfemail\s*=\s*["\']([0-9a-fA-F]+)["\']/', $html, $m)) {
            $found = array_merge($found, array_map([self::class, 'decodeCloudflare'], $m[1]));
        }
        $text = html_entity_decode(strip_tags(preg_replace('/<(script|style)\b.*?<\/\1>/is', ' ', $html)), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        if (preg_match_all('/' . self::EMAIL . '/', $text, $m)) {
            $found = array_merge($found, $m[0]);
        }

        return array_values(array_unique(array_filter(array_map(fn ($e) => mb_strtolower(trim($e)), $found))));
    }

    public static function decodeCloudflare(string $hex): string
    {
        if (strlen($hex) < 4 || strlen($hex) % 2) {
            return '';
        }
        $bytes = array_map('hexdec', str_split($hex, 2));
        $key = array_shift($bytes);

        return implode('', array_map(fn ($b) => chr($b ^ $key), $bytes));
    }

    public static function acceptable(string $email, string $domain): bool
    {
        if (! preg_match('/^' . self::EMAIL . '$/', $email)) {
            return false;
        }
        [$local, $host] = explode('@', $email, 2);
        if (preg_match('/\.(png|jpe?g|gif|webp|svg)$/', $host)) {
            return false;
        }
        foreach (self::SKIP_LOCAL as $skip) {
            if (str_starts_with($local, $skip)) {
                return false;
            }
        }

        return self::ownDomain($host, $domain) || in_array($host, self::FREE_MAIL, true);
    }

    public static function best(array $emails, string $domain): ?string
    {
        $rank = function (string $email) use ($domain) {
            [$local, $host] = explode('@', $email, 2);
            $preferred = count(self::PREFERRED);
            foreach (self::PREFERRED as $i => $word) {
                if ($local === $word || str_starts_with($local, $word)) {
                    $preferred = $i;
                    break;
                }
            }

            return [self::ownDomain($host, $domain) ? 0 : 1, $preferred, strlen($email)];
        };
        $emails = array_values(array_unique($emails));
        usort($emails, fn ($a, $b) => $rank($a) <=> $rank($b));

        return $emails[0] ?? null;
    }

    /** Връзки към „Контакти“ / „За нас“ на същия сайт. */
    public static function contactLinks(string $html, string $base): array
    {
        $host = parse_url($base, PHP_URL_HOST);
        $links = [];
        if (! preg_match_all('/<a\b[^>]*href\s*=\s*["\']([^"\'#]+)["\'][^>]*>(.*?)<\/a>/is', $html, $m, PREG_SET_ORDER)) {
            return [];
        }
        foreach ($m as [, $href, $text]) {
            $href = trim(html_entity_decode($href));
            if (preg_match('/^(mailto|tel|javascript):/i', $href)) {
                continue;
            }
            $target = (string) \GuzzleHttp\Psr7\UriResolver::resolve(new \GuzzleHttp\Psr7\Uri($base), new \GuzzleHttp\Psr7\Uri($href));
            if (parse_url($target, PHP_URL_HOST) !== $host || rtrim($target, '/') === rtrim($base, '/')) {
                continue;
            }
            $hay = mb_strtolower(rawurldecode($href) . ' ' . strip_tags($text));
            foreach (self::CONTACT_WORDS as $word) {
                if (str_contains($hay, $word) && ! in_array($target, $links, true)) {
                    $links[] = $target;
                    break;
                }
            }
        }

        return array_slice($links, 0, self::MAX_EXTRA_PAGES);
    }

    private static function ownDomain(string $host, string $domain): bool
    {
        $site = preg_replace('/^www\./', '', $domain);

        return $host === $site || str_ends_with($host, '.' . $site) || str_ends_with($site, '.' . $host);
    }

    private function ok(array $result): bool
    {
        return $result['response'] !== null && $result['response']->successful();
    }
}
