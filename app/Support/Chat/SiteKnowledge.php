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
        $out[] = __('Правим сайтове за бизнеси и се грижим хората да ги намират в Google.') . ' ' . (site('services_page')['intro'] ?? '');
        $out[] = 'Website: ' . lroute('home');

        // За нас: кои сме, историята и хората (Крео трябва да може да каже кои са собствениците).
        $about = app(\App\Support\Content::class)->withDefaults('about');
        $out[] = "\n## " . __('Кои сме ние') . ' (' . lroute('about') . ')';
        foreach (array_merge((array) ($about['paragraphs'] ?? []), (array) ($about['story'] ?? [])) as $paragraph) {
            $out[] = $paragraph;
        }
        $team = site('team') ?? [];
        if ($team) {
            $out[] = 'Founders and owners of Creatium Lab (the whole team; they do the work themselves, the client talks directly to them):';
            foreach ($team as $person) {
                $out[] = '- ' . trim(($person['name'] ?? '') . ', ' . ($person['role'] ?? ''), ', ') . ': ' . ($person['description'] ?? '')
                    . (! empty($person['bio']) ? ' ' . implode(' ', (array) $person['bio']) : '')
                    . (! empty($person['skills']) ? ' Skills: ' . implode(', ', (array) $person['skills']) . '.' : '');
            }
        }
        $legal = config('creatium.legal');
        $out[] = 'Company: ' . $legal['company'] . ($legal['eik'] ? ', EIK ' . $legal['eik'] : '') . ($legal['address'] ? ', ' . $legal['address'] : '') . '.';

        $problem = app(\App\Support\Content::class)->withDefaults('home_problem');
        if (filled($problem['solution_title'] ?? null)) {
            $out[] = "\n## " . $problem['solution_title'];
            $out[] = (string) ($problem['solution_text'] ?? '');
            foreach ((array) ($problem['stats'] ?? []) as $stat) {
                $out[] = '- ' . $stat['value'] . ' ' . $stat['text'] . ' (source: ' . $stat['source'] . ')';
            }
        }
        foreach ((array) ($problem['guide_items'] ?? []) as $i => $item) {
            if ($i === 0 && filled($problem['guide_label'] ?? null)) {
                $out[] = $problem['guide_label'] . ':';
            }
            $out[] = '- ' . $item['title'] . ': ' . $item['text'];
        }
        if (filled($problem['system_title'] ?? null) && ! empty($problem['system_steps'])) {
            $out[] = "\n## " . $problem['system_title'];
            $out[] = (string) ($problem['system_text'] ?? '');
            foreach ($problem['system_steps'] as $i => $step) {
                $out[] = ($i + 1) . '. ' . $step['title'] . ' (' . ($step['subtitle'] ?? '') . '): ' . $step['text'];
            }
            if (filled($problem['system_result'] ?? null)) {
                $out[] = '= ' . $problem['system_result'];
            }
        }

        // Дигиталният двигател: седемте нива на фунията и какво става, ако едно спре.
        $values = app(\App\Support\Content::class)->withDefaults('home_trust');
        if (! empty($values['funnel_levels'])) {
            $out[] = "\n## " . ($values['engine_title'] ?? 'Digital engine') . ' (' . lroute('services') . ')';
            $out[] = trim(($values['engine_subtitle'] ?? '') . '. ' . ($values['engine_text'] ?? ''), '. ');
            foreach ($values['funnel_levels'] as $i => $level) {
                $out[] = ($i + 1) . '. ' . $level['stage'] . (filled($level['tag'] ?? null) ? ' (' . $level['tag'] . ')' : '') . ': ' . ($level['works_title'] ?? '') . '. ' . ($level['works_text'] ?? '')
                    . ' ' . ($level['lesson'] ?? '') . ' If it stops: ' . ($level['off_title'] ?? '') . '. ' . ($level['off_text'] ?? '');
            }
        }
        if (filled($values['values_text'] ?? null)) {
            $out[] = "\n## " . ($values['fit_eyebrow'] ?? 'Values') . ': ' . ($values['values_title'] ?? '');
            $out[] = trim(($values['values_subtitle'] ?? '') . '. ' . $values['values_text'], '. ');
            foreach ((array) ($values['value_items'] ?? []) as $item) {
                $out[] = '- ' . $item['title'] . ': ' . $item['text'];
            }
        }
        if (! empty($values['demos_text'])) {
            $out[] = "\n## " . ($values['demos_title'] ?? 'Industries');
            $out[] = trim(($values['demos_subtitle'] ?? '') . '. ' . $values['demos_text'], '. ');
        }

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
            if (filled($plan['deposit'] ?? null)) {
                $out[] = 'Start price payment: ' . $plan['deposit'];
            }
            foreach (['build' => 'build_items', 'monthly' => 'monthly_items'] as $label => $field) {
                if (! empty($plan[$field])) {
                    $out[] = ($plan[$label . '_title'] ?? $label) . ': ' . implode('; ', (array) $plan[$field]);
                }
            }
        }

        $plansFull = app(\App\Support\Content::class)->withDefaults('plans');
        $out[] = "\n### " . __('Цени и плащане');
        $out[] = __('Цените са фиксирани. Стартовата цена се плаща на две части: първата сума преди да подготвим офертата и демото, останалата при съгласие. Не обещавай дали първата сума се връща, ако клиентът не продължи: това не е решено, насочи към консултация.');
        array_push($out, ...\App\Support\PlanBilling::textLines($plansFull));
        if (! empty($plansFull['compare'])) {
            $out[] = __('Сравнение') . ' (' . implode(' | ', array_map(fn ($p) => $p['name'], array_values($plansFull['items'] ?? []))) . '):';
            foreach ($plansFull['compare'] as $row) {
                $values = array_map(fn ($v) => match (strtolower(trim((string) $v))) { 'yes' => __('Да'), 'no', '' => __('Не'), default => $v }, array_values((array) ($row['values'] ?? [])));
                $out[] = '- ' . $row['label'] . ': ' . implode(' | ', $values);
            }
        }

        $process = app(\App\Support\Content::class)->withDefaults('home_process');
        if (! empty($process['steps'])) {
            $out[] = "\n## " . __('Работен процес');
            if (filled($process['text'] ?? null)) {
                $out[] = trim(($process['subtitle'] ?? '') . '. ' . $process['text'], '. ');
            }
            foreach ($process['steps'] as $i => $step) {
                $out[] = ($i + 1) . '. ' . $step['title'] . ': ' . $step['text'];
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
        $out[] = 'Get a quote for a package (short form, the team replies within one working day): ' . lroute('packages') . '#oferta';
        $out[] = 'Calculator of how much a better digital presence could improve visits and enquiries (on the Services page): ' . lroute('services') . '#kalkulator';
        $out[] = 'Free website check (report by email in about a minute): ' . lroute('site-check');
        $out[] = 'We reply to inquiries within one working day.';

        return trim(implode("\n", array_filter($out, fn ($line) => $line !== null && $line !== '')));
    }
}
