<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\AdminController;

// ══════════════════════════════════════════
// USER GUEST ROUTES (unchanged)
// ══════════════════════════════════════════
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

// ══════════════════════════════════════════
// USER PROTECTED ROUTES (unchanged)
// ══════════════════════════════════════════
Route::middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('home');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

// ══════════════════════════════════════════
// ADMIN GUEST ROUTES
// ══════════════════════════════════════════
Route::prefix('admin')->group(function () {
    Route::get('/login', [AdminController::class, 'showLogin'])->name('admin.login');
    Route::post('/login', [AdminController::class, 'login'])->name('admin.login.submit');
});

// ══════════════════════════════════════════
// ADMIN PROTECTED ROUTES
// ══════════════════════════════════════════
Route::middleware(['admin'])->prefix('admin')->name('admin.')->group(function () {

    // Dashboard
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::post('/logout', [AdminController::class, 'logout'])->name('logout');

    // Users
    Route::get('/users', [AdminController::class, 'users'])->name('users');
    Route::post('/users/store', [AdminController::class, 'storeUser'])->name('users.store');
    Route::post('/users/{id}/update', [AdminController::class, 'updateUser'])->name('users.update');
    Route::delete('/users/{id}', [AdminController::class, 'destroyUser'])->name('users.destroy');

    // Countries
    Route::get('/countries', [AdminController::class, 'countries'])->name('countries');
    Route::post('/countries/{id}/update', [AdminController::class, 'updateCountry'])->name('countries.update');
    Route::delete('/countries/{id}', [AdminController::class, 'destroyCountry'])->name('countries.destroy');

    // Ports
    Route::get('/ports', [AdminController::class, 'ports'])->name('ports');
    Route::post('/ports/{id}/update', [AdminController::class, 'updatePort'])->name('ports.update');
    Route::delete('/ports/{id}', [AdminController::class, 'destroyPort'])->name('ports.destroy');

    // News
    Route::get('/news', [AdminController::class, 'news'])->name('news');
    Route::delete('/news/{id}', [AdminController::class, 'destroyNews'])->name('news.destroy');

    // Exchange Rates
    Route::get('/rates', [AdminController::class, 'rates'])->name('rates');

    // Weather
    Route::get('/weather', [AdminController::class, 'weather'])->name('weather');

    // Risk Analytics
    Route::get('/risks', [AdminController::class, 'risks'])->name('risks');

    // Reports
    Route::get('/reports', [AdminController::class, 'reports'])->name('reports');

    // Activity Logs
    Route::get('/logs', [AdminController::class, 'logs'])->name('logs');

    // Settings
    Route::get('/settings', [AdminController::class, 'settings'])->name('settings');
    Route::post('/settings/update', [AdminController::class, 'updateSettings'])->name('settings.update');
});