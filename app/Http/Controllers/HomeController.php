<?php

namespace App\Http\Controllers;

use App\Mail\NewLeadMail;
use App\Models\Category;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class HomeController extends Controller
{
    public function index()
    {
        return view('home', [
            'industries' => config('creatium.industries'),
            'services' => config('creatium.services'),
            'process' => config('creatium.process'),
            'packages' => config('creatium.packages'),
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
            'contact' => [
                'required', 'string', 'max:255',
                function (string $attribute, mixed $value, \Closure $fail) {
                    $isValid = str_contains($value, '@')
                        ? filter_var($value, FILTER_VALIDATE_EMAIL)
                        : preg_match('/^[0-9+()\-\s]{6,25}$/', $value);

                    if (! $isValid) {
                        $fail('Моля, въведете валиден телефон или имейл.');
                    }
                },
            ],
            'message' => 'required|string|max:5000',
            'consent' => 'accepted',
        ], [
            'name.required' => 'Моля, въведете вашето име.',
            'contact.required' => 'Моля, въведете телефон или имейл за връзка.',
            'message.required' => 'Моля, разкажете ни малко повече за вашия бизнес.',
            'consent.accepted' => 'Моля, потвърдете, че сте запознати с Политиката за поверителност.',
        ]);

        // Honeypot: real visitors never fill this hidden field.
        if ($request->filled('website')) {
            return back()->with('success', 'Получихме запитването ви и ще се свържем с вас възможно най-скоро.');
        }

        $leadCategory = Category::firstOrCreate(
            ['name' => 'Ново запитване'],
            ['description' => 'Автоматично създадени запитвания от сайта, изчакващи категоризация']
        );

        $isEmail = str_contains($validated['contact'], '@');

        $lead = Project::create([
            'name' => 'Запитване от ' . $validated['name'],
            'description' => $validated['message'],
            'start_date' => now()->toDateString(),
            'status' => 'Planning',
            'manager' => 'Неразпределен',
            'category_id' => $leadCategory->id,
            'client_email' => $isEmail ? $validated['contact'] : null,
            'client_phone' => $isEmail ? null : $validated['contact'],
            'source' => 'website',
        ]);

        try {
            Mail::to(config('creatium.notify_email'))->send(new NewLeadMail($lead));
        } catch (\Throwable $e) {
            Log::error('Lead notification email failed: ' . $e->getMessage(), ['project_id' => $lead->id]);
        }

        return back()->with('success', 'Получихме запитването ви и ще се свържем с вас възможно най-скоро.');
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
