<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ProjectController;
use App\Http\Controllers\Admin\TechnologyController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PageController;
use Illuminate\Support\Facades\Route;

// Public site: Bulgarian at the root, English under /en. Route names are the same with an "en." prefix (see lroute()).
$publicPages = [
    // name => [Bulgarian path, English path, action]
    'home' => ['/', '/', [HomeController::class, 'index']],
    'services' => ['/uslugi', '/services', [PageController::class, 'services']],
    'packages' => ['/paketi', '/packages', [PageController::class, 'packages']],
    'portfolio' => ['/proekti', '/projects', [PageController::class, 'portfolio']],
    'about' => ['/za-nas', '/about', [PageController::class, 'about']],
    'contact' => ['/kontakti', '/contact', [HomeController::class, 'contact']],
    'privacy' => ['/poveritelnost', '/privacy', [PageController::class, 'privacy']],
    'terms' => ['/usloviya', '/terms', [PageController::class, 'terms']],
    'cookies' => ['/biskvitki', '/cookies', [PageController::class, 'cookies']],
];

foreach (['bg', 'en'] as $locale) {
    Route::prefix($locale === 'en' ? 'en' : '')->name($locale === 'en' ? 'en.' : '')->group(function () use ($publicPages, $locale) {
        foreach ($publicPages as $name => [$bgPath, $enPath, $action]) {
            Route::get($locale === 'en' ? $enPath : $bgPath, $action)->name($name);
        }

        Route::post($locale === 'en' ? '/contact' : '/kontakti', [HomeController::class, 'submitContact'])
            ->middleware('throttle:5,10')
            ->name('contact.submit');
    });
}

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
