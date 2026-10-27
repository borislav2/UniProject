<?php

namespace App\Support\Admin;

use App\Models\Category;
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
