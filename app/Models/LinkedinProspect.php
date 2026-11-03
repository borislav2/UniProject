<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Потенциален клиент от LinkedIn („Потенциални клиенти“ → „LinkedIn“). LinkedIn забранява автоматично събиране и
 * изпращане, затова екипът търси и пише на ръка, а админът само помни кого, докъде е стигнало и кога да напомним.
 * Нищо не се взима от LinkedIn и нищо не се праща натам.
 */
class LinkedinProspect extends Model
{
    /** Стъпките по ред: [етикет, клас на значката]. */
    public const STATUSES = [
        'found' => ['Намерен', 'bg-gray-100 text-gray-800'],
        'invited' => ['Покана изпратена', 'bg-blue-100 text-blue-800'],
        'connected' => ['Приета покана', 'bg-indigo-100 text-indigo-800'],
        'messaged' => ['Писано', 'bg-purple-100 text-purple-800'],
        'replied' => ['Отговори', 'bg-green-100 text-green-800'],
        'not_interested' => ['Не се интересува', 'bg-gray-200 text-gray-700'],
    ];

    /** Следващата стъпка за бързия бутон: [нов статус, текст на бутона]. */
    public const NEXT_STEP = [
        'found' => ['invited', 'Изпратих покана'],
        'invited' => ['connected', 'Прие'],
        'connected' => ['messaged', 'Писах'],
        'messaged' => ['replied', 'Отговори'],
    ];

    /** Статус → колона с времето, което се записва първия път, когато делото стигне до тази стъпка или по-нататък. */
    private const STEPS = ['invited' => 'invited_at', 'connected' => 'connected_at', 'messaged' => 'messaged_at', 'replied' => 'replied_at'];

    protected $fillable = [
        'profile_url', 'kind', 'name', 'title', 'company', 'city', 'industry', 'website', 'email', 'phone',
        'status', 'note', 'next_action_at', 'assigned_to', 'added_by', 'project_id',
    ];

    protected $casts = [
        'next_action_at' => 'date',
        'invited_at' => 'datetime',
        'connected_at' => 'datetime',
        'messaged_at' => 'datetime',
        'replied_at' => 'datetime',
    ];

    /**
     * Привежда адрес от LinkedIn към https://www.linkedin.com/in/<име> или /company/<име> (малки букви, без параметри
     * и наклонена черта накрая). Всичко друго (други сайтове, други видове страници) връща null.
     *
     * @return array{url: string, kind: string, slug: string}|null
     */
    public static function normalizeUrl(?string $raw): ?array
    {
        $raw = trim((string) $raw);
        if ($raw === '' || preg_match('/\s/', $raw)) {
            return null;
        }
        if (! preg_match('#^[a-z][a-z0-9+.-]*://#i', $raw)) {
            $raw = 'https://' . $raw;
        }
        $parts = parse_url($raw);
        if (! $parts || ! in_array(strtolower($parts['scheme'] ?? ''), ['http', 'https'], true) || isset($parts['user']) || isset($parts['port'])) {
            return null;
        }
        $host = strtolower(rtrim($parts['host'] ?? '', '.'));
        if ($host !== 'linkedin.com' && ! str_ends_with($host, '.linkedin.com')) {
            return null;
        }
        if (! preg_match('#^/(in|company)/([^/?\#]+)#i', $parts['path'] ?? '', $m)) {
            return null;
        }
        $slug = mb_strtolower(rawurldecode($m[2]));
        if ($slug === '' || preg_match('/[\x00-\x1f\s\/\\\\]/u', $slug)) {
            return null;
        }
        $kind = strtolower($m[1]) === 'company' ? 'company' : 'person';

        return ['url' => 'https://www.linkedin.com/' . ($kind === 'company' ? 'company' : 'in') . '/' . rawurlencode($slug), 'kind' => $kind, 'slug' => $slug];
    }

    /** Име от адреса, когато не е въведено: „ivan-petrov-12ab34“ → „Ivan Petrov“. */
    public static function nameFromSlug(string $slug): string
    {
        $slug = preg_replace('/-[0-9a-f]{6,}$/i', '', $slug);

        return Str::title(str_replace(['-', '_'], ' ', $slug)) ?: $slug;
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status][0] ?? $this->status;
    }

    public function statusClass(): string
    {
        return self::STATUSES[$this->status][1] ?? 'bg-gray-100 text-gray-800';
    }

    /**
     * Сменя статуса и записва кога (и от кого) е стигнато до стъпката, но само първия път и никога не изтрива.
     * Пропусната стъпка (от „Намерен“ направо на „Писано“) попълва и по-ранните. „Не се интересува“ не пипа времената.
     */
    public function moveTo(string $status, ?int $userId = null): void
    {
        $this->status = $status;
        $reached = array_search($status, array_keys(self::STEPS), true);
        if ($reached === false) {
            return;
        }
        foreach (array_slice(self::STEPS, 0, $reached + 1, true) as $step => $column) {
            if (! $this->{$column}) {
                $this->{$column} = now();
                if (in_array($step, ['invited', 'messaged'], true)) {
                    $this->{$step === 'invited' ? 'invited_by' : 'messaged_by'} = $userId;
                }
            }
        }
    }

    public function scopeDue(Builder $query): Builder
    {
        return $query->whereNotNull('next_action_at')->whereDate('next_action_at', '<=', now('Europe/Sofia')->toDateString())
            ->where('status', '!=', 'not_interested');
    }

    public function isDue(): bool
    {
        return $this->next_action_at && $this->next_action_at->toDateString() <= now('Europe/Sofia')->toDateString()
            && $this->status !== 'not_interested';
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function inviter()
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    public function messenger()
    {
        return $this->belongsTo(User::class, 'messaged_by');
    }

    /** Покани и съобщения днес (София), за дневната цел. */
    public static function todayStats(): array
    {
        $start = now('Europe/Sofia')->startOfDay()->utc();

        return [
            'invites' => self::where('invited_at', '>=', $start)->count(),
            'messages' => self::where('messaged_at', '>=', $start)->count(),
            'goal' => max(1, (int) config('creatium.outreach.linkedin_daily_invites')),
        ];
    }
}
