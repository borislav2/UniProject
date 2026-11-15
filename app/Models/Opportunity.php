<?php

namespace App\Models;

use App\Support\Opportunities\Countries;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Възможност: обществена поръчка, агенция партньор, заявка от платформа за свободна практика, грант или нещо ръчно.
 * Нищо не се подава и не се праща автоматично: екипът решава (статуси new, interesting, preparing, submitted, won, lost, skipped).
 */
class Opportunity extends Model
{
    public const KINDS = [
        'tender' => 'Обществена поръчка',
        'partner' => 'Партньор',
        'freelance' => 'Свободна практика',
        'grant' => 'Грант или програма',
        'other' => 'Друго',
    ];

    public const SOURCES = [
        'ted' => 'TED (ЕС)',
        'contracts-finder' => 'Contracts Finder',
        'find-a-tender' => 'Find a Tender',
        'search' => 'Търсене на бизнеси',
        'manual' => 'Добавена на ръка',
    ];

    /** Статус => [етикет, клас на значката]. */
    public const STATUSES = [
        'new' => ['Нова', 'bg-gray-100 text-gray-800'],
        'interesting' => ['Интересна', 'bg-blue-100 text-blue-800'],
        'preparing' => ['Подготвяме', 'bg-amber-100 text-amber-800'],
        'submitted' => ['Подадена', 'bg-indigo-100 text-indigo-800'],
        'won' => ['Спечелена', 'bg-green-100 text-green-800'],
        'lost' => ['Загубена', 'bg-red-100 text-red-800'],
        'skipped' => ['Пропусната', 'bg-gray-200 text-gray-700'],
    ];

    /** Работят се (не са приключили). */
    public const OPEN_STATUSES = ['new', 'interesting', 'preparing'];

    public const TOPICS = [
        'web' => 'уебсайт и уеб разработка',
        'seo' => 'оптимизация за търсачки (SEO)',
        'marketing' => 'маркетинг и реклама',
        'social' => 'социални медии',
        'design' => 'дизайн и брандиране',
        'pr' => 'връзки с обществеността',
    ];

    public const CURRENCY_SYMBOLS = ['EUR' => '€', 'GBP' => '£', 'USD' => '$'];

    protected $fillable = [
        'kind', 'source', 'external_id', 'title', 'buyer', 'country', 'language', 'url', 'url_key', 'summary', 'cpv',
        'value_min', 'value_max', 'currency', 'published_at', 'deadline_at', 'score', 'score_reasons', 'status', 'skip_reason',
        'assigned_to', 'note', 'next_action_at', 'project_id', 'outreach_prospect_id', 'added_by', 'raw', 'last_seen_at',
    ];

    protected $casts = [
        'cpv' => 'array', 'score_reasons' => 'array', 'raw' => 'array', 'score' => 'integer',
        'value_min' => 'float', 'value_max' => 'float',
        'published_at' => 'datetime', 'deadline_at' => 'datetime', 'next_action_at' => 'datetime', 'last_seen_at' => 'datetime',
    ];

    /** Адресът без параметри за проследяване и без накрая наклонена черта: един и същ адрес = една възможност. */
    public static function urlKey(?string $url): ?string
    {
        $url = trim((string) $url);
        if ($url === '') {
            return null;
        }
        if (! preg_match('#^[a-z][a-z0-9+.-]*://#i', $url)) {
            $url = 'https://' . $url;
        }
        $parts = parse_url($url);
        if (! $parts || empty($parts['host'])) {
            return null;
        }
        parse_str($parts['query'] ?? '', $query);
        $query = array_diff_key($query, array_flip(['utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content', 'ref', 'referrer', 'fbclid', 'gclid', 'source']));
        ksort($query);
        $host = preg_replace('/^www\./', '', strtolower($parts['host']));

        return sha1($host . '|' . rtrim($parts['path'] ?? '/', '/') . '|' . http_build_query($query));
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereIn('status', self::OPEN_STATUSES);
    }

    /** Нови с поне толкова точки (броячът в менюто). */
    public function scopeWorthLooking(Builder $query): Builder
    {
        return $query->where('status', 'new')->where('score', '>=', (int) config('creatium.opportunities.badge_score'));
    }

