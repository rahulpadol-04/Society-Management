<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\SocietyRegistrationController;
use App\Http\Controllers\Auth\TwoFactorChallengeController;
use App\Http\Controllers\Auth\TwoFactorSettingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route(auth()->check() ? 'dashboard' : 'login'));

/*
|--------------------------------------------------------------------------
| Guest
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'show'])->name('login');
    Route::post('login', [LoginController::class, 'login'])->middleware('throttle:auth');

    Route::get('register', [SocietyRegistrationController::class, 'show'])->name('register');
    Route::post('register', [SocietyRegistrationController::class, 'store'])->middleware('throttle:auth');
});

// Two-factor challenge (password verified, session not yet fully authenticated).
Route::get('two-factor-challenge', [TwoFactorChallengeController::class, 'show'])->name('two-factor.challenge');
Route::post('two-factor-challenge', [TwoFactorChallengeController::class, 'verify'])
    ->name('two-factor.challenge.verify')->middleware('throttle:auth');

Route::post('logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

/*
|--------------------------------------------------------------------------
| Authenticated application (tenant-scoped, subscription-gated)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'tenant', 'subscription'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('dashboard/chart/{type}', [DashboardController::class, 'chart'])->name('dashboard.chart');

    // Profile & security
    Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

    Route::get('two-factor', [TwoFactorSettingController::class, 'show'])->name('two-factor.settings');
    Route::post('two-factor/enable', [TwoFactorSettingController::class, 'enable'])->name('two-factor.enable');
    Route::post('two-factor/confirm', [TwoFactorSettingController::class, 'confirm'])->name('two-factor.confirm');
    Route::delete('two-factor', [TwoFactorSettingController::class, 'disable'])->name('two-factor.disable');

    Route::get('subscription/expired', fn () => view('subscription.expired'))->name('subscription.expired');

    // Feature module web routes (auto-loaded — each file lives in routes/web/).
    foreach (glob(base_path('routes/web/*.php')) ?: [] as $moduleRoutes) {
        require $moduleRoutes;
    }
});
