<?php

namespace App\Support\Admin;

use App\Models\ClientEmail;
use App\Models\LinkedinProspect;
use App\Models\OutreachCall;
use App\Models\OutreachProspect;
use App\Models\Post;
use App\Models\Project;
use App\Models\ReviewReply;
use App\Models\User;
use App\Support\Facebook\FacebookPublisher;
use App\Support\LinkedIn\LinkedInPublisher;
use App\Support\Outreach\Outreach;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Блоковете на „Днес“. Всеки блок е масив, който общият шаблон (admin/today/_block) рисува еднакво:
 * ключ, заглавие, пояснение, брой, до $limit реда с преки действия, връзка „Виж всички“ и текст, когато няма нищо.
 * Ред: title, url, chips [[текст, клас]], sub, tone (danger|warn|info), phone, actions [[текст, url, метод, primary]], pipeline (Project).
 * Кой блок за кой профил е: Profiles::todayBlocks(). $member е човекът, чийто ден гледаме (null = целият екип).
 */
class TodayBlocks
{
    private const QUIET = 'Няма нищо за днес';

    public function __construct(private ?User $member = null, private int $limit = 7) {}

    /** @param list<string> $keys @return array<string, array> */
    public function make(array $keys): array
    {
        $blocks = [];
        foreach ($keys as $key) {
            $method = 'block' . Str::studly(str_replace('-', '_', $key));
            $blocks[$key] = ['key' => $key] + $this->{$method}();
        }

        return $blocks;
    }

    private function userId(): ?int
    {
        return $this->member?->id;
    }

    /** Запитвания на човека и неразпределените (за целия екип: всички). */
    private function only(Collection $items): Collection
    {
        $id = $this->userId();

        return $id ? $items->filter(fn ($p) => ! $p->assigned_to || $p->assigned_to === $id)->values() : $items;
    }

    private function block(string $title, Collection $rows, array $extra = []): array
    {
        $count = $extra['count'] ?? $rows->count();
        unset($extra['count']);

        return array_merge([
            'title' => $title,
            'count' => $count,
            'rows' => $rows->take($this->limit)->values()->all(),
            'rest' => max(0, $count - $this->limit),
            'empty' => self::QUIET,
        ], $extra);
    }

    private function waiting($at): string
    {
        $hours = (int) $at->diffInHours(now());

        return $hours < 1 ? 'току-що' : ($hours < 24 ? "чака {$hours} ч" : 'чака ' . intdiv($hours, 24) . ' дни');
    }

    private function sofia($at)
    {
        return $at->copy()->timezone('Europe/Sofia');
    }

    // ---- Продажби ----

    private function blockSalesConsultations(): array
    {
        $rows = $this->only(Today::consultations())->map(function ($p) {
            $at = $this->sofia($p->consultation_at);

            return [
                'title' => ($at->isToday() ? 'Днес' : 'Утре') . ', ' . $at->format('H:i') . ' · ' . $p->name,
                'url' => route('admin.projects.show', $p),
                'tone' => $at->isToday() ? 'info' : null,
                'sub' => Str::limit($p->serviceLabel(), 70),
                'phone' => $p->client_phone,
            ];
        });

        return $this->block('Консултации', $rows, ['url' => route('admin.inquiries.index')]);
    }

