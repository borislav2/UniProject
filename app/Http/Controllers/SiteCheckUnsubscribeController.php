<?php

namespace App\Http\Controllers;

use App\Models\OutreachSuppression;
use App\Models\Project;
use Illuminate\Http\Request;

/**
 * Отписване от писмата след безплатната проверка на сайт (линкът в писмото и бутонът „Отписване“ на Gmail и Outlook, RFC 8058).
 * Подписан адрес, без вход и без CSRF (път otpisvane/). Спира писмата и записва адреса, за да не му пишем и по друг повод.
 */
class SiteCheckUnsubscribeController extends Controller
{
    public function __invoke(Request $request, Project $project)
    {
        abort_unless($project->leadKind() === 'site-check', 404);

        if (! $project->followup_stopped_at) {
            $project->forceFill(['followup_stopped_at' => now()])->save();
            $project->notes()->create(['user_id' => null, 'kind' => 'note', 'body' => 'Клиентът се отписа от писмата след проверката.']);
        }
        if (filled($project->client_email)) {
            OutreachSuppression::add($project->client_email, 'unsubscribed');
        }

        if ($request->isMethod('post')) {
            return response()->noContent();
        }

        return response()->view('site-check-unsubscribed', ['project' => $project]);
    }
}
