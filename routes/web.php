<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ProjectController;
use App\Http\Controllers\Admin\TechnologyController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PageController;
use Illuminate\Support\Facades\Route;

// Public site
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/uslugi', [PageController::class, 'services'])->name('services');
Route::get('/proekti', [PageController::class, 'portfolio'])->name('portfolio');
Route::get('/za-nas', [PageController::class, 'about'])->name('about');
Route::get('/kontakti', [HomeController::class, 'contact'])->name('contact');
Route::post('/kontakti', [HomeController::class, 'submitContact'])->middleware('throttle:5,10')->name('contact.submit');
Route::get('/poveritelnost', [PageController::class, 'privacy'])->name('privacy');
Route::get('/usloviya', [PageController::class, 'terms'])->name('terms');
Route::get('/sitemap.xml', [PageController::class, 'sitemap'])->name('sitemap');
Route::get('/robots.txt', [PageController::class, 'robots'])->name('robots');

// Old URLs
Route::redirect('/about', '/za-nas', 301);
Route::redirect('/contact', '/kontakti', 301);

// Team login (no public registration: accounts are created with `php artisan creatium:make-admin` or by an admin)
Route::get('/login', [HomeController::class, 'showLoginForm'])->name('login');
Route::post('/login', [HomeController::class, 'login'])->middleware('throttle:5,1')->name('login.post');
Route::post('/logout', [HomeController::class, 'logout'])->name('logout');

// Admin panel
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');

    Route::get('projects/search', [ProjectController::class, 'search'])->name('projects.search');
    Route::resource('projects', ProjectController::class);
    Route::resource('categories', CategoryController::class);
    Route::resource('technologies', TechnologyController::class);

    Route::middleware('role:admin')->group(function () {
        Route::resource('users', UserController::class);
    });
});
