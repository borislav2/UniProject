<?php

namespace App\Support\Facebook;

use App\Models\Post;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Пуска статиите от блога във Facebook страницата (Graph API: POST /{page-id}/feed с текст и връзка;
 * Facebook сам показва корицата и заглавието от Open Graph таговете на статията).
 *
 * Автоматично (run(), извиква се от PublishBlogToFacebook най-много веднъж на 10 минути, без cron):
 * новите статии (излезли преди по-малко от FRESH_DAYS дни) веднага, а по-старите по една на ден
 * между 09:00 и 20:00 в София, от най-старата. Само български статии (сайтът е на български).
 * При грешка статията се маркира и не се опитва пак сама; при невалиден ключ автоматиката спира за 12 часа.
 */
class FacebookPublisher
{
    public const FRESH_DAYS = 2;

    private const PAUSE_KEY = 'facebook:paused';

    public static function configured(): bool
    {
        return filled(config('services.facebook.page_id')) && filled(config('services.facebook.page_token'));
    }

    public static function paused(): ?string
    {
        return Cache::get(self::PAUSE_KEY);
    }

    /** Публикува една чакаща статия, ако има ред. Връща публикуваната статия или null. */
    public function run(): ?Post
    {
        if (! self::configured() || self::paused()) {
            return null;
        }

        return Cache::lock('facebook:publishing', 120)->get(function () {
            $post = $this->due();

            return $post && $this->publish($post) === null ? $post : null;
        });
    }

    /** Следващата статия за автоматично публикуване или null. */
    public function due(?Carbon $now = null): ?Post
    {
        $now ??= now();
        $pending = Post::visible('bg')->whereNull('facebook_post_id')->whereNull('facebook_error')->orderBy('published_at');
        $freshSince = $now->copy()->subDays(self::FRESH_DAYS);

        if ($fresh = (clone $pending)->where('published_at', '>=', $freshSince)->first()) {
            return $fresh;
        }

        $sofia = $now->copy()->timezone('Europe/Sofia');
        if ($sofia->hour < 9 || $sofia->hour >= 20) {
            return null;
        }
        // По-старите: една на ден. Броим публикуваните днес, които не са били нови в момента на публикуване.
        $postedToday = Post::where('facebook_posted_at', '>=', $sofia->copy()->startOfDay()->utc())
            ->whereNotNull('facebook_post_id')->get()
            ->contains(fn (Post $post) => $post->published_at->lt($post->facebook_posted_at->copy()->subDays(self::FRESH_DAYS)));

        return $postedToday ? null : (clone $pending)->where('published_at', '<', $freshSince)->first();
    }

    /** Публикува статията. Връща текста на грешката или null при успех. */
    public function publish(Post $post): ?string
    {
        if (! self::configured()) {
            return 'Facebook не е свързан (FACEBOOK_PAGE_ID / FACEBOOK_PAGE_TOKEN).';
        }

        try {
            $response = Http::asForm()->acceptJson()->connectTimeout(10)->timeout(30)
                ->post(rtrim(config('services.facebook.graph_url'), '/') . '/' . config('services.facebook.page_id') . '/feed', [
                    'message' => $this->message($post),
                    'link' => $this->link($post),
                    'access_token' => config('services.facebook.page_token'),
                ]);
        } catch (ConnectionException $e) {
            return $this->fail($post, 'Facebook не отговаря: ' . $e->getMessage());
        }

        if (! $response->successful() || ! $response->json('id')) {
            $error = $response->json('error') ?? [];
            // 190 = невалиден или изтекъл ключ: спираме автоматиката, за да не се маркират всички статии.
            if (($error['code'] ?? null) === 190) {
                Cache::put(self::PAUSE_KEY, $error['message'] ?? 'Невалиден ключ', now()->addHours(12));
            }

            return $this->fail($post, trim(($error['message'] ?? 'Грешка ' . $response->status()) . (isset($error['code']) ? ' (код ' . $error['code'] . ')' : '')));
        }

        $post->forceFill(['facebook_post_id' => $response->json('id'), 'facebook_posted_at' => now(), 'facebook_error' => null])->save();
        Cache::forget(self::PAUSE_KEY);

        return null;
    }

    public function message(Post $post): string
    {
        return trim($post->tr('title', 'bg') . "\n\n" . $post->excerptOrSummary('bg', 400));
    }

    /** Връзката към статията с UTM, за да се виждат посещенията от Facebook в отчетите. */
    public function link(Post $post): string
    {
        return $post->url('bg') . '?' . http_build_query(['utm_source' => 'facebook', 'utm_medium' => 'social', 'utm_campaign' => 'blog']);
    }

    public static function postUrl(Post $post): ?string
    {
        return $post->facebook_post_id ? 'https://www.facebook.com/' . $post->facebook_post_id : null;
    }

    private function fail(Post $post, string $error): string
    {
        $post->forceFill(['facebook_error' => mb_substr($error, 0, 1000)])->save();
        Log::warning('Facebook publish failed', ['post_id' => $post->id, 'error' => $error]);

        return $error;
    }
}