    private function blockSalesUnread(): array
    {
        $rows = $this->only(Today::unread())->map(function ($lead) {
            $check = $lead->siteCheckSummary();
            $late = $lead->created_at->lt(now()->subHours(4));
            $sub = collect([Str::limit($lead->serviceLabel(), 60), $lead->lead_channel])->filter()->join(' · ');
            $chips = [];
            if ($lead->leadKind()) {
                $chips[] = [$lead->leadKindLabel(), $lead->leadKindClass()];
            }
            $chips[] = $lead->assignee ? [$lead->assignee->name, 'bg-gray-800 text-white'] : ['Без човек', 'border border-dashed border-orange-500 text-orange-700'];
            $detail = $check && $check['score'] !== null
                ? 'Оценка ' . $check['score'] . '/100 · ' . count($check['fail']) . ' ' . (count($check['fail']) === 1 ? 'проблем' : 'проблема') . ($check['fail'] ? ': ' . collect($check['fail'])->pluck('title')->take(2)->join(', ') : '')
                : Str::limit((string) $lead->description, 140);
            $actions = [];
            if (! $lead->assigned_to) {
                $actions[] = ['Поемам го', route('admin.inquiries.take', $lead), 'post', true];
            }
            $actions[] = ['Имейл', route('admin.client-emails.index', ['project' => $lead->id]), 'get', false];

            return [
                'title' => $lead->name,
                'url' => route('admin.projects.show', $lead),
                'chips' => $chips,
                'wait' => [$this->waiting($lead->created_at), $late],
                'sub' => $sub,
                'detail' => $detail,
                'tone' => $late ? 'danger' : 'info',
                'phone' => $lead->client_phone,
                'actions' => $actions,
                'pipeline' => $lead,
                'pipeline_label' => 'Обнови етапа и следващата стъпка',
            ];
        });

        return $this->block('Нови запитвания', $rows, ['hint' => 'отговорете възможно най-бързо', 'url' => route('admin.inquiries.index', ['filter' => 'unread']), 'empty' => 'Няма непрочетени запитвания.']);
    }

    private function blockSalesDue(): array
    {
        $rows = $this->only(Today::due())->map(function ($lead) {
            $at = $this->sofia($lead->next_action_at);
            $overdue = ! $at->isToday();

            return [
                'title' => $lead->name,
                'url' => route('admin.projects.show', $lead),
                'chips' => [[$lead->stageLabel(), $lead->stageClass()]],
                'sub' => '→ ' . ($lead->next_action ?: 'Свържете се') . ' · ' . ($overdue ? 'от ' . $at->format('d.m') : 'днес ' . $at->format('H:i')),
                'sub_tone' => $overdue ? 'danger' : null,
                'tone' => $overdue ? 'danger' : null,
                'phone' => $lead->client_phone,
                'pipeline' => $lead,
                'pipeline_label' => 'Готово? Запишете следващата стъпка',
            ];
        });

        return $this->block('Следващи стъпки за днес', $rows, ['url' => route('admin.inquiries.index'), 'empty' => 'Няма стъпки за днес.']);
    }

    /** Обаждания днес срещу целта, обажданията отново и бутон за следващото обаждане (специален блок, не се свива). */
    private function blockSalesCalls(): array
    {
        $calls = OutreachCall::todayStats();
        $callbacks = Today::callbacks();

        return [
            'kind' => 'calls',
            'title' => 'Обаждания',
            'count' => $callbacks,
            'calls' => $calls,
            'callbacks' => $callbacks,
            'emails' => ['sent' => Outreach::sentToday(), 'limit' => (int) config('creatium.outreach.daily_limit')],
            'rows' => [],
            'rest' => 0,
            'url' => route('admin.outreach.calls.index'),
        ];
    }

    private function blockSalesHot(): array
    {
        $rows = Today::hotProspects()->map(function ($p) {
            $actions = [];
            if ($p->project_id) {
                $actions[] = ['Запитване →', route('admin.projects.show', $p->project_id), 'get', false];
            } else {
                $actions[] = ['→ Запитване', route('admin.outreach.lead', $p), 'post', false, 'Прояви интерес: направи запитване с етапи и следваща стъпка'];
            }

            return [
                'title' => $p->displayName(),
                'sub' => 'Кликна: ' . (OutreachProspect::CLICK_LINKS[$p->last_click_link] ?? $p->last_click_link) . ' · ' . $this->sofia($p->last_clicked_at)->format('d.m H:i') . ($p->click_count > 1 ? " ({$p->click_count}×)" : ''),
                'phone' => $p->phone,
                'mailto' => $p->email,
                'tone' => 'info',
                'actions' => $actions,
            ];
        });

        return $this->block('Кликнаха по писмото', $rows, ['hint' => 'топли контакти, обадете се или пишете', 'url' => route('admin.outreach.index', ['clicked' => 1])]);
    }