    /** Интересни или в подготовка със срок в следващите $days дни (без изтеклите). */
    public function scopeDueSoon(Builder $query, int $days = 7): Builder
    {
        return $query->whereIn('status', ['interesting', 'preparing'])->whereNotNull('deadline_at')
            ->where('deadline_at', '>', now())->where('deadline_at', '<=', now()->addDays($days));
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function prospect()
    {
        return $this->belongsTo(OutreachProspect::class, 'outreach_prospect_id');
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function notes()
    {
        return $this->hasMany(OpportunityNote::class)->latest('id');
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status][0] ?? $this->status;
    }

    public function statusClass(): string
    {
        return self::STATUSES[$this->status][1] ?? 'bg-gray-100 text-gray-800';
    }

    public function kindLabel(): string
    {
        return self::KINDS[$this->kind] ?? $this->kind;
    }

    public function sourceLabel(): string
    {
        if ($this->source === 'manual' && ! empty($this->raw['origin'])) {
            return (string) $this->raw['origin'];
        }

        return self::SOURCES[$this->source] ?? $this->source;
    }

    public function countryName(): string
    {
        return Countries::name($this->country);
    }

    public function isOpen(): bool
    {
        return in_array($this->status, self::OPEN_STATUSES, true);
    }

    /** Цели дни до срока: отрицателни, ако е минал; null без срок. */
    public function daysLeft(): ?int
    {
        return $this->deadline_at ? (int) floor(now()->floatDiffInDays($this->deadline_at, false)) : null;
    }

    /** Срокът наближава: под 7 дни и още не е минал, за работени възможности. */
    public function isUrgent(): bool
    {
        $days = $this->daysLeft();

        return $days !== null && $days >= 0 && $days < 7 && ! in_array($this->status, ['skipped', 'won', 'lost', 'submitted'], true);
    }

    public function deadlineText(): ?string
    {
        return $this->deadline_at?->copy()->timezone('Europe/Sofia')->format('d.m.Y');
    }

    public function daysLeftText(): ?string
    {
        $days = $this->daysLeft();
        if ($days === null) {
            return null;
        }

        return match (true) {
            $days < 0 => 'изтекъл',
            $days === 0 => 'днес',
            $days === 1 => 'утре',
            default => "след {$days} дни",
        };
    }

    /** Стойността за показване: „€25 000“, „£60 000“, „от X до Y“, „неизвестна“. */
    public function valueText(): ?string
    {
        $symbol = self::CURRENCY_SYMBOLS[$this->currency] ?? null;
        $format = fn ($n) => ($symbol ?? '') . number_format((float) $n, 0, ',', ' ') . ($symbol ? '' : ' ' . $this->currency);

        return match (true) {
            $this->value_min && $this->value_max && $this->value_min != $this->value_max => 'от ' . $format($this->value_min) . ' до ' . $format($this->value_max),
            (bool) ($this->value_max ?? $this->value_min) => $format($this->value_max ?? $this->value_min),
            default => null,
        };
    }

    public function topicLabel(): ?string
    {
        return self::TOPICS[$this->raw['topic'] ?? ''] ?? null;
    }

    /**
     * Едно изречение на български за какво става дума, сглобено от полетата (не е превод на текста от обявата):
     * „Поръчка за уебсайт и уеб разработка от Loviisan kaupunki, Финландия, срок 23.11.2026, стойност €25 000.“
     */
    public function summaryLine(): string
    {
        $where = $this->countryName();
        $deadline = $this->deadlineText();
        $value = $this->valueText();

        if ($this->kind === 'partner') {
            $place = collect([$this->raw['city'] ?? null, $where !== 'без държава' ? $where : null])->filter()->join(', ');

            return 'Агенция партньор: ' . $this->title . ($place ? ', ' . $place : '') . '. Предлагаме сайтове, SEO и имейл под тяхното име.';
        }

        $subject = match ($this->kind) {
            'tender' => 'Поръчка за ' . ($this->topicLabel() ?? 'услуги, близки до нашите'),
            'freelance' => 'Заявка за ' . ($this->topicLabel() ?? 'работа') . ' от платформа за свободна практика',
            'grant' => 'Грант или програма',
            default => 'Възможност' . ($this->topicLabel() ? ' за ' . $this->topicLabel() : ''),
        };
        $parts = [$subject . ($this->buyer ? ' от ' . $this->buyer : '')];
        if ($where !== 'без държава') {
            $parts[] = $where;
        }
        $parts[] = $deadline ? 'срок ' . $deadline : 'без обявен срок';
        $parts[] = $value ? 'стойност ' . $value : 'стойност неизвестна';

        return implode(', ', $parts) . '.';
    }

    /**
     * Сменя статуса и пише в историята. Приключилите (изпратена, спечелена, загубена, пропусната) махат следващата стъпка.
     */
    public function moveTo(string $status, ?int $userId = null, ?string $reason = null): void
    {
        if ($status === $this->status && $reason === null) {
            return;
        }
        $from = $this->status;
        $this->status = $status;
        $this->skip_reason = $status === 'skipped' ? ($reason ?: $this->skip_reason) : null;
        if (in_array($status, ['submitted', 'won', 'lost', 'skipped'], true)) {
            $this->next_action_at = null;
        }
        $this->save();

        $body = (self::STATUSES[$from][0] ?? $from) . ' → ' . (self::STATUSES[$status][0] ?? $status) . ($reason ? ': ' . $reason : '');
        OpportunityNote::create(['opportunity_id' => $this->id, 'user_id' => $userId, 'kind' => 'status', 'body' => $body]);
    }

    public function expire(): void
    {
        $this->moveTo('skipped', null, 'изтекъл срок');
    }

    public static function startOfDaySofia(): Carbon
    {
        return now('Europe/Sofia')->startOfDay()->utc();
    }
}
