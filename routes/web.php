<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ChatController as AdminChatController;
use App\Http\Controllers\Admin\ClientEmailController;
use App\Http\Controllers\Admin\ContentController;
use App\Http\Controllers\Admin\InquiryController;
use App\Http\Controllers\Admin\OutreachCallController;
use App\Http\Controllers\Admin\OutreachController;
use App\Http\Controllers\Admin\OutreachSearchController;
use App\Http\Controllers\Admin\ReviewReplyController;
use App\Http\Controllers\Admin\PostCategoryController;
use App\Http\Controllers\Admin\PostController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\SeoController;
use App\Http\Controllers\Admin\UploadController;
use App\Http\Controllers\Admin\ProjectController;
use App\Http\Controllers\Admin\TechnologyController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\DemoController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OutreachUnsubscribeController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\SiteCheckController;
use Illuminate\Support\Facades\Route;

// Public site: Bulgarian at the root, English under /en. Route names are the same with an "en." prefix (see lroute()).
$publicPages = [
    // name => [Bulgarian path, English path, action]
    'home' => ['/', '/', [HomeController::class, 'index']],
    'services' => ['/uslugi', '/services', [PageController::class, 'services']],
    'packages' => ['/paketi', '/packages', [PageController::class, 'packages']],
    'portfolio' => ['/proekti', '/projects', [PageController::class, 'portfolio']],
    'site-check' => ['/proverka-na-sait', '/website-check', [SiteCheckController::class, 'show']],
    'about' => ['/za-nas', '/about', [PageController::class, 'about']],
    'faq' => ['/vaprosi', '/faq', [PageController::class, 'faq']],
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

        Route::get($locale === 'en' ? '/services/{slug}' : '/uslugi/{slug}', [PageController::class, 'service'])->where('slug', '[a-z0-9-]+')->name('services.show');

        Route::get($locale === 'en' ? '/presentation' : '/prezentatsiya', [PageController::class, 'presentation'])->name('presentation');

        Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
        Route::get($locale === 'en' ? '/blog/category/{slug}' : '/blog/kategoriya/{slug}', [BlogController::class, 'category'])->name('blog.category');
        Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('blog.show');

        Route::post($locale === 'en' ? '/contact' : '/kontakti', [HomeController::class, 'submitContact'])
            ->middleware('throttle:5,10')
            ->name('contact.submit');

        Route::post($locale === 'en' ? '/website-check' : '/proverka-na-sait', [SiteCheckController::class, 'submit'])
            ->middleware('throttle:5,10')
            ->name('site-check.submit');
    });
}

// Чатът на сайта (partials/chat-widget)
Route::post('/chat', [ChatController::class, 'send'])->middleware('throttle:20,1')->name('chat.send');
Route::get('/chat/{conversation}', [ChatController::class, 'history'])->whereUuid('conversation')->middleware('throttle:60,1')->name('chat.history');

// Demo sites shown on the Projects page (Bulgarian only, not indexed)
Route::get('/demo/{slug}', [DemoController::class, 'show'])->where('slug', '[a-z-]+')->name('demo.show');

// Отписване от първите писма до бизнеси (линк в писмото + one-click от пощенския клиент)
Route::match(['get', 'post'], '/otpisvane/{prospect}', OutreachUnsubscribeController::class)->middleware('signed')->name('outreach.unsubscribe');

Route::get('/sitemap.xml', [PageController::class, 'sitemap'])->name('sitemap');
Route::get('/robots.txt', [PageController::class, 'robots'])->name('robots');
Route::get('/llms.txt', [PageController::class, 'llms'])->name('llms');

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

    // Emails to clients from templates (App\Support\ClientEmails)
    Route::get('emails', [ClientEmailController::class, 'index'])->name('client-emails.index');
    Route::get('emails/{template}', [ClientEmailController::class, 'create'])->name('client-emails.create');
    Route::post('emails/{template}/preview', [ClientEmailController::class, 'preview'])->name('client-emails.preview');
    Route::post('emails/{template}', [ClientEmailController::class, 'store'])->middleware('throttle:20,1')->name('client-emails.store');

    // Потенциални клиенти: бизнеси от Harvey и първо писмо до тях
    // Разговорите от чата на сайта
    Route::get('chats', [AdminChatController::class, 'index'])->name('chats.index');
    Route::get('chats/{chat}', [AdminChatController::class, 'show'])->name('chats.show');

    Route::get('reviews', [ReviewReplyController::class, 'index'])->name('reviews.index');
    Route::post('reviews', [ReviewReplyController::class, 'store'])->middleware('throttle:30,1')->name('reviews.store');
    Route::get('reviews/{reply}', [ReviewReplyController::class, 'show'])->name('reviews.show');
    Route::patch('reviews/{reply}', [ReviewReplyController::class, 'update'])->name('reviews.update');
    Route::get('outreach-search', [OutreachSearchController::class, 'index'])->name('outreach.search.index');
    Route::post('outreach-search', [OutreachSearchController::class, 'store'])->middleware('throttle:20,1')->name('outreach.search.store');
    Route::post('outreach-search/{search}/work', [OutreachSearchController::class, 'work'])->name('outreach.search.work');
    Route::post('outreach-search/reset', [OutreachSearchController::class, 'reset'])->name('outreach.search.reset');
    Route::get('outreach-calls', [OutreachCallController::class, 'index'])->name('outreach.calls.index');
    Route::post('outreach-calls/import', [OutreachCallController::class, 'import'])->name('outreach.calls.import');
    Route::patch('outreach-calls/{call}', [OutreachCallController::class, 'update'])->name('outreach.calls.update');
    Route::get('outreach-calls/next', [OutreachCallController::class, 'next'])->name('outreach.calls.next');
    Route::get('outreach-calls/back', [OutreachCallController::class, 'back'])->name('outreach.calls.back');
    Route::post('outreach-calls/{call}/skip', [OutreachCallController::class, 'skip'])->name('outreach.calls.skip');
    Route::get('outreach', [OutreachController::class, 'index'])->name('outreach.index');
    Route::post('outreach/import', [OutreachController::class, 'import'])->name('outreach.import');
    Route::post('outreach/import-server', [OutreachController::class, 'importServer'])->name('outreach.import-server');
    Route::post('outreach/check', [OutreachController::class, 'check'])->name('outreach.check');
    Route::post('outreach/send-selected', [OutreachController::class, 'sendSelected'])->middleware('throttle:10,1')->name('outreach.send-selected');
    Route::get('outreach/{prospect}', [OutreachController::class, 'compose'])->name('outreach.compose');
    Route::post('outreach/{prospect}/preview', [OutreachController::class, 'preview'])->name('outreach.preview');
    Route::post('outreach/{prospect}/send', [OutreachController::class, 'send'])->middleware('throttle:20,1')->name('outreach.send');
    Route::patch('outreach/{prospect}', [OutreachController::class, 'update'])->name('outreach.update');

    Route::get('projects/search', [ProjectController::class, 'search'])->name('projects.search');
    Route::resource('projects', ProjectController::class);
    Route::resource('categories', CategoryController::class);
    Route::resource('technologies', TechnologyController::class);

    // Site content: blog, page texts, SEO
    Route::post('posts/{post}/facebook', [PostController::class, 'facebook'])->middleware('throttle:10,1')->name('posts.facebook');
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
