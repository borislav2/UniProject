<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Project extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'name',
        'description',
        'start_date',
        'end_date',
        'status',
        'manager',
        'category_id',
        'file_path',
        'client_email',
        'client_phone',
        'source',
        'service',
        'business_size',
        'website_url',
        'screenshot',
        'read_at',
        'email_sent_at',
        'consultation_at',
        'locale',
        'is_public',
        'lead_channel',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'referrer',
        'stage',
        'stage_changed_at',
        'next_action',
        'next_action_at',
        'lost_reason',
        'assigned_to',
        'package',
        'monthly_fee',
        'billing_day',
        'monitor_status',
        'monitor_error',
        'monitor_checked_at',
        'monitor_alerted_at',
        'ssl_expires_at',
        'domain_expires_at',
    ];
    
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_public' => 'boolean',
        'read_at' => 'datetime',
        'email_sent_at' => 'datetime',
        'consultation_at' => 'datetime',
        'stage_changed_at' => 'datetime',
        'next_action_at' => 'datetime',
        'monthly_fee' => 'decimal:2',
        'billing_day' => 'integer',
        'monitor_checked_at' => 'datetime',
        'monitor_alerted_at' => 'datetime',
        'ssl_expires_at' => 'datetime',
        'domain_expires_at' => 'datetime',
        'site_check_report' => 'array',
    ];

    /** Етапите на продажбата за запитванията. Отворените са тези, които още чакат действие от нас. */
    public const STAGES = [
        'new' => ['Ново', 'bg-blue-100 text-blue-800'],
        'contacted' => ['Свързахме се', 'bg-indigo-100 text-indigo-800'],
        'offer' => ['Оферта изпратена', 'bg-purple-100 text-purple-800'],
        'won' => ['Спечелен', 'bg-green-100 text-green-800'],
        'lost' => ['Загубен', 'bg-gray-200 text-gray-700'],
    ];

    public const OPEN_STAGES = ['new', 'contacted', 'offer'];

    protected static function booted(): void
    {
        // Всяко ново запитване от сайта (форма, чат, проверка на сайт) започва като „Ново“.
        static::creating(function (Project $project) {
            if ($project->source === 'website' && ! $project->stage) {
                $project->stage = 'new';
                $project->stage_changed_at = now();
            }
        });
    }

    public function stageLabel(): ?string
    {
        return $this->stage ? (self::STAGES[$this->stage][0] ?? $this->stage) : null;
    }

    public function stageClass(): string
    {
        return self::STAGES[$this->stage][1] ?? 'bg-gray-100 text-gray-800';
    }

    public function isOpenLead(): bool
    {
        return in_array($this->stage, self::OPEN_STAGES, true);
    }

    /** Следващата стъпка е минала (или е за днес по-рано) и запитването още е отворено. */
    public function isOverdue(): bool
    {
        return $this->isOpenLead() && $this->next_action_at && $this->next_action_at->isPast();
    }

    public function scopeOpenLeads(Builder $query): Builder
    {
        return $query->whereIn('stage', self::OPEN_STAGES);
    }

    /**
     * Откъде е дошло запитването от сайта: формата за контакт, „Получете оферта“ от пакетите,
     * безплатната проверка на сайт или Крео (чата). [текст, цвят на етикета]
     */
    public const LEAD_KINDS = [
        'form' => ['Форма за контакт', 'bg-sky-100 text-sky-800'],
        'quote' => ['Оферта за пакет', 'bg-emerald-100 text-emerald-800'],
        'site-check' => ['Проверка на сайт', 'bg-amber-100 text-amber-800'],
        'chat' => ['Крео (чат)', 'bg-fuchsia-100 text-fuchsia-800'],
    ];

    public const SITE_CHECK_SERVICE = 'Проверка на сайт';

    public const CHAT_SERVICE = 'Запитване от чата';

    public function leadKind(): ?string
    {
        if ($this->source !== 'website') {
            return null;
        }

        return match (true) {
            $this->service === self::SITE_CHECK_SERVICE => 'site-check',
            $this->service === self::CHAT_SERVICE => 'chat',
            str_starts_with((string) $this->service, 'Оферта: ') => 'quote',
            default => 'form',
        };
    }

    public function leadKindLabel(): ?string
    {
        return self::LEAD_KINDS[$this->leadKind()][0] ?? null;
    }

    public function leadKindClass(): string
    {
        return self::LEAD_KINDS[$this->leadKind()][1] ?? 'bg-gray-100 text-gray-700';
    }

    /** Запитвания от формите на сайта (контакт и оферта за пакет), без проверките на сайт и чата. */
    public function scopeFormLeads(Builder $query): Builder
    {
        return $query->where('source', 'website')
            ->where(fn ($q) => $q->whereNull('service')->orWhereNotIn('service', [self::SITE_CHECK_SERVICE, self::CHAT_SERVICE]));
    }

    /**
     * Проекти за клиенти: създадените в админа и спечелените запитвания. Запитванията (отворени или загубени)
     * от сайта и от писмата/обажданията са в „Запитвания“ и „Проверки на сайт“, не тук.
     */
    public function scopeClientProjects(Builder $query): Builder
    {
        return $query->where(fn ($q) => $q->where('stage', 'won')
            ->orWhere(fn ($q) => $q->whereNull('stage')->where(fn ($q) => $q->whereNull('source')->orWhereNotIn('source', ['website', 'outreach']))));
    }

    /** Хората, които са пуснали безплатната проверка на сайта си. */
    public function scopeSiteChecks(Builder $query): Builder
    {
        return $query->where('source', 'website')->where('service', self::SITE_CHECK_SERVICE);
    }

    /** Запитвания на човека от екипа и неразпределените (за да не остане нищо без човек). */
    public function scopeForMember(Builder $query, ?int $userId): Builder
    {
        return $userId ? $query->where(fn ($q) => $q->where('assigned_to', $userId)->orWhereNull('assigned_to')) : $query;
    }

    /**
     * Обобщението на безплатната проверка: оценка и точките по статус. Старите запитвания нямат записан резултат,
     * затова оценката и списъкът „За оправяне“ се четат от описанието.
     */
    public function siteCheckSummary(): ?array
    {
        if ($this->leadKind() !== 'site-check') {
            return null;
        }
        $report = $this->site_check_report;
        if (is_array($report) && isset($report['score'])) {
            $checks = collect($report['checks'] ?? []);

            return [
                'domain' => $report['domain'] ?? $this->website_url,
                'score' => (int) $report['score'],
                'fail' => $checks->where('status', 'fail')->values()->all(),
                'warn' => $checks->where('status', 'warn')->values()->all(),
                'ok' => $checks->where('status', 'ok')->count(),
                'sent' => (bool) ($report['sent'] ?? false),
                'failed' => false,
                // Новият отчет (обхождане на сайта); старите запитвания нямат тези ключове.
                'crawl' => $report['crawl'] ?? null,
                'pagespeed' => $report['pagespeed'] ?? null,
                'shopping' => \App\Support\SiteCheck\ShoppingReadiness::summary($report['shopping'] ?? null),
                'issues' => collect($report['issues'] ?? [])->map(function ($issue) {
                    $d = \App\Support\SiteCheck\SiteCrawler::describe($issue['key'], $issue['count']);

                    return ['severity' => $issue['severity'], 'title' => $d['title'], 'count' => $issue['count'], 'examples' => $issue['examples'] ?? []];
                })->all(),
            ];
        }
        if (is_array($report) && ! empty($report['failed'])) {
            return ['domain' => $report['domain'] ?? $this->website_url, 'score' => null, 'fail' => [], 'warn' => [], 'ok' => 0, 'sent' => false, 'failed' => true];
        }

        $text = (string) $this->description;
        if (! preg_match('/оценка (\d+) от 100/u', $text, $score)) {
            return ['domain' => $this->website_url, 'score' => null, 'fail' => [], 'warn' => [], 'ok' => 0, 'sent' => false, 'failed' => str_contains($text, 'не успя'), 'pending' => str_contains($text, 'тече')];
        }
        $problems = preg_match('/За оправяне:\n((?:- .+\n?)+)/u', $text, $m)
            ? array_map(fn ($line) => ['status' => 'fail', 'title' => ltrim($line, '- '), 'detail' => ''], array_filter(explode("\n", trim($m[1]))))
            : [];

        return ['domain' => $this->website_url, 'score' => (int) $score[1], 'fail' => array_values($problems), 'warn' => [], 'ok' => null, 'sent' => str_contains($text, 'Отчетът е изпратен'), 'failed' => false];
    }
    
    /** Статусите в базата са на английски (валидират се така); в админа се показват на български. */
    public const STATUSES = [
        'Planning' => ['Планиране', 'bg-yellow-100 text-yellow-800'],
        'In Progress' => ['В работа', 'bg-blue-100 text-blue-800'],
        'Completed' => ['Завършен', 'bg-green-100 text-green-800'],
        'On Hold' => ['На пауза', 'bg-orange-100 text-orange-800'],
        'Cancelled' => ['Отказан', 'bg-red-100 text-red-800'],
    ];

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status][0] ?? (string) $this->status;
    }

    public function statusClass(): string
    {
        return self::STATUSES[$this->status][1] ?? 'bg-gray-100 text-gray-800';
    }

    /** Темата от формата (new-system, modernization, support) с текста ѝ; други стойности (напр. „Проверка на сайт“) се показват както са. */
    public function serviceLabel(): ?string
    {
        return $this->service ? (config('creatium.contact_topics')[$this->service] ?? $this->service) : null;
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function payments()
    {
        return $this->hasMany(ClientPayment::class)->latest('period');
    }

    /** Клиенти на месечен абонамент (с месечна такса, без отказаните). */
    public function scopeSubscribers(Builder $query): Builder
    {
        return $query->where('monthly_fee', '>', 0)->where('status', '!=', 'Cancelled');
    }

    /**
     * Плащането за текущия месец (по София): платено, предстои (до деня на плащане) или закъснява.
     * Ако миналият месец е неплатен (а клиентът е бил на абонамент), закъснението се брои от него.
     *
     * @return array{state: string, period: string, due: \Illuminate\Support\Carbon, days: int}
     */
    public function paymentState(): array
    {
        $today = now('Europe/Sofia')->startOfDay();
        $dueIn = fn ($month) => $month->copy()->day(min(max(1, (int) ($this->billing_day ?: 1)), $month->daysInMonth));
        $paid = $this->relationLoaded('payments') ? $this->payments->pluck('period')->all() : $this->payments()->pluck('period')->all();

        $previous = $today->copy()->startOfMonth()->subMonth();
        $startedBefore = $this->created_at && $this->created_at->timezone('Europe/Sofia')->lt($today->copy()->startOfMonth());
        if ($startedBefore && $paid && ! in_array($previous->format('Y-m'), $paid, true)) {
            $due = $dueIn($previous);

            return ['state' => 'late', 'period' => $previous->format('Y-m'), 'due' => $due, 'days' => (int) $due->diffInDays($today)];
        }

        $period = $today->format('Y-m');
        $due = $dueIn($today);
        if (in_array($period, $paid, true)) {
            return ['state' => 'paid', 'period' => $period, 'due' => $due, 'days' => 0];
        }

        return $today->lte($due)
            ? ['state' => 'upcoming', 'period' => $period, 'due' => $due, 'days' => (int) $today->diffInDays($due)]
            : ['state' => 'late', 'period' => $period, 'due' => $due, 'days' => (int) $due->diffInDays($today)];
    }

    public function notes()
    {
        return $this->hasMany(ProjectNote::class)->latest();
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    
    public function technologies()
    {
        return $this->belongsToMany(Technology::class);
    }
    
    public function scopeSearch(Builder $query, string $search): Builder
    {
        return $query->where('name', 'like', "%{$search}%")
            ->orWhere('description', 'like', "%{$search}%")
            ->orWhere('manager', 'like', "%{$search}%")
            ->orWhere('client_email', 'like', "%{$search}%")
            ->orWhere('client_phone', 'like', "%{$search}%")
            ->orWhere('status', 'like', "%{$search}%")
            ->orWhereHas('category', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            })
            ->orWhereHas('technologies', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
    }
}
