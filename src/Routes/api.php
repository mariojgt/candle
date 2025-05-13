<?php

use Illuminate\Support\Facades\Route;
use Mariojgt\Candle\Http\Controllers\EventController;
use Mariojgt\Candle\Http\Controllers\SiteController;
use Mariojgt\Candle\Http\Controllers\ApiKeyController;
use Mariojgt\Candle\Http\Middleware\ApiKeyAuth;

// Public routes - no authentication needed
Route::post('/collect', [EventController::class, 'store'])->name('candle.collect');

// Routes protected by API key authentication
Route::middleware([ApiKeyAuth::class, 'web'])->group(function () {
    // Events
    Route::get('/analytics', [EventController::class, 'index'])->name('candle.events.index');
    Route::get('/analytics/counts', [EventController::class, 'counts'])->name('candle.events.counts');
    Route::get('/analytics/pageviews', [EventController::class, 'pageviews'])->name('candle.pageviews');
    Route::get('/analytics/unique-visitors', [EventController::class, 'uniqueVisitors'])->name('candle.unique-visitors');
    Route::get('/analytics/sessions', [EventController::class, 'sessions'])->name('candle.sessions');
    Route::get('/analytics/sessions/data', [EventController::class, 'sessionsData'])->name('candle.sessions.data');
    Route::get('/analytics/retention', [EventController::class, 'retention'])->name('candle.retention');
    Route::get('/analytics/top-pages', [EventController::class, 'topPages'])->name('candle.top-pages');
    Route::get('/analytics/referrers', [EventController::class, 'referrers'])->name('candle.referrers');
    Route::get('/analytics/browsers', [EventController::class, 'browsers'])->name('candle.browsers');
    Route::get('/analytics/os', [EventController::class, 'operatingSystems'])->name('candle.os');
    Route::get('/analytics/devices', [EventController::class, 'devices'])->name('candle.devices');
    Route::get('/analytics/countries', [EventController::class, 'countries'])->name('candle.countries');
    Route::get('/analytics/languages', [EventController::class, 'languages'])->name('candle.languages');
    Route::get('/analytics/user-flow', [EventController::class, 'userFlow'])->name('candle.user-flow');
    Route::get('/analytics/sessions/{session_id}', [EventController::class, 'showSession'])
        ->name('candle.sessions.show');

    // Site management
    Route::get('/sites', [SiteController::class, 'index'])->name('candle.sites.index');
    Route::get('/sites/{site}', [SiteController::class, 'show'])->name('candle.sites.show');
    Route::post('/sites', [SiteController::class, 'store'])->name('candle.sites.store');
    Route::put('/sites/{site}', [SiteController::class, 'update'])->name('candle.sites.update');
    Route::delete('/sites/{site}', [SiteController::class, 'destroy'])->name('candle.sites.destroy');

    // API Key management
    Route::get('/api-keys', [ApiKeyController::class, 'index'])->name('candle.api-keys.index');
    Route::get('/api-keys/{apiKey}', [ApiKeyController::class, 'show'])->name('candle.api-keys.show');
    Route::post('/api-keys', [ApiKeyController::class, 'store'])->name('candle.api-keys.store');
    Route::put('/api-keys/{apiKey}', [ApiKeyController::class, 'update'])->name('candle.api-keys.update');
    Route::delete('/api-keys/{apiKey}', [ApiKeyController::class, 'destroy'])->name('candle.api-keys.destroy');
    Route::post('/api-keys/{apiKey}/revoke', [ApiKeyController::class, 'revoke'])->name('candle.api-keys.revoke');
    Route::post('/api-keys/{apiKey}/activate', [ApiKeyController::class, 'activate'])->name('candle.api-keys.activate');
});
