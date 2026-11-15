<?php

namespace App\Support\Admin;

use App\Models\Category;
use App\Models\LinkedinProspect;
use App\Models\Opportunity;
use App\Models\OutreachCall;
use App\Models\OutreachProspect;
use App\Models\Project;
use App\Models\ProjectNote;
use App\Models\User;

/**
 * Превръща потенциален клиент (от писмата или от обажданията) в запитване, когато прояви интерес:
 * с данните за връзка, етап „Свързахме се“ и бележка откъде идва. Повторно натискане отваря същото запитване.
 */
class LeadConverter
{
    public static function fromProspect(OutreachProspect $prospect, ?User $user = null): Project
    {
        if ($prospect->project_id && ($existing = Project::find($prospect->project_id))) {
            return $existing;
        }

        $lines = ['Потенциален клиент от писмата до бизнеси (' . $prospect->email . ').'];
        if ($prospect->clicked_at) {
            $lines[] = 'Кликна по писмото: ' . (OutreachProspect::CLICK_LINKS[$prospect->last_click_link] ?? $prospect->last_click_link) . ', ' . $prospect->last_clicked_at->timezone('Europe/Sofia')->format('d.m.Y H:i') . '.';
        }
        if ($prospect->findings) {
            $lines[] = "Какво забелязахме на сайта:\n– " . collect($prospect->findings)->pluck('title')->join("\n– ");
        }

        $lead = self::create($prospect->displayName(), implode("\n\n", $lines), 'Писма до бизнеси', [
            'client_email' => $prospect->email,
            'client_phone' => $prospect->phone,
            'website_url' => $prospect->website,
        ], $user);
        $prospect->forceFill(['project_id' => $lead->id])->save();

        return $lead;
    }

    public static function fromCall(OutreachCall $call, ?User $user = null): Project
    {
        if ($call->project_id && ($existing = Project::find($call->project_id))) {
            return $existing;
        }

        $lines = ['Бизнес от списъка за обаждане (' . collect([$call->industryLabel(), $call->city])->filter()->join(', ') . ').'];
        if ($call->note) {
            $lines[] = 'Бележка от разговора: ' . $call->note;
        }

        $lead = self::create($call->name, implode("\n\n", $lines), 'Обаждания', ['client_phone' => $call->phone], $user);
        $call->forceFill(['project_id' => $lead->id, 'status' => in_array($call->status, ['new', 'no_answer'], true) ? 'interested' : $call->status])->save();

        return $lead;
    }

    public static function fromLinkedin(LinkedinProspect $prospect, ?User $user = null): Project
    {
        if ($prospect->project_id && ($existing = Project::find($prospect->project_id))) {
            return $existing;
        }

        $lines = ['Потенциален клиент от LinkedIn (' . $prospect->statusLabel() . ').', 'Профил: ' . $prospect->profile_url];
        if ($about = collect([$prospect->title, $prospect->company, $prospect->city, $prospect->industry])->filter()->join(', ')) {
            $lines[] = $about . '.';
        }
        if ($prospect->note) {
            $lines[] = 'Бележка: ' . $prospect->note;
        }

        $lead = self::create($prospect->name . ($prospect->company && $prospect->kind === 'person' ? ' (' . $prospect->company . ')' : ''), implode("\n\n", $lines), 'LinkedIn', [
            'client_email' => $prospect->email,
            'client_phone' => $prospect->phone,
            'website_url' => $prospect->website,
            'assigned_to' => $prospect->assigned_to,
        ], $user);
        $prospect->forceFill(['project_id' => $lead->id])->save();

        return $lead;
    }

    /**
     * Възможност (поръчка, партньор, заявка от платформа) става проект в „Проекти“: работа, не запитване. Източникът е
     * „opportunity“, каналът „Възможности“; повторно натискане отваря същия проект. Нищо не се подава никъде.
     */
    public static function fromOpportunity(Opportunity $opportunity, ?User $user = null): Project
    {
        if ($opportunity->project_id && ($existing = Project::find($opportunity->project_id))) {
            return $existing;
        }

        $lines = [$opportunity->kindLabel() . ' от „' . $opportunity->sourceLabel() . '“, оценка ' . $opportunity->score . '/100.', $opportunity->summaryLine()];
        if ($opportunity->url) {
            $lines[] = 'Адрес: ' . $opportunity->url;
        }
        if ($opportunity->note) {
            $lines[] = 'Бележка: ' . $opportunity->note;
        }
        $category = Category::firstOrCreate(['name' => 'Възможности'], ['description' => 'Проекти, започнали от обществена поръчка, партньор или платформа за свободна практика']);

        $project = Project::create([
            'name' => 'Възможност: ' . mb_substr($opportunity->title, 0, 200),
            'description' => implode("\n\n", $lines),
            'start_date' => now()->toDateString(),
            'status' => 'Planning',
            'manager' => $user?->name ?? 'Неразпределен',
            'category_id' => $category->id,
            'source' => 'opportunity',
            'lead_channel' => 'Възможности',
            'assigned_to' => $opportunity->assigned_to ?? $user?->id,
            'read_at' => now(),
        ]);
        ProjectNote::create(['project_id' => $project->id, 'user_id' => $user?->id, 'kind' => 'note', 'body' => 'Създаден от възможност: ' . $opportunity->title . '.']);

        $opportunity->forceFill(['project_id' => $project->id])->save();
        if (in_array($opportunity->status, ['new', 'interesting'], true)) {
            $opportunity->moveTo('preparing', $user?->id);
        }

        return $project;
    }

    private static function create(string $name, string $description, string $channel, array $contact, ?User $user): Project
    {
        $category = Category::firstOrCreate(['name' => 'Ново запитване'], ['description' => 'Автоматично създадени запитвания от сайта, изчакващи категоризация']);

        $lead = Project::create($contact + [
            'name' => 'Запитване от ' . $name,
            'description' => $description,
            'start_date' => now()->toDateString(),
            'status' => 'Planning',
            'manager' => $user?->name ?? 'Неразпределен',
            'category_id' => $category->id,
            'source' => 'outreach',
            'lead_channel' => $channel,
            'stage' => 'contacted',
            'stage_changed_at' => now(),
            'read_at' => now(),
        ]);
        ProjectNote::create(['project_id' => $lead->id, 'user_id' => $user?->id, 'kind' => 'stage', 'body' => "Създадено от „{$channel}“ с етап „Свързахме се“."]);

        return $lead;
    }
}
