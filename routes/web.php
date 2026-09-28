<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\ProjectController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\TechnologyController;
use App\Http\Controllers\Admin\UserController;

// Public routes
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/about', [HomeController::class, 'about'])->name('about');
Route::get('/contact', [HomeController::class, 'contact'])->name('contact');
Route::post('/contact', [HomeController::class, 'submitContact'])->name('contact.submit');

// Authentication routes
Route::get('/login', [HomeController::class, 'showLoginForm'])->name('login');
Route::post('/login', [HomeController::class, 'login'])->name('login.post');
Route::get('/register', [HomeController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [HomeController::class, 'register'])->name('register.post');
Route::post('/logout', [HomeController::class, 'logout'])->name('logout');

// Admin routes

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
    
    // Projects
    Route::resource('projects', ProjectController::class);
    Route::get('projects/search', [ProjectController::class, 'search'])->name('projects.search');
    
    // Categories
    Route::resource('categories', CategoryController::class);
    
    // Technologies
    Route::resource('technologies', TechnologyController::class);
    
    // Users
    Route::resource('users', UserController::class);
});
