<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ContentController;
use App\Http\Controllers\Admin\InquiryController;
use App\Http\Controllers\Admin\PostCategoryController;
use App\Http\Controllers\Admin\PostController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\SeoController;
use App\Http\Controllers\Admin\UploadController;
use App\Http\Controllers\Admin\ProjectController;
use App\Http\Controllers\Admin\TechnologyController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\DemoController;
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

        Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
        Route::get($locale === 'en' ? '/blog/category/{slug}' : '/blog/kategoriya/{slug}', [BlogController::class, 'category'])->name('blog.category');
        Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('blog.show');

        Route::post($locale === 'en' ? '/contact' : '/kontakti', [HomeController::class, 'submitContact'])
            ->middleware('throttle:5,10')
            ->name('contact.submit');
    });
}

// Demo sites shown on the Projects page (Bulgarian only, not indexed)
Route::get('/demo/{slug}', [DemoController::class, 'show'])->where('slug', '[a-z-]+')->name('demo.show');

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

    // Messages from the website contact form (stored as projects with source = website)
    Route::get('inquiries', [InquiryController::class, 'index'])->name('inquiries.index');
    Route::post('inquiries/read-all', [InquiryController::class, 'readAll'])->name('inquiries.read-all');
    Route::post('inquiries/{project}/read', [InquiryController::class, 'read'])->name('inquiries.read');
    Route::post('inquiries/{project}/unread', [InquiryController::class, 'unread'])->name('inquiries.unread');

    Route::get('projects/search', [ProjectController::class, 'search'])->name('projects.search');
    Route::resource('projects', ProjectController::class);
    Route::resource('categories', CategoryController::class);
    Route::resource('technologies', TechnologyController::class);

    // Site content: blog, page texts, SEO
    Route::resource('posts', PostController::class)->except('show');
    Route::resource('post-categories', PostCategoryController::class)->except(['show', 'create']);
    Route::get('content', [ContentController::class, 'index'])->name('content.index');
    Route::get('content/{key}', [ContentController::class, 'edit'])->name('content.edit');
    Route::put('content/{key}', [ContentController::class, 'update'])->name('content.update');
    Route::delete('content/{key}', [ContentController::class, 'reset'])->name('content.reset');
    Route::get('seo', [SeoController::class, 'index'])->name('seo.index');
    Route::get('seo/{page}', [SeoController::class, 'edit'])->name('seo.edit');
    Route::put('seo/{page}', [SeoController::class, 'update'])->name('seo.update');
    Route::post('uploads/image', [UploadController::class, 'image'])->name('uploads.image');

    Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::middleware('role:admin')->group(function () {
        Route::resource('users', UserController::class);
    });
});
