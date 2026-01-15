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
        $stats = [
            'total_projects' => Project::count(),
            'total_categories' => Category::count(),
            'total_technologies' => Technology::count(),
            'completed_projects' => Project::where('status', 'Completed')->count(),
            'in_progress_projects' => Project::where('status', 'In Progress')->count(),
        ];
        
        $featuredProjects = Project::with(['category', 'technologies'])
            ->inRandomOrder()
            ->take(6)
            ->get();
        
        $categories = Category::withCount('projects')->get();
        $technologies = Technology::withCount('projects')->orderBy('projects_count', 'desc')->take(8)->get();
        
        return view('home', compact('stats', 'featuredProjects', 'categories', 'technologies'));
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
