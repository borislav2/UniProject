<?php

namespace App\Support;

use Illuminate\Support\Facades\Auth;

/**
 * Which public design is shown: `current` (the live site) or `g` (the redesign, concept G).
 *
 * `creatium.design` = `g` (CREATIUM_DESIGN) shows it to everyone. Otherwise only logged-in admin-panel users who opened
 * `?dizain=g` see it (kept in the session until `?dizain=star`), with noindex and a small „Изход“ pill, so the live site
 * does not change while the redesign is built. See App\Http\Middleware\DesignPreview.
 */
class Design
{
    public const SESSION_KEY = 'design';

    public static function configured(): string
    {
        return config('creatium.design') === 'g' ? 'g' : 'current';
    }

    /** A logged-in user with access to the admin panel (the same check as CheckAdminAccess). */
    public static function canPreview(): bool
    {
        return Auth::check() && Auth::user()->canAccessAdminPanel();
    }

    /** True when design g is shown only because an admin switched the preview on. */
    public static function preview(): bool
    {
        if (self::configured() === 'g') {
            return false;
        }

        return self::canPreview() && request()->hasSession() && request()->session()->get(self::SESSION_KEY) === 'g';
    }

    public static function current(): string
    {
        return self::configured() === 'g' || self::preview() ? 'g' : 'current';
    }

    public static function is(string $design): bool
    {
        return self::current() === $design;
    }
}