    private function blockSalesFollowups(): array
    {
        $due = OutreachProspect::dueForFollowUp();
        $count = (clone $due)->count();
        $rows = (clone $due)->orderBy('sent_at')->limit($this->limit)->get()->map(fn ($p) => [
            'title' => $p->displayName(),
            'sub' => 'Писмо от ' . $this->sofia($p->sent_at)->format('d.m') . ', без отговор',
            'actions' => [['Напомни', route('admin.outreach.follow-up', $p), 'post', false]],
        ]);

        return $this->block('За напомняне', $rows, [
            'count' => $count,
            'hint' => 'без отговор ' . config('creatium.outreach.followup_after_days') . '+ дни',
            'url' => route('admin.outreach.index', ['followup' => 1]),
            'empty' => 'Никой не чака напомняне.',
        ]);
    }

    private function blockSalesNostep(): array
    {
        $rows = $this->only(Today::withoutNextStep())->map(fn ($lead) => [
            'title' => $lead->name,
            'url' => route('admin.projects.show', $lead),
            'chips' => [[$lead->stageLabel(), $lead->stageClass()]],
            'tone' => 'warn',
            'phone' => $lead->client_phone,
            'pipeline' => $lead,
            'pipeline_label' => 'Запишете следваща стъпка',
        ]);

        return $this->block('Без следваща стъпка', $rows, ['hint' => 'лесно се забравят', 'url' => route('admin.inquiries.index')]);
    }

    // ---- Сайтове и техника ----

    private function blockDevProjects(): array
    {
        $id = $this->userId();
        $query = Project::clientProjects()->where('status', 'In Progress')->with('assignee')
            ->when($id, fn ($q) => $q->orderByRaw('case when assigned_to = ? then 0 else 1 end', [$id]))
            ->orderByRaw('end_date is null')->orderBy('end_date');
        $rows = $query->get()->map(function ($p) use ($id) {
            $late = $p->end_date && $p->end_date->isPast();

            return [
                'title' => $p->name,
                'url' => route('admin.projects.show', $p),
                'chips' => array_values(array_filter([
                    $p->assignee ? [$p->assignee->id === $id ? 'Мой' : $p->assignee->name, $p->assignee->id === $id ? 'bg-brand-600 text-white' : 'bg-gray-100 text-gray-700'] : null,
                ])),
                'sub' => $p->end_date ? ($late ? 'Срокът мина на ' : 'Срок ') . $p->end_date->format('d.m.Y') : 'Без краен срок',
                'sub_tone' => $late ? 'danger' : null,
                'tone' => $late ? 'danger' : null,
                'actions' => [['Редакция', route('admin.projects.edit', $p), 'get', false]],
            ];
        });

        return $this->block('Проекти в работа', $rows, ['url' => route('admin.projects.index', ['filter' => 'active']), 'empty' => 'Няма проекти в работа.']);
    }

    /** Абонати със спрял сайт, изтичащ SSL/домейн, закъсняло или наближаващо плащане (до 5 дни). */
    private function blockDevClients(): array
    {
        $rows = Project::subscribers()->with('payments')->orderBy('name')->get()->map(function ($client) {
            $payment = $client->paymentState();
            $problems = SiteMonitor::problems($client);
            $chips = [];
            foreach ($problems as $problem) {
                $chips[] = [$problem, 'bg-red-100 text-red-800'];
            }
            if ($payment['state'] === 'late') {
                $chips[] = ['Плащането закъснява ' . $payment['days'] . ' дни', 'bg-red-100 text-red-800'];
            } elseif ($payment['state'] === 'upcoming' && $payment['days'] <= 5) {
                $chips[] = [$payment['days'] === 0 ? 'Плащане днес' : 'Плащане след ' . $payment['days'] . ' дни', 'bg-amber-100 text-amber-800'];
            }
            if (! $chips) {
                return null;
            }
            $actions = [];
            if ($payment['state'] !== 'paid' && ($payment['state'] === 'late' || $payment['days'] <= 5)) {
                $actions[] = ['Платено', route('admin.clients.paid', $client), 'post', false];
            }
            if ($client->website_url) {
                $actions[] = ['Провери сега', route('admin.clients.check', $client), 'post', false];
            }

            return [
                'title' => $client->name,
                'url' => route('admin.clients.index'),
                'chips' => $chips,
                'sub' => $client->website_url ?: null,
                'tone' => $problems || $payment['state'] === 'late' ? 'danger' : 'warn',
                'phone' => $client->client_phone,
                'actions' => $actions,
            ];
        })->filter()->sortBy(fn ($r) => $r['tone'] === 'danger' ? 0 : 1)->values();

        return $this->block('Клиенти на абонамент', $rows, ['hint' => 'сайтове и плащания', 'url' => route('admin.clients.index'), 'empty' => 'Сайтовете работят, плащанията са наред.']);
    }

