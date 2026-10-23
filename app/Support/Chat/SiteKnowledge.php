<?php

namespace App\Support\Chat;

use App\Http\Controllers\PageController;

/**
 * Какво знае чатът: услугите, пакетите с цените, честите въпроси, процесът и контактите,
 * взети от съдържанието на сайта (site()) на текущия език. Промените от админа влизат веднага.
 */
class SiteKnowledge
{
    public static function text(): string
    {
        $contact = config('creatium.contact');
        $trust = site('home_trust') ?? [];
        $out = [];

        $out[] = '# Creatium Lab';
        $out[] = __('Правим сайтове за малки бизнеси и се грижим хората да ги намират в Google.') . ' ' . (site('services_page')['intro'] ?? '');
        $out[] = 'Website: ' . lroute('home');

        $out[] = "\n## " . __('Услуги');
        foreach (site('service_pages') ?? [] as $key => $page) {
            $out[] = "\n### " . $page['title'] . ' (' . PageController::serviceUrl($key) . ')';
            $out[] = $page['lead'];
            if (! empty($page['includes'])) {
                $out[] = 'Includes: ' . implode('; ', (array) $page['includes']);
            }
            if (filled($page['result'] ?? null)) {
                $out[] = 'Result: ' . $page['result'];
            }
            if (filled($page['note'] ?? null)) {
                $out[] = 'Note: ' . $page['note'];
            }
            foreach ($page['faq'] ?? [] as [$q, $a]) {
                $out[] = "Q: $q\nA: $a";
            }
        }

        $plans = site('plans') ?? [];
        $out[] = "\n## " . __('Пакети') . ' (' . lroute('packages') . ')';
        foreach ($plans['items'] ?? [] as $plan) {
            $out[] = "\n### " . $plan['name'];
            $out[] = $plan['audience'] ?? '';
            $out[] = 'Prices: ' . implode('; ', (array) ($plan['prices'] ?? []));
            foreach (['build' => 'build_items', 'monthly' => 'monthly_items'] as $label => $field) {
                if (! empty($plan[$field])) {
                    $out[] = ($plan[$label . '_title'] ?? $label) . ': ' . implode('; ', (array) $plan[$field]);
                }
            }
        }

        $process = site('home_process') ?? [];
        if (! empty($process['steps'])) {
            $out[] = "\n## " . __('Работен процес');
            foreach ($process['steps'] as $i => $step) {
                $out[] = ($i + 1) . '. ' . $step['title'] . ': ' . $step['text'];
            }
        }

        if (! empty($trust['fit_items'])) {
            $out[] = "\n## " . ($trust['fit_eyebrow'] ?? 'Who we work with');
            foreach ($trust['fit_items'] as $item) {
                $out[] = '- ' . $item['title'] . ': ' . $item['text'];
            }
            if (! empty($trust['not_items'])) {
                $out[] = ($trust['not_title'] ?? 'Not a fit') . ': ' . implode('; ', $trust['not_items']);
            }
        }
        if (! empty($trust['principle_items'])) {
            $out[] = "\n## " . ($trust['principles_eyebrow'] ?? 'Principles');
            foreach ($trust['principle_items'] as $item) {
                $out[] = '- ' . $item['title'] . ': ' . $item['text'];
            }
        }

        if ($questions = site('questions')) {
            $out[] = "\n## " . __('Често задавани въпроси');
            foreach ($questions as $item) {
                $out[] = 'Q: ' . $item['q'] . "\nA: " . $item['a'];
            }
        }

        if ($demos = site('demos')) {
            $out[] = "\n## Demo websites (" . lroute('portfolio') . ')';
            $out[] = 'Fictional businesses built to show what we do for each industry, marked "Demo": '
                . implode('; ', array_map(fn ($d) => $d['name'] . ' (' . $d['industry'] . ')', $demos)) . '.';
        }

        $out[] = "\n## " . __('Контакти');
        $out[] = 'Email: ' . $contact['email'];
        $out[] = 'Phone: ' . implode(', ', $contact['phones']);
        $out[] = 'Location: ' . __($contact['city']) . ', ' . __('България') . '; we work with businesses across Bulgaria.';
        $out[] = 'Free consultation (' . config('creatium.booking.minutes') . ' minutes, pick a day and time on weekdays): ' . lroute('contact') . '#konsultatsiya';
        $out[] = 'Contact form: ' . lroute('contact');
        $out[] = 'Free website check (report by email in about a minute): ' . lroute('site-check');
        $out[] = 'We reply to inquiries within one working day.';

        return trim(implode("\n", array_filter($out, fn ($line) => $line !== null && $line !== '')));
    }
}
