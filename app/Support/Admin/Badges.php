<?php

namespace App\Support\Admin;

use App\Models\LinkedinProspect;
use App\Models\Project;

/**
 * Броячите в менюто на админа. Смятат се веднъж на заявка (памет в процеса, нулира се между заявките),
 * за да не ги прави всяко място от шаблона по отделно.
 */
class Badges
{
    private const KEY = 'admin.badges';

    /** Памет на заявката (атрибут на Request), не на процеса: тестове и дълги процеси не държат стари числа. */
    public static function all(): array
    {
        $attributes = request()->attributes;
        if (! $attributes->has(self::KEY)) {
            $attributes->set(self::KEY, self::compute());
        }

        return $attributes->get(self::KEY);
    }

    public static function get(?string $key): int
    {
        return $key ? (int) (self::all()[$key] ?? 0) : 0;
    }

    private static function compute(): array
    {
        $callbacks = Today::callbacks();
        $linkedin = LinkedinProspect::due()->count();

        return [
            'inquiries' => Project::formLeads()->whereNull('read_at')->count(),
            'site_checks' => Project::siteChecks()->whereNull('read_at')->count(),
            'late_payments' => Project::subscribers()->with('payments')->get()->filter(fn ($client) => $client->paymentState()['state'] === 'late')->count(),
            'callbacks' => $callbacks,
            'linkedin_due' => $linkedin,
            // „Днес“ брои спешното за целия екип (непрочетени, стъпки за днес, обаждания отново, LinkedIn).
            'today' => Today::urgentCount(),
        ];
    }
}
