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
        return view('home', [
            'hero' => site('home_hero'),
            'problem' => site('home_problem'),
            'engine' => site('home_engine'),
            'process' => site('home_process'),
            'contactBlock' => site('home_contact'),
            'trust' => site('home_trust'),
            'engineCards' => site('services_page.cards') ?? [],
            'demos' => site('demos') ?? [],
            'posts' => config('creatium.blog_enabled') ? Post::visible()->with('category')->latest('published_at')->take(3)->get() : collect(),
            'reviews' => config('creatium.google_reviews'),
            'contact' => config('creatium.contact'),
        ]);
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
            'service' => ['required', 'string', Rule::in(array_keys(config('creatium.contact_topics')))],
            'message' => 'nullable|string|max:5000',
            'consultation' => ['nullable', 'string', function ($attribute, $value, $fail) {
                if (! Booking::resolve($value)) {
                    $fail(__('Този час вече е зает. Моля, изберете друг.'));
                }
            }],
            'consent' => 'accepted',
        ], [
            'name.required' => __('Моля, въведете вашето име.'),
            'phone.required' => __('Моля, въведете телефон, на който да ви се обадим.'),
            'phone.regex' => __('Моля, въведете валиден телефонен номер.'),
            'email.required' => __('Моля, въведете имейл.'),
            'email.email' => __('Моля, въведете валиден имейл.'),
            'service.required' => __('Моля, изберете статус на проекта.'),
            'service.in' => __('Моля, изберете статус от списъка.'),
            'consent.accepted' => __('Моля, потвърдете, че сте запознати с Политиката за поверителност.'),
        ]);

        $consultationAt = Booking::resolve($validated['consultation'] ?? null);
        $success = $consultationAt
            ? __('Получихме запитването ви. Ще ви се обадим на :time за консултацията.', ['time' => Booking::label($consultationAt)])
            : __('Получихме запитването ви. Ще ви се обадим до един работен ден.');
        // Admin and the notification email are in Bulgarian, so the lead always stores the Bulgarian label.
        $service = config('creatium.contact_topics')[$validated['service']];

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
            'description' => ($validated['message'] ?? null) ?: 'Без съобщение. Тема: ' . $service . '.',
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

            return redirect()->intended(route('admin.dashboard'));
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
