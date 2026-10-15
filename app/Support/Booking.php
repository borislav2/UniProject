<?php

namespace App\Support;

use App\Models\Project;
use Illuminate\Support\Carbon;

/**
 * Свободните часове за консултация във формата за контакт (creatium.booking):
 * работни дни от утре нататък, фиксирани часове в българско време, без часовете, вече заявени от друго запитване.
 */
class Booking
{
    /** @return list<array{date: Carbon, slots: list<array{value: string, time: string, taken: bool}>}> */
    public static function days(): array
    {
        $config = config('creatium.booking');
        $tz = $config['timezone'];
        $taken = Project::whereNotNull('consultation_at')->where('consultation_at', '>', now())
            ->pluck('consultation_at')
            ->map(fn ($at) => Carbon::parse($at)->timezone($tz)->format('Y-m-d H:i'))
            ->flip();

        $days = [];
        $day = now($tz)->startOfDay();
        while (count($days) < $config['days']) {
            $day = $day->copy()->addDay();
            if ($day->isWeekend()) {
                continue;
            }
            $days[] = [
                'date' => $day->copy(),
                'slots' => array_map(function ($time) use ($day, $taken) {
                    $value = $day->format('Y-m-d') . ' ' . $time;

                    return ['value' => $value, 'time' => $time, 'taken' => isset($taken[$value])];
                }, $config['times']),
            ];
        }

        return $days;
    }

    /** A free slot in Sofia time ("2026-10-16 11:00") as a UTC moment, or null if it is not offered or already taken. */
    public static function resolve(?string $value): ?Carbon
    {
        foreach (self::days() as $day) {
            foreach ($day['slots'] as $slot) {
                if ($slot['value'] === $value && ! $slot['taken']) {
                    return Carbon::createFromFormat('Y-m-d H:i', $value, config('creatium.booking.timezone'))->utc();
                }
            }
        }

        return null;
    }

    /** "четвъртък, 16 октомври, 11:00" in the current language and Sofia time. */
    public static function label(Carbon $at): string
    {
        return $at->copy()->timezone(config('creatium.booking.timezone'))->locale(app()->getLocale())->isoFormat('dddd, D MMMM, HH:mm');
    }

    /** Link that opens a prefilled Google Calendar event. */
    public static function googleCalendarUrl(Carbon $at, string $title, string $details = ''): string
    {
        $start = $at->copy()->utc();
        $end = $start->copy()->addMinutes(config('creatium.booking.minutes'));

        return 'https://calendar.google.com/calendar/render?' . http_build_query([
            'action' => 'TEMPLATE',
            'text' => $title,
            'dates' => $start->format('Ymd\THis\Z') . '/' . $end->format('Ymd\THis\Z'),
            'details' => $details,
        ]);
    }
}