    private function blockDevSiteChecks(): array
    {
        $rows = Project::siteChecks()->whereNull('read_at')->latest()->get()->map(function ($p) {
            $summary = $p->siteCheckSummary();
            $score = $summary['score'] ?? null;

            return [
                'title' => $summary['domain'] ?? $p->website_url ?? $p->name,
                'url' => route('admin.projects.show', $p),
                'chips' => $score !== null ? [['Оценка ' . $score . '/100', $score >= 80 ? 'bg-green-100 text-green-800' : ($score >= 50 ? 'bg-amber-100 text-amber-800' : 'bg-red-100 text-red-800')]] : [],
                'sub' => collect($summary['fail'] ?? [])->pluck('title')->take(2)->join(', ') ?: null,
                'wait' => [$this->waiting($p->created_at), $p->created_at->lt(now()->subHours(24))],
                'tone' => 'info',
                'phone' => $p->client_phone,
                'actions' => [['Отвори', route('admin.projects.show', $p), 'get', false]],
            ];
        });

        return $this->block('Нови проверки на сайт', $rows, ['url' => route('admin.site-checks.index'), 'empty' => 'Няма непрочетени проверки.']);
    }

    private function blockDevLeads(): array
    {
        $id = $this->userId();
        $rows = Project::openLeads()->with('assignee')
            ->when($id, fn ($q) => $q->where('assigned_to', $id), fn ($q) => $q->whereNotNull('assigned_to'))
            ->whereNotNull('next_action_at')->orderBy('next_action_at')->get()->map(function ($lead) {
                $at = $this->sofia($lead->next_action_at);
                $overdue = $lead->next_action_at->lt(now()->startOfDay());

                return [
                    'title' => $lead->name,
                    'url' => route('admin.projects.show', $lead),
                    'chips' => [[$lead->stageLabel(), $lead->stageClass()]],
                    'sub' => '→ ' . ($lead->next_action ?: 'Свържете се') . ' · ' . ($at->isToday() ? 'днес ' . $at->format('H:i') : ($overdue ? 'от ' : 'на ') . $at->format('d.m')),
                    'sub_tone' => $overdue ? 'danger' : null,
                    'tone' => $overdue ? 'danger' : null,
                    'phone' => $lead->client_phone,
                    'pipeline' => $lead,
                    'pipeline_label' => 'Готово? Запишете следващата стъпка',
                ];
            });

        return $this->block($id ? 'Моите запитвания със следваща стъпка' : 'Запитвания със следваща стъпка', $rows, ['url' => route('admin.inquiries.index')]);
    }

    private function blockDevHealth(): array
    {
        $rows = collect(SystemHealth::warnings())->map(fn ($w) => [
            'title' => $w['name'],
            'url' => route('admin.health'),
            'chips' => [[$w['group'], 'bg-gray-100 text-gray-700']],
            'sub' => $w['text'],
            'fix' => $w['fix'] ?? null,
            'tone' => 'warn',
        ]);

        return $this->block('Здраве на системата', $rows, ['url' => route('admin.health'), 'empty' => 'Всичко е наред.']);
    }

    // ---- Бранд и съдържание ----

