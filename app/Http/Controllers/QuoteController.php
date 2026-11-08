<?php

namespace App\Http\Controllers;

use App\Mail\NewLeadMail;
use App\Models\Category;
use App\Models\Project;
use App\Support\LeadAttribution;
use App\Support\PlanBilling;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

/**
 * „Получете оферта“ на /paketi (собствениците, ноември 2026): профил на клиента за избрания пакет.
 * Става запитване (Project, source website) като формата за контакт; след изпращане страницата показва благодарност.
 */
class QuoteController extends Controller
{
    public function store(Request $request)
    {
        // The Bulgarian plan names as the admin sees them (the lead and the team email are in Bulgarian).
        $plans = app(\App\Support\Content::class)->get('plans', 'bg')['items'] ?? [];
        $validated = $request->validate([
            'plan' => ['required', 'integer', Rule::in(array_keys($plans))],
            'name' => 'required|string|max:120',
            'email' => 'nullable|required_without:phone|email|max:255',
            'phone' => ['nullable', 'required_without:email', 'string', 'max:25', 'regex:/^[0-9+()\-\s]{6,25}$/'],
            'position' => 'nullable|string|max:120',
            'business' => 'required|string|max:150',
            'sector' => 'nullable|string|max:120',
            'city' => 'nullable|string|max:80',
            'site_url' => 'nullable|string|max:255',
            'goal' => ['nullable', 'string', Rule::in(array_keys(config('creatium.contact_goals')))],
            'timeline' => ['nullable', 'string', Rule::in(array_keys(config('creatium.contact_timelines')))],
            'consent' => 'accepted',
            \App\Support\Turnstile::FIELD => \App\Support\Turnstile::rules(),
        ], \App\Support\Turnstile::messages() + [
            'name.required' => __('Моля, въведете вашите имена.'),
            'email.required_without' => __('Моля, въведете имейл или телефон.'),
            'phone.required_without' => __('Моля, въведете имейл или телефон.'),
            'email.email' => __('Моля, въведете валиден имейл.'),
            'phone.regex' => __('Моля, въведете валиден телефонен номер.'),
            'business.required' => __('Моля, въведете името на бизнеса.'),
            'consent.accepted' => __('Моля, потвърдете, че сте запознати с Политиката за поверителност.'),
        ]);

        $back = redirect()->to(lroute('packages') . '#oferta');

        // Honeypot: real visitors never fill this hidden field.
        if ($request->filled('website')) {
            return $back->with('quote_sent', true);
        }

        // Admin and the team email are in Bulgarian, whatever the language of the page.
        $planName = $plans[$validated['plan']]['name'];
        $lines = array_filter([
            'Пакет: ' . $planName,
            'Позиция: ' . ($validated['position'] ?? ''),
            'Бизнес: ' . $validated['business'],
            'Сфера: ' . ($validated['sector'] ?? ''),
            'Град: ' . ($validated['city'] ?? ''),
            'Сайт сега: ' . (filled($validated['site_url'] ?? null) ? $validated['site_url'] : 'няма'),
            'Цел: ' . (config('creatium.contact_goals')[$validated['goal'] ?? ''] ?? ''),
            'Кога: ' . (config('creatium.contact_timelines')[$validated['timeline'] ?? ''] ?? ''),
        ], fn ($line) => ! str_ends_with($line, ': '));

        // Превключвателят на /paketi: начинът на плащане на абонамента. Непозната стойност (променена или остаряла форма) се пропуска, без грешка.
        if (in_array($request->input('billing'), [PlanBilling::MONTHLY, PlanBilling::YEARLY], true)) {
            $lines[] = 'Плащане: ' . ($request->input('billing') === PlanBilling::YEARLY ? 'годишно' : 'месечно');
        }

        $attribution = $request->session()->get(LeadAttribution::SESSION_KEY);
        $lead = Project::create([
            'name' => 'Запитване за оферта от ' . $validated['name'],
            'description' => 'Иска оферта за пакет „' . $planName . "“.\n\n" . implode("\n", $lines),
            'website_url' => HomeController::siteUrl($validated['site_url'] ?? null),
            'start_date' => now()->toDateString(),
            'status' => 'Planning',
            'manager' => 'Неразпределен',
            'category_id' => Category::firstOrCreate(
                ['name' => 'Ново запитване'],
                ['description' => 'Автоматично създадени запитвания от сайта, изчакващи категоризация']
            )->id,
            'client_phone' => filled($validated['phone'] ?? null) ? $validated['phone'] : null,
            'client_email' => filled($validated['email'] ?? null) ? mb_strtolower($validated['email']) : null,
            'service' => 'Оферта: ' . $planName,
            'locale' => app()->getLocale(),
            'source' => 'website',
            'lead_channel' => LeadAttribution::channel($attribution),
            'utm_source' => $attribution['utm_source'] ?? null,
            'utm_medium' => $attribution['utm_medium'] ?? null,
            'utm_campaign' => $attribution['utm_campaign'] ?? null,
            'referrer' => $attribution['referrer'] ?? null,
        ]);

        try {
            Mail::to(config('creatium.notify_email'))->send(new NewLeadMail($lead));
            $lead->forceFill(['email_sent_at' => now()])->save();
        } catch (\Throwable $e) {
            Log::error('Quote notification email failed: ' . $e->getMessage(), ['project_id' => $lead->id]);
        }
        \App\Support\Notify\Telegram::newLead($lead);

        return $back->with('quote_sent', true)->with('lead_created', true)->with('lead_service', $lead->service);
    }
}
