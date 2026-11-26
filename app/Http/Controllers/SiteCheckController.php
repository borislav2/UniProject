<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Project;
use App\Support\LeadAttribution;
use App\Support\SiteCheck\ReviewDue;
use App\Support\SiteCheck\SendSiteCheckReport;
use App\Support\SiteCheck\SiteChecker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

use function Illuminate\Support\defer;

/** Безплатна проверка на сайт: човекът дава домейн и имейл, проверката тече след отговора и отчетът идва по имейл. */
class SiteCheckController extends Controller
{
    public const SERVICE = 'Проверка на сайт';

    public function show()
    {
        // ?site=domain.bg (линкът от третото писмо след отчета) попълва адреса; показва се само коректно нормализиран домейн.
        return gview('site-check', ['prefill' => SiteChecker::normalizeDomain(request()->query('site')) ?? '']);
    }

    public function submit(Request $request)
    {
        $validated = $request->validate([
            'domain' => ['required', 'string', 'max:255', function ($attribute, $value, $fail) {
                if (! SiteChecker::normalizeDomain($value)) {
                    $fail(__('Моля, въведете адреса на сайта, например vashiyatsait.bg.'));
                }
            }],
            'email' => 'required|email|max:255',
            'consent' => 'accepted',
            // Само в новия дизайн: основната цел на човека. По желание, за да работи и старата форма без нея.
            'goal' => ['nullable', 'string', 'in:' . implode(',', array_keys(ReviewDue::goals()))],
            \App\Support\Turnstile::FIELD => \App\Support\Turnstile::rules(),
        ], \App\Support\Turnstile::messages() + [
            'domain.required' => __('Моля, въведете адреса на сайта.'),
            'email.required' => __('Моля, въведете имейл, на който да изпратим отчета.'),
            'email.email' => __('Моля, въведете валиден имейл.'),
            'goal.in' => __('Моля, изберете една от целите в списъка.'),
            'consent.accepted' => __('Моля, потвърдете, че сте запознати с Политиката за поверителност.'),
        ]);

        $domain = SiteChecker::normalizeDomain($validated['domain']);
        $email = mb_strtolower($validated['email']);

        // Honeypot: real visitors never fill this hidden field.
        if ($request->filled('website')) {
            return redirect(lroute('site-check'))->with('site_check_sent', $email);
        }

        // Отчетът отива на чужд имейл, затова ограничаваме колко пъти на ден може да се праща на един адрес и за един сайт.
        $limits = ['site-check:email:' . sha1($email) => 3, 'site-check:domain:' . $domain => 5];
        foreach ($limits as $key => $max) {
            if (RateLimiter::tooManyAttempts($key, $max)) {
                return back()->withInput()->withErrors(['email' => __('Днес вече изпратихме няколко отчета за този сайт или имейл. Опитайте утре или ни пишете.')]);
            }
        }
        foreach (array_keys($limits) as $key) {
            RateLimiter::hit($key, 86400);
        }

        $attribution = $request->session()->get(LeadAttribution::SESSION_KEY);
        // Освен автоматичния отчет всяка заявка получава личен преглед от специалист до два работни дни.
        $due = ReviewDue::after();
        $goal = $validated['goal'] ?? null;
        $lead = Project::create([
            'name' => 'Проверка на сайт: ' . $domain,
            'description' => "Проверката на {$domain} тече. Отчетът ще бъде изпратен на {$email}.\n" . ReviewDue::note($due)
                . ($goal ? "\n" . ReviewDue::goalNote($goal) : ''),
            'review_due_at' => $due,
            'check_goal' => $goal,
            'start_date' => now()->toDateString(),
            'status' => 'Planning',
            'manager' => 'Неразпределен',
            'category_id' => Category::firstOrCreate(
                ['name' => 'Ново запитване'],
                ['description' => 'Автоматично създадени запитвания от сайта, изчакващи категоризация']
            )->id,
            'client_email' => $email,
            'website_url' => 'https://' . $domain,
            'service' => self::SERVICE,
            'locale' => app()->getLocale(),
            'source' => 'website',
            'lead_channel' => LeadAttribution::channel($attribution),
            'utm_source' => $attribution['utm_source'] ?? null,
            'utm_medium' => $attribution['utm_medium'] ?? null,
            'utm_campaign' => $attribution['utm_campaign'] ?? null,
            'referrer' => $attribution['referrer'] ?? null,
        ]);

        $locale = app()->getLocale();
        defer(fn () => app(SendSiteCheckReport::class)($lead, $domain, $email, $locale));

        return redirect(lroute('site-check'))
            ->with('site_check_sent', $email)
            ->with('site_check_domain', $domain)
            ->with('lead_created', true)
            ->with('lead_service', self::SERVICE);
    }
}