    private function blockBrandPosts(): array
    {
        $scheduled = Post::where('status', 'published')->where('published_at', '>', now())->orderBy('published_at')->get();
        $drafts = Post::where('status', 'draft')->latest('updated_at')->get();
        $rows = $scheduled->map(fn ($post) => [
            'title' => $post->trOrBg('title') ?: 'Без заглавие',
            'url' => route('admin.posts.edit', $post),
            'chips' => [['Насрочена', 'bg-sky-100 text-sky-800']],
            'sub' => 'Излиза ' . $this->sofia($post->published_at)->format('d.m.Y') . ' в ' . $this->sofia($post->published_at)->format('H:i'),
            'actions' => [['Редактирай', route('admin.posts.edit', $post), 'get', false]],
        ])->concat($drafts->map(fn ($post) => [
            'title' => $post->trOrBg('title') ?: 'Без заглавие',
            'url' => route('admin.posts.edit', $post),
            'chips' => [['Чернова', 'bg-amber-100 text-amber-800']],
            'sub' => 'Последна промяна ' . $this->sofia($post->updated_at)->format('d.m.Y'),
            'tone' => 'warn',
            'actions' => [['Редактирай', route('admin.posts.edit', $post), 'get', false]],
        ]));

        return $this->block('Блог: насрочени и чернови', $rows, [
            'count' => $scheduled->count() + $drafts->count(),
            'extra' => ['scheduled' => $scheduled->count(), 'drafts' => $drafts->count()],
            'url' => route('admin.posts.index'),
            'empty' => 'Няма насрочени статии и чернови.',
        ]);
    }

    /** Facebook и LinkedIn: само проблемите (нужен е ключ, спряно, изтича, неуспешна публикация) с бутон „Опитай пак“. */
    private function blockBrandAutopost(): array
    {
        $rows = collect();
        $summary = [];
        $fbPaused = FacebookPublisher::paused();
        if (! FacebookPublisher::configured()) {
            $summary[] = 'Facebook не е свързан';
        } elseif ($fbPaused) {
            $rows->push(['title' => 'Facebook е спрян', 'sub' => $fbPaused, 'tone' => 'danger', 'fix' => 'php artisan creatium:facebook-connect', 'url' => route('admin.posts.index')]);
        } else {
            $summary[] = 'Facebook излиза автоматично';
        }
        $liDays = LinkedInPublisher::daysLeft();
        if (! LinkedInPublisher::configured()) {
            $summary[] = 'LinkedIn не е свързан';
        } elseif (LinkedInPublisher::expired()) {
            $rows->push(['title' => 'Ключът за LinkedIn е изтекъл', 'sub' => 'Статиите не излизат, докато не се поднови.', 'tone' => 'danger', 'fix' => 'php artisan creatium:linkedin-connect', 'url' => route('admin.posts.index')]);
        } elseif (LinkedInPublisher::paused()) {
            $rows->push(['title' => 'LinkedIn е спрян', 'sub' => LinkedInPublisher::paused(), 'tone' => 'danger', 'fix' => 'php artisan creatium:linkedin-connect', 'url' => route('admin.posts.index')]);
        } elseif ($liDays !== null && $liDays < 10) {
            $rows->push(['title' => "Ключът за LinkedIn изтича след {$liDays} " . ($liDays === 1 ? 'ден' : 'дни'), 'sub' => 'Подновете го навреме.', 'tone' => 'warn', 'fix' => 'php artisan creatium:linkedin-connect', 'url' => route('admin.posts.index')]);
        } else {
            $summary[] = 'LinkedIn излиза автоматично';
        }

        $failed = Post::where(fn ($q) => $q->where(fn ($w) => $w->whereNotNull('facebook_error')->whereNull('facebook_post_id'))->orWhere(fn ($w) => $w->whereNotNull('linkedin_error')->whereNull('linkedin_post_id')))->latest('updated_at')->limit(10)->get();
        foreach ($failed as $post) {
            foreach (['facebook' => 'Facebook', 'linkedin' => 'LinkedIn'] as $net => $name) {
                $error = $post->{$net . '_error'};
                if ($error && ! $post->{$net . '_post_id'}) {
                    $rows->push([
                        'title' => ($post->trOrBg('title') ?: 'Статия'),
                        'url' => route('admin.posts.edit', $post),
                        'chips' => [[$name . ': грешка', 'bg-red-100 text-red-800']],
                        'sub' => Str::limit($error, 120),
                        'tone' => 'danger',
                        'actions' => [['Опитай пак', route('admin.posts.' . $net, $post), 'post', true]],
                    ]);
                }
            }
        }

        return $this->block('Публикуване във Facebook и LinkedIn', $rows, ['url' => route('admin.posts.index'), 'empty' => implode(' · ', $summary) ?: self::QUIET]);
    }

