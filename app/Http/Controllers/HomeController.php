<?php

namespace App\Http\Controllers;

use App\Mail\NewLeadMail;
use App\Models\Category;
use App\Models\Project;
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
            'hero' => site('hero'),
            'audience' => site('audience'),
            'industries' => site('industries'),
            'services' => site('services'),
            'process' => site('process'),
            'promise' => site('process_promise'),
            'sections' => site('home_sections'),
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
            'service' => ['required', 'string', Rule::in(array_keys(config('creatium.contact_topics')))],
            'message' => 'nullable|string|max:5000',
            'consent' => 'accepted',
        ], [
            'name.required' => __('Моля, въведете вашето име.'),
            'phone.required' => __('Моля, въведете телефон, на който да ви се обадим.'),
            'phone.regex' => __('Моля, въведете валиден телефонен номер.'),
            'service.required' => __('Моля, изберете с какво да помогнем.'),
            'service.in' => __('Моля, изберете услуга от списъка.'),
            'consent.accepted' => __('Моля, потвърдете, че сте запознати с Политиката за поверителност.'),
        ]);

        $success = __('Получихме запитването ви. Ще ви се обадим до един работен ден.');
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
            'service' => $service,
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
        } catch (\Throwable $e) {
            Log::error('Lead notification email failed: ' . $e->getMessage(), ['project_id' => $lead->id]);
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
