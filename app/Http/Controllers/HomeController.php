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
            'hero' => config('creatium.hero'),
            'audience' => config('creatium.audience'),
            'industries' => config('creatium.industries'),
            'services' => config('creatium.services'),
            'process' => config('creatium.process'),
            'promise' => config('creatium.process_promise'),
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
            'service' => ['required', 'string', Rule::in(config('creatium.contact_topics'))],
            'message' => 'nullable|string|max:5000',
            'consent' => 'accepted',
        ], [
            'name.required' => 'Моля, въведете вашето име.',
            'phone.required' => 'Моля, въведете телефон, на който да ви се обадим.',
            'phone.regex' => 'Моля, въведете валиден телефонен номер.',
            'service.required' => 'Моля, изберете с какво да помогнем.',
            'service.in' => 'Моля, изберете услуга от списъка.',
            'consent.accepted' => 'Моля, потвърдете, че сте запознати с Политиката за поверителност.',
        ]);

        // Honeypot: real visitors never fill this hidden field.
        if ($request->filled('website')) {
            return back()->with('success', 'Получихме запитването ви. Ще ви се обадим до един работен ден.');
        }

        $leadCategory = Category::firstOrCreate(
            ['name' => 'Ново запитване'],
            ['description' => 'Автоматично създадени запитвания от сайта, изчакващи категоризация']
        );

        $attribution = $request->session()->get(LeadAttribution::SESSION_KEY);

        $lead = Project::create([
            'name' => 'Запитване от ' . $validated['name'],
            'description' => ($validated['message'] ?? null) ?: 'Без съобщение. Тема: ' . $validated['service'] . '.',
            'start_date' => now()->toDateString(),
            'status' => 'Planning',
            'manager' => 'Неразпределен',
            'category_id' => $leadCategory->id,
            'client_phone' => $validated['phone'],
            'service' => $validated['service'],
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
            ->with('success', 'Получихме запитването ви. Ще ви се обадим до един работен ден.')
            ->with('lead_created', true)
            ->with('lead_service', $validated['service']);
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
