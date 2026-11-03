<?php

namespace App\Support\LinkedIn;

use App\Models\Post;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Пуска статиите от блога в LinkedIn страницата на фирмата (Posts API: POST {api_url}/posts с връзка към статията,
 * LinkedIn показва картата от Open Graph таговете). Същите правила като във Facebook (FacebookPublisher):
 * новите статии (по-малко от FRESH_DAYS дни) веднага, по-старите по една на ден между 09:00 и 20:00 в София,
 * от най-старата; само български статии. Грешката се записва на статията и не се опитва пак сама;
 * отговор 401 (невалиден или изтекъл ключ) спира автоматиката за 12 часа. Ключът важи 60 дни (LINKEDIN_TOKEN_EXPIRES):
 * след датата на изтичане нищо не се праща.
 */
class LinkedInPublisher
{
    public const FRESH_DAYS = 2;

    private const PAUSE_KEY = 'linkedin:paused';

    public static function configured(): bool
    {
        return filled(config('services.linkedin.organization_id')) && filled(config('services.linkedin.token'));
    }

    /** Датата, на която ключът изтича (Y-m-d), или null, ако не е записана или не се чете. */
    public static function expiresOn(): ?Carbon
    {
        $value = config('services.linkedin.token_expires');
        if (blank($value)) {
            return null;
        }
        try {
            return Carbon::createFromFormat('Y-m-d', trim((string) $value), 'Europe/Sofia')->startOfDay();
        } catch (\Throwable) {
            return null;
        }
    }

    /** Ключът е изтекъл: датата на изтичане е в миналото (на самия ден още важи). */
    public static function expired(): bool
    {
        $expires = self::expiresOn();

        return $expires !== null && now('Europe/Sofia')->startOfDay()->greaterThan($expires);
    }

    /** Колко дни остават до датата на изтичане (0 = изтича днес, отрицателно = изтекъл) или null, ако няма дата. */
    public static function daysLeft(): ?int
    {
        $expires = self::expiresOn();

        return $expires ? (int) now('Europe/Sofia')->startOfDay()->diffInDays($expires, false) : null;
    }

    /** Може ли да се публикува: има настройки и ключът не е изтекъл. */
    public static function ready(): bool
    {
        return self::configured() && ! self::expired();
    }

    public static function paused(): ?string
    {
        return Cache::get(self::PAUSE_KEY);
    }

    /** Публикува една чакаща статия, ако има ред. Връща публикуваната статия или null. */
    public function run(): ?Post
    {
        if (! self::ready() || self::paused()) {
            return null;
        }

        return Cache::lock('linkedin:publishing', 120)->get(function () {
            $post = $this->due();

            return $post && $this->publish($post) === null ? $post : null;
        });
    }

    /** Следващата статия за автоматично публикуване или null. */
    public function due(?Carbon $now = null): ?Post
    {
        $now ??= now();
        $pending = Post::visible('bg')->whereNull('linkedin_post_id')->whereNull('linkedin_error')->orderBy('published_at');
        $freshSince = $now->copy()->subDays(self::FRESH_DAYS);

        if ($fresh = (clone $pending)->where('published_at', '>=', $freshSince)->first()) {
            return $fresh;
        }

        $sofia = $now->copy()->timezone('Europe/Sofia');
        if ($sofia->hour < 9 || $sofia->hour >= 20) {
            return null;
        }
        // По-старите: една на ден. Броим публикуваните днес, които не са били нови в момента на публикуване.
        $postedToday = Post::where('linkedin_posted_at', '>=', $sofia->copy()->startOfDay()->utc())
            ->whereNotNull('linkedin_post_id')->get()
            ->contains(fn (Post $post) => $post->published_at->lt($post->linkedin_posted_at->copy()->subDays(self::FRESH_DAYS)));

        return $postedToday ? null : (clone $pending)->where('published_at', '<', $freshSince)->first();
    }

