<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChatConversation;
use App\Models\OutreachCall;
use App\Models\OutreachProspect;
use App\Models\Post;
use App\Models\Project;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * Едно търсене за всичко (Ctrl+K): запитвания и проекти, потенциални клиенти, обаждания, разговори с Крео, статии.
 * Телефоните се сравняват само по цифрите („0888 12 34 56“ = „0888123456“), последните 9 цифри стигат.
 */
class SearchController extends Controller
{
    public function __invoke(Request $request)
    {
        $q = trim((string) $request->query('q'));
        $results = [];

        if (mb_strlen($q) >= 2) {
            $like = '%' . str_replace(['%', '_'], ['\%', '\_'], $q) . '%';
            $digits = preg_replace('/\D/', '', $q);
            $phone = strlen($digits) >= 6 ? '%' . substr($digits, -9) . '%' : null;
            $phoneMatch = fn (Builder $query, string $column) => $phone
                ? $query->orWhereRaw("REPLACE(REPLACE(REPLACE(REPLACE({$column}, ' ', ''), '-', ''), '+', ''), '/', '') LIKE ?", [$phone])
                : $query;

            $results = [
                'Запитвания и проекти' => Project::where(function ($w) use ($like, $phoneMatch) {
                    $w->where('name', 'like', $like)->orWhere('client_email', 'like', $like)->orWhere('website_url', 'like', $like)->orWhere('description', 'like', $like);
                    $phoneMatch($w, 'client_phone');
                })->latest()->limit(15)->get()->map(fn ($p) => [
                    'title' => $p->name, 'meta' => collect([$p->stageLabel() ?? $p->statusLabel(), $p->client_phone, $p->client_email])->filter()->join(' · '),
                    'url' => route('admin.projects.show', $p),
                ]),
                'Потенциални клиенти (писма)' => OutreachProspect::where(function ($w) use ($like, $phoneMatch) {
                    $w->where('company', 'like', $like)->orWhere('email', 'like', $like)->orWhere('website', 'like', $like);
                    $phoneMatch($w, 'phone');
                })->latest()->limit(10)->get()->map(fn ($p) => [
                    'title' => $p->displayName(), 'meta' => collect([OutreachProspect::STATUSES[$p->status] ?? $p->status, $p->email, $p->phone])->filter()->join(' · '),
                    'url' => $p->project_id ? route('admin.projects.show', $p->project_id) : route('admin.outreach.compose', $p),
                ]),
                'Обаждания' => OutreachCall::where(function ($w) use ($like, $phoneMatch) {
                    $w->where('name', 'like', $like)->orWhere('note', 'like', $like);
                    $phoneMatch($w, 'phone');
                })->latest()->limit(10)->get()->map(fn ($c) => [
                    'title' => $c->name, 'meta' => collect([OutreachCall::STATUSES[$c->status] ?? $c->status, $c->phone, $c->city])->filter()->join(' · '),
                    'url' => $c->project_id ? route('admin.projects.show', $c->project_id) : route('admin.outreach.calls.next', ['call' => $c->id]),
                ]),
                'Разговори с Крео' => ChatConversation::whereHas('messages', fn ($m) => $m->where('text', 'like', $like))
                    ->latest()->limit(5)->get()->map(fn ($c) => [
                        'title' => 'Разговор от ' . $c->created_at->timezone('Europe/Sofia')->format('d.m.Y H:i'), 'meta' => $c->page, 'url' => route('admin.chats.show', $c),
                    ]),
                'Статии' => Post::where('title->bg', 'like', $like)->latest()->limit(5)->get()->map(fn ($p) => [
                    'title' => $p->trOrBg('title'), 'meta' => $p->status === 'published' ? 'публикувана' : 'чернова', 'url' => route('admin.posts.edit', $p),
                ]),
            ];
            $results = array_filter($results, fn ($items) => $items->isNotEmpty());
        }

        return view('admin.search', ['q' => $q, 'results' => $results]);
    }
}