    private function blockBrandLinkedin(): array
    {
        $id = $this->userId();
        $due = Today::linkedinDue($id);
        $today = \App\Support\Admin\TeamActivity::counts($id, \App\Support\Admin\TeamActivity::startOfToday());
        $limit = (int) config('creatium.outreach.linkedin_daily_invites');
        $rows = $due->map(function (LinkedinProspect $c) {
            $past = $c->next_action_at->toDateString() < now('Europe/Sofia')->toDateString();

            return [
                'title' => $c->name,
                'url' => $c->profile_url,
                'external' => true,
                'chips' => [[$c->statusLabel(), $c->statusClass()]],
                'sub' => ($past ? 'от ' . $c->next_action_at->format('d.m') : 'днес') . ($c->note ? ' · ' . Str::limit($c->note, 90) : ''),
                'sub_tone' => $past ? 'danger' : null,
                'tone' => $past ? 'danger' : null,
                'actions' => [['Отбележи →', route('admin.linkedin.index', ['due' => 1, 'q' => $c->name]), 'get', false]],
            ];
        });

        return $this->block('LinkedIn: време е да напишете', $rows, [
            'hint' => "днес {$today['linkedin_invites']} / {$limit} покани, {$today['linkedin_messages']} съобщения",
            'url' => route('admin.linkedin.index', ['due' => 1]),
            'empty' => "Няма контакти за днес. Покани днес: {$today['linkedin_invites']} / {$limit}.",
            'meter' => [$today['linkedin_invites'], $limit],
        ]);
    }

    private function blockBrandReviews(): array
    {
        $rows = ReviewReply::latest()->where('created_at', '>=', now()->subDays(7))->get()->map(fn ($r) => [
            'title' => $r->business_name,
            'url' => route('admin.reviews.show', $r),
            'chips' => [[str_repeat('★', (int) $r->rating), 'bg-amber-100 text-amber-800']],
            'sub' => collect([$r->reviewer_name, $this->sofia($r->created_at)->format('d.m H:i')])->filter()->join(' · '),
            'actions' => [['Отвори', route('admin.reviews.show', $r), 'get', false]],
        ]);

        return $this->block('Отговори на отзиви', $rows, ['hint' => 'последните 7 дни', 'url' => route('admin.reviews.index'), 'empty' => 'Няма нови отговори тази седмица.']);
    }

    /** Абонати, на които още не е изпратен месечен отчет този месец. */
    private function blockBrandReports(): array
    {
        $sent = ClientEmail::where('template', 'monthly-report')->whereNotNull('sent_at')
            ->where('sent_at', '>=', now('Europe/Sofia')->startOfMonth()->utc())->pluck('project_id')->filter()->all();
        $rows = Project::subscribers()->whereNotIn('id', $sent)->orderBy('name')->get()->map(fn ($client) => [
            'title' => $client->name,
            'url' => route('admin.clients.index'),
            'sub' => collect([$client->package, $client->client_email])->filter()->join(' · '),
            'actions' => [['Месечен отчет', route('admin.client-emails.create', ['template' => 'monthly-report', 'project' => $client->id]), 'get', true]],
        ]);

        return $this->block('Месечни отчети за изпращане', $rows, ['hint' => $this->sofia(now())->locale('bg')->isoFormat('MMMM'), 'url' => route('admin.clients.index'), 'empty' => 'Всички отчети за месеца са изпратени.']);
    }