    /** Публикува статията. Връща текста на грешката или null при успех. */
    public function publish(Post $post): ?string
    {
        if (! self::configured()) {
            return 'LinkedIn не е свързан (LINKEDIN_ORGANIZATION_ID / LINKEDIN_ACCESS_TOKEN).';
        }
        if (self::expired()) {
            return 'Ключът за LinkedIn е изтекъл (' . self::expiresOn()->format('d.m.Y') . '). Подновете го: php artisan creatium:linkedin-connect';
        }

        try {
            $response = Http::withToken(config('services.linkedin.token'))->acceptJson()->connectTimeout(10)->timeout(30)
                ->withHeaders(['LinkedIn-Version' => config('services.linkedin.version'), 'X-Restli-Protocol-Version' => '2.0.0'])
                ->post(rtrim(config('services.linkedin.api_url'), '/') . '/posts', $this->payload($post));
        } catch (ConnectionException $e) {
            return $this->fail($post, 'LinkedIn не отговаря: ' . $e->getMessage());
        }

        $id = $response->header('x-restli-id');
        if ($response->status() !== 201 || blank($id)) {
            // 401 = невалиден или изтекъл ключ: спираме автоматиката, за да не се маркират всички статии.
            if ($response->status() === 401) {
                Cache::put(self::PAUSE_KEY, $response->json('message') ?: 'Невалиден или изтекъл ключ', now()->addHours(12));
            }
            $message = $response->json('message') ?: 'Грешка ' . $response->status();

            return $this->fail($post, trim($message . ' (код ' . $response->status() . ')'));
        }

        $post->forceFill(['linkedin_post_id' => $id, 'linkedin_posted_at' => now(), 'linkedin_error' => null])->save();
        Cache::forget(self::PAUSE_KEY);

        return null;
    }

    /** Тялото на заявката към Posts API (статия с връзка, публична, в основната лента). */
    public function payload(Post $post): array
    {
        $excerpt = $post->excerptOrSummary('bg', 400);

        return [
            'author' => 'urn:li:organization:' . config('services.linkedin.organization_id'),
            'commentary' => $this->commentary($post),
            'visibility' => 'PUBLIC',
            'distribution' => ['feedDistribution' => 'MAIN_FEED', 'targetEntities' => [], 'thirdPartyDistributionChannels' => []],
            'content' => ['article' => ['source' => $this->link($post), 'title' => $post->tr('title', 'bg'), 'description' => $excerpt]],
            'lifecycleState' => 'PUBLISHED',
            'isReshareDisabledByAuthor' => false,
        ];
    }

    public function commentary(Post $post): string
    {
        return self::escape(trim($post->tr('title', 'bg') . "\n\n" . $post->excerptOrSummary('bg', 400)));
    }

    /**
     * Текстът на публикацията е във формат „little text“: тези знаци имат значение (@ споменава, # е таг, скобите са
     * връзки и др.), затова всеки се предшества с обратна наклонена черта: \ | { } @ [ ] ( ) < > # * _ ~
     */
    public static function escape(string $text): string
    {
        return preg_replace('/([\\\\|{}@\[\]()<>#*_~])/u', '\\\\$1', $text);
    }

    /** Връзката към статията с UTM, за да се виждат посещенията от LinkedIn в отчетите. */
    public function link(Post $post): string
    {
        return $post->url('bg') . '?' . http_build_query(['utm_source' => 'linkedin', 'utm_medium' => 'social', 'utm_campaign' => 'blog']);
    }

    /** Публикацията в LinkedIn; идентификаторът е URN (urn:li:share:… или urn:li:ugcPost:…). */
    public static function postUrl(Post $post): ?string
    {
        return $post->linkedin_post_id ? 'https://www.linkedin.com/feed/update/' . $post->linkedin_post_id : null;
    }

    private function fail(Post $post, string $error): string
    {
        $post->forceFill(['linkedin_error' => mb_substr($error, 0, 1000)])->save();
        Log::warning('LinkedIn publish failed', ['post_id' => $post->id, 'error' => $error]);

        return $error;
    }
}
