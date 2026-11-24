<?php

namespace App\Support\Seo;

use App\Models\OutreachSetting;
use App\Models\Post;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * IndexNow: tells Bing, Yandex, Seznam and Naver at once that a page is new or changed. Bing feeds ChatGPT search and Copilot,
 * so a new article can be found there within hours instead of days. The key is public by design (it is served at /{key}.txt).
 * Nothing here ever throws: a failure is logged and kept in outreach_settings `indexnow_state` for „Здраве на системата“.
 */
class IndexNow
{
    public const STATE_KEY = 'indexnow_state';

    /** The protocol allows 10 000 URLs per request. */
    public const MAX_URLS = 10000;

    public static function enabled(): bool
    {
        return (bool) config('creatium.indexnow.enabled') && self::key() !== '';
    }

    public static function key(): string
    {
        return (string) config('creatium.indexnow.key');
    }

    public static function keyLocation(): string
    {
        return rtrim(lroute('home', [], 'bg'), '/') . '/' . self::key() . '.txt';
    }

    public static function state(): array
    {
        return (array) OutreachSetting::get(self::STATE_KEY, []);
    }

    private static function remember(array $changes): void
    {
        try {
            OutreachSetting::put(self::STATE_KEY, $changes + self::state());
        } catch (\Throwable $e) {
            report($e);
        }
    }

    /** Posts that are visible now and were never submitted. */
    public static function pendingPosts()
    {
        return Post::where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->whereNull('indexnow_at')
            ->oldest('published_at');
    }

    /** Every language of a post that can be read. */
    public static function postUrls(Post $post): array
    {
        return array_values(array_map(fn ($l) => $post->url($l), $post->locales()));
    }

    /** Submits the posts that became visible and have no indexnow_at yet. Returns how many posts went out. */
    public static function submitNewPosts(int $limit = 50): int
    {
        if (! self::enabled() || ! config('creatium.blog_enabled')) {
            return 0;
        }
        try {
            $posts = self::pendingPosts()->take($limit)->get()->filter(fn (Post $p) => $p->locales() !== []);
            if ($posts->isEmpty()) {
                return 0;
            }
            $urls = $posts->flatMap(fn (Post $p) => self::postUrls($p))->values()->all();
            if (! self::submit($urls)) {
                return 0;
            }
            Post::whereIn('id', $posts->pluck('id'))->update(['indexnow_at' => now()]);

            return $posts->count();
        } catch (\Throwable $e) {
            report($e);
            self::remember(['last_error' => $e->getMessage(), 'last_attempt_at' => now()->toIso8601String()]);

            return 0;
        }
    }

    /** Every address of the sitemap (the pages and the posts). */
    public static function sitemapUrls(): array
    {
        $xml = app(\App\Http\Controllers\PageController::class)->sitemap()->getContent();
        preg_match_all('#<loc>([^<]+)</loc>#', $xml, $m);

        return array_values(array_unique(array_map('html_entity_decode', $m[1])));
    }

    /** @param list<string> $urls */
    public static function submit(array $urls): bool
    {
        $urls = array_values(array_unique(array_filter($urls, 'filled')));
        if (! self::enabled() || $urls === []) {
            return false;
        }

        $ok = true;
        $error = null;
        $status = null;
        try {
            foreach (array_chunk($urls, self::MAX_URLS) as $chunk) {
                $response = Http::timeout(10)->acceptJson()->asJson()->post((string) config('creatium.indexnow.endpoint'), [
                    'host' => parse_url(lroute('home', [], 'bg'), PHP_URL_HOST),
                    'key' => self::key(),
                    'keyLocation' => self::keyLocation(),
                    'urlList' => $chunk,
                ]);
                $status = $response->status();
                if (! $response->successful()) {
                    $ok = false;
                    $error = 'IndexNow върна ' . $status;
                    break;
                }
            }
        } catch (\Throwable $e) {
            $ok = false;
            $error = $e->getMessage();
        }

        if (! $ok) {
            Log::warning('IndexNow: ' . $error);
        }
        self::remember([
            'last_attempt_at' => now()->toIso8601String(),
            'last_error' => $ok ? null : $error,
            'last_status' => $status,
        ] + ($ok ? ['last_ok_at' => now()->toIso8601String(), 'last_count' => count($urls)] : []));

        return $ok;
    }
}
