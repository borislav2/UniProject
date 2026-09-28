<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\Category;
use App\Models\Technology;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function index()
    {
        $industries = config('creatium.industries');
        $services = config('creatium.services');
        $process = config('creatium.process');
        $packages = config('creatium.packages');
        $contact = config('creatium.contact');

        return view('home', compact('industries', 'services', 'process', 'packages', 'contact'));
    }

    public function submitContact(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'contact' => 'required|string|max:255',
            'message' => 'required|string',
        ], [
            'name.required' => 'Моля, въведете вашето име.',
            'contact.required' => 'Моля, въведете телефон или имейл за връзка.',
            'message.required' => 'Моля, разкажете ни малко повече за вашия бизнес.',
        ]);

        $leadCategory = Category::firstOrCreate(
            ['name' => 'Ново запитване'],
            ['description' => 'Автоматично създадени запитвания от сайта, изчакващи категоризация']
        );

        $isEmail = str_contains($validated['contact'], '@');

        Project::create([
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

        return back()->with('success', 'Благодарим ви! Ще се свържем с вас възможно най-скоро.');
    }

    public function about()
    {
        return view('about');
    }

    public function contact()
    {
        return view('contact');
    }
    
    public function showLoginForm()
    {
        return view('auth.login');
    }
    
    public function showRegisterForm()
    {
        return view('auth.register');
    }
    
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);
        
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->route('admin.dashboard')->with('success', 'Успешен вход в системата!');
        }
        
        return back()->withErrors([
            'email' => 'Грешен имейл или парола.',
        ]);
    }
    
    public function register(Request $request)
    {
        $credentials = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);
        
        $user = User::create([
            'name' => $credentials['name'],
            'email' => $credentials['email'],
            'password' => bcrypt($credentials['password']),
        ]);
        
        // Assign default role (developer) to new user
        $developerRole = \App\Models\Role::where('slug', 'developer')->first();
        if ($developerRole) {
            $user->roles()->attach($developerRole->id);
        }
        
        Auth::login($user);
        $request->session()->regenerate();
        
        return redirect()->route('admin.dashboard')->with('success', 'Регистрация успешна! Моля влезте в системата.');
    }
    
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect('/')->with('success', 'Успешно излязохте от системата.');
    }
}
