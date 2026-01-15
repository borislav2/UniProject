<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckAdminAccess
{
    public function handle(Request $request, Closure $next)
    {
        
       
        if (Auth::check() && !Auth::user()->canAccessAdminPanel()) {
            // Вместо към login, пратете го към началната страница с грешка
            // или върнете 403 статус.
           return redirect()->route('home');
        }
        
        return $next($request);
    }
}