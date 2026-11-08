<?php

namespace App\Http\Controllers;

use App\Mail\ConsultationBookedMail;
use App\Mail\NewLeadMail;
use App\Models\Category;
use App\Models\Post;
use App\Models\Project;
use App\Support\Booking;
use App\Support\LeadAttribution;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

class HomeController extends Controller
{
    public function index()
    {
        $content = app(\App\Support\Content::class);
        $data = [
            'hero' => $content->withDefaults('home_hero'),
            'problem' => $content->withDefaults('home_problem'),
            'process' => $content->withDefaults('home_process'),
            'contactBlock' => site('home_contact'),
            'trust' => $content->withDefaults('home_trust'),
            'demos' => site('demos') ?? [],
            'posts' => config('creatium.blog_enabled') ? Post::visible()->with('category')->latest('published_at')->take(3)->get() : collect(),
            'reviews' => config('creatium.google_reviews'),
            'contact' => config('creatium.contact'),
        ];

        // The redesign (concept G): the same data plus the packages for the price cards.
        if (\App\Support\Design::is('g')) {
            $plans = $content->withDefaults('plans');

            return view('g.home', $data + ['plans' => $plans, 'billing' => \App\Support\PlanBilling::config($plans)]);
        }

        return view('home', $data);
    }

    public function contact()
    {
        return view('contact', ['contact' => config('creatium.contact')]);
    }

    public function submitContact(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => ['required', 'string', 'max:25', 'regex:/^[0-9+()\-\s]{6,25}$/'],
            'email' => 'required|email|max:255',
            // Older pages sent the project status; the simple form (November 2026) sends interests instead.
            'service' => ['nullable', 'string', Rule::in(array_keys(config('creatium.contact_topics')))],
            'interests' => 'nullable|array',
            'interests.*' => ['string', Rule::in(array_keys(config('creatium.contact_interests')))],
            'message' => 'nullable|string|max:5000',
            'site_url' => 'nullable|string|max:255',
            'consultation' => ['nullable', 'string', function ($attribute, $value, $fail) {
                if (! Booking::resolve($value)) {
                    $fail(__('Този час вече е зает. Моля, изберете друг.'));
                }
            }],
            'consent' => 'accepted',
            \App\Support\Turnstile::FIELD => \App\Support\Turnstile::rules(),
        ], \App\Support\Turnstile::messages() + [
            'name.required' => __('Моля, въведете вашето име.'),
            'phone.required' => __('Моля, въведете телефон, на който да ви се обадим.'),
            'phone.regex' => __('Моля, въведете валиден телефонен номер.'),
            'email.required' => __('Моля, въведете имейл.'),
            'email.email' => __('Моля, въведете валиден имейл.'),
            'service.in' => __('Моля, изберете статус от списъка.'),
            'interests.*.in' => __('Моля, изберете услуга от списъка.'),
            'consent.accepted' => __('Моля, потвърдете, че сте запознати с Политиката за поверителност.'),
        ]);

        $consultationAt = Booking::resolve($validated['consultation'] ?? null);
        $success = $consultationAt
            ? __('Получихме запитването ви. Ще ви се обадим на :time за разговора.', ['time' => Booking::label($consultationAt)])
            : __('Получихме запитването ви. Ще ви се обадим до един работен ден.');
        // Admin and the notification email are in Bulgarian, so the lead always stores the Bulgarian label.
        $service = self::serviceLabel($validated);

        // Honeypot: real visitors never fill this hidden field.
        if ($request->filled('website')) {
            return back()->with('success', $success);
        }

        $leadCategory = Category::firstOrCreate(
            ['name' => 'Ново запитване'],
            ['description' => 'Автоматично създадени запитвания от сайта, изчакващи категоризация']
        );

        $attribution = $request->session()->get(LeadAttribution::SESSION_KEY);

        $lead = Project::create([
            'name' => 'Запитване от ' . $validated['name'],
            'description' => self::leadDescription($validated, $service),
            'website_url' => self::siteUrl($validated['site_url'] ?? null),
            'start_date' => now()->toDateString(),
            'status' => 'Planning',
            'manager' => 'Неразпределен',
            'category_id' => $leadCategory->id,
            'client_phone' => $validated['phone'],
            'client_email' => mb_strtolower($validated['email']),
            'service' => $service,
            'consultation_at' => $consultationAt,
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
            Log::error('Lead notification email failed: ' . $e->getMessage(), ['project_id' => $lead->id]);
        }
        \App\Support\Notify\Telegram::newLead($lead);

        if ($consultationAt) {
            try {
                Mail::to($lead->client_email)->send(new ConsultationBookedMail($lead));
            } catch (\Throwable $e) {
                Log::error('Consultation confirmation email failed: ' . $e->getMessage(), ['project_id' => $lead->id]);
            }
        }

        return back()
            ->with('success', $success)
            ->with('lead_created', true)
            ->with('lead_service', $service);
    }

    /**
     * The lead's service in Bulgarian and without colons: the chosen interests joined with commas,
     * else the project status an older page sent, else „Запитване от сайта“.
     */
    public static function serviceLabel(array $data): string
    {
        $interests = self::interestLabels($data);
        if ($interests) {
            return mb_substr(implode(', ', $interests), 0, 255);
        }

        return isset($data['service']) ? config('creatium.contact_topics')[$data['service']] : 'Запитване от сайта';
    }

    /** @return list<string> the Bulgarian labels of the chosen interests */
    private static function interestLabels(array $data): array
    {
        $labels = config('creatium.contact_interests');

        return array_values(array_map(fn ($key) => $labels[$key], array_unique($data['interests'] ?? [])));
    }

    /**
     * The message plus the answers in plain Bulgarian sentences for the admin and the team email.
     */
    private static function leadDescription(array $data, string $service): string
    {
        $interests = self::interestLabels($data);
        $site = trim((string) ($data['site_url'] ?? ''));
        $lines = array_filter([
            $interests ? 'Интересува се от ' . implode(', ', $interests) . '.' : '',
            $site !== '' ? 'Сегашен сайт ' . $site : '',
        ], 'strlen');
        $lines = implode("\n", $lines);
        $message = trim((string) ($data['message'] ?? ''));

        if ($message === '' && $lines === '') {
            return 'Без съобщение. ' . $service . '.';
        }

        return trim($message . ($message !== '' && $lines !== '' ? "\n\n" : '') . $lines);
    }

    /** „vashiyatsait.bg“ → https://vashiyatsait.bg; anything that is not a web address is left out. */
    public static function siteUrl(?string $value): ?string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }
        if (! preg_match('#^https?://#i', $value)) {
            $value = 'https://' . ltrim($value, '/');
        }

        return filter_var($value, FILTER_VALIDATE_URL) && str_contains((string) parse_url($value, PHP_URL_HOST), '.') ? mb_substr($value, 0, 255) : null;
    }

    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            return redirect()->intended(route('admin.today'));
        }

        return back()->withInput($request->only('email'))->withErrors([
            'email' => 'Грешен имейл или парола.',
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