    private function blockBrandEmails(): array
    {
        $rows = ClientEmail::with('project')->whereNull('outreach_prospect_id')->where('created_at', '>=', now()->subDays(7))->latest()->limit(40)->get()->map(fn ($m) => [
            'title' => $m->subject,
            'chips' => array_values(array_filter([
                [$m->templateLabel(), 'bg-gray-100 text-gray-700'],
                $m->sent_at ? null : ['Не е изпратен', 'bg-red-100 text-red-800'],
            ])),
            'sub' => $m->to_email . ' · ' . $this->sofia($m->sent_at ?? $m->created_at)->format('d.m H:i'),
            'tone' => $m->sent_at ? null : 'danger',
            'url' => $m->project_id ? route('admin.projects.show', $m->project_id) : null,
        ]);

        return $this->block('Последни имейли до клиенти', $rows, ['hint' => 'последните 7 дни', 'url' => route('admin.client-emails.index'), 'empty' => 'Няма изпратени имейли тази седмица.']);
    }

    // ---- Числата най-горе ----

    /** @param array<string, array> $blocks @return list<array{label: string, value: mixed, of?: mixed, hot?: bool, note?: ?string, url?: ?string, meter?: ?array}> */
    public static function kpis(string $profile, array $blocks): array
    {
        $count = fn (string $key) => $blocks[$key]['count'] ?? 0;
        $calls = $blocks['sales-calls'] ?? null;

        $sales = fn () => [
            ['label' => 'Непрочетени', 'value' => $count('sales-unread'), 'hot' => $count('sales-unread') > 0, 'url' => route('admin.inquiries.index', ['filter' => 'unread'])],
            ['label' => 'Стъпки за днес', 'value' => $count('sales-due'), 'hot' => collect($blocks['sales-due']['rows'] ?? [])->contains(fn ($r) => ($r['tone'] ?? null) === 'danger')],
            ['label' => 'Обаждания', 'value' => $calls['calls']['total'] ?? 0, 'of' => $calls['calls']['goal'] ?? null, 'note' => ($calls['callbacks'] ?? 0) ? $calls['callbacks'] . ' отново днес' : null, 'url' => route('admin.outreach.calls.next'), 'meter' => [$calls['calls']['total'] ?? 0, $calls['calls']['goal'] ?? 1]],
            ['label' => 'Първи писма днес', 'value' => $calls['emails']['sent'] ?? 0, 'of' => $calls['emails']['limit'] ?? null, 'url' => route('admin.outreach.index'), 'meter' => [$calls['emails']['sent'] ?? 0, $calls['emails']['limit'] ?? 1]],
            ['label' => 'Кликнали по писмата', 'value' => $count('sales-hot'), 'hot' => $count('sales-hot') > 0],
        ];

        return match ($profile) {
            'dev' => [
                ['label' => 'Проекти в работа', 'value' => $count('dev-projects'), 'url' => route('admin.projects.index', ['filter' => 'active'])],
                ['label' => 'Клиенти с проблем', 'value' => $count('dev-clients'), 'hot' => $count('dev-clients') > 0, 'url' => route('admin.clients.index')],
                ['label' => 'Нови проверки', 'value' => $count('dev-site-checks'), 'url' => route('admin.site-checks.index')],
                ['label' => 'Със следваща стъпка', 'value' => $count('dev-leads')],
                ['label' => 'Предупреждения', 'value' => $count('dev-health'), 'hot' => $count('dev-health') > 0, 'url' => route('admin.health')],
            ],
            'brand' => [
                ['label' => 'Насрочени статии', 'value' => $blocks['brand-posts']['extra']['scheduled'] ?? 0, 'url' => route('admin.posts.index')],
                ['label' => 'Чернови', 'value' => $blocks['brand-posts']['extra']['drafts'] ?? 0, 'hot' => ($blocks['brand-posts']['extra']['drafts'] ?? 0) > 0],
                ['label' => 'LinkedIn за днес', 'value' => $count('brand-linkedin'), 'hot' => $count('brand-linkedin') > 0, 'url' => route('admin.linkedin.index', ['due' => 1]), 'meter' => $blocks['brand-linkedin']['meter'] ?? null],
                ['label' => 'Проблеми с публикуване', 'value' => $count('brand-autopost'), 'hot' => $count('brand-autopost') > 0],
                ['label' => 'Отчети за изпращане', 'value' => $count('brand-reports'), 'url' => route('admin.clients.index')],
            ],
            default => $sales(),
        };
    }
}
