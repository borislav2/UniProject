<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Project;
use App\Support\LeadAttribution;
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
        return view('site-check');
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
        ], [
            'domain.required' => __('Моля, въведете адреса на сайта.'),
            'email.required' => __('Моля, въведете имейл, на който да изпратим отчета.'),
            'email.email' => __('Моля, въведете валиден имейл.'),
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
        $lead = Project::create([
            'name' => 'Проверка на сайт: ' . $domain,
            'description' => "Проверката на {$domain} тече. Отчетът ще бъде изпратен на {$email}.",
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
            ->with('lead_created', true)
            ->with('lead_service', self::SERVICE);
    }
}
