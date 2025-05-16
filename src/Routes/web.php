<?php

use Illuminate\Support\Facades\Route;
use Mariojgt\Candle\Http\Controllers\Auth\AuthController;
use Mariojgt\Candle\Http\Controllers\SiteController;
use Mariojgt\Candle\Http\Controllers\ApiKeyController;
use Mariojgt\Candle\Http\Controllers\DashboardController;
use Mariojgt\Candle\Http\Controllers\EventController;
use Mariojgt\Candle\Models\Site;

// Authentication Routes (no auth required)
Route::prefix('candle')->name('candle.auth.')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/magic-link', [AuthController::class, 'requestMagicLink'])->name('request-magic-link');
    Route::get('/magic-link/{token}', [AuthController::class, 'processMagicLink'])->name('magic-link');
    Route::get('/register', [AuthController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register');
});

// Dashboard routes - protected by Laravel's auth system
Route::middleware(config('candle.middleware'))->prefix('candle')->group(function () {
    // Logout route
    Route::post('/logout', [AuthController::class, 'logout'])->name('candle.auth.logout');

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('candle.dashboard');

    // Site management
    Route::prefix('sites')->name('candle.sites.')->group(function () {
        Route::get('/', [SiteController::class, 'index'])->name('index');
        Route::get('/create', [SiteController::class, 'create'])->name('create');
        Route::post('/', [SiteController::class, 'store'])->name('store');
        Route::get('/{site}', [SiteController::class, 'show'])->name('show');
        Route::get('/{site}/edit', [SiteController::class, 'edit'])->name('edit');
        Route::put('/{site}', [SiteController::class, 'update'])->name('update');
        Route::delete('/{site}', [SiteController::class, 'destroy'])->name('destroy');
        Route::get('/{site}/tracking-code', [SiteController::class, 'getTrackingCode'])->name('tracking-code');
    });

    // API Key management
    Route::prefix('api-keys')->name('candle.api-keys.')->group(function () {
        Route::get('/', [ApiKeyController::class, 'index'])->name('index');
        Route::get('/create', [ApiKeyController::class, 'create'])->name('create');
        Route::post('/', [ApiKeyController::class, 'store'])->name('store');
        Route::get('/{apiKey}', [ApiKeyController::class, 'show'])->name('show');
        Route::delete('/{apiKey}', [ApiKeyController::class, 'destroy'])->name('destroy');
        Route::post('/{apiKey}/revoke', [ApiKeyController::class, 'revoke'])->name('revoke');
        Route::post('/{apiKey}/activate', [ApiKeyController::class, 'activate'])->name('activate');
    });
});

// Public tracker.js route
Route::get('/tracker.js', function () {
    $siteId = request('site_id');
    $site = Site::findOrFail($siteId);
    $apiKey = $site->apiKeys()->where('active', true)->first();

    if (!$apiKey) {
        abort(403, 'API key not found or inactive.');
    }

    return response()
        ->view('candle::tracker', [
            'siteId' => $siteId,
            'apiKey' => $apiKey->key,
            'trackClicks' => $site->settings['track_clicks'] ?? true,
            'trackForms' => $site->settings['track_forms'] ?? true,
            'trackRouteChanges' => $site->settings['track_route_changes'] ?? true,
            'cookieTimeout' => $site->settings['cookie_timeout'] ?? 30,
        ])
        ->header('Content-Type', 'application/javascript');
})->name('candle.tracker');

// API Routes for collecting events
Route::prefix(config('candle.route_prefix'))->group(function () {
    Route::post('/', [EventController::class, 'store']);
});
