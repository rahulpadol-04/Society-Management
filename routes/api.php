<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController as ApiDashboardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API v1 (Sanctum token auth, tenant-scoped)
|--------------------------------------------------------------------------
| Global 'throttle:api' is applied in bootstrap/app.php. Routes are mounted
| under the /api prefix by the framework.
*/

// All API route names are prefixed with "api.v1." so they never collide with
// the web route names (web and API both expose resource routes named e.g.
// "complaints.*"; without this prefix route() helpers in web controllers would
// resolve to the API URL).
Route::prefix('v1')->name('api.v1.')->group(function () {
    // Public
    Route::post('auth/login', [AuthController::class, 'login'])->middleware('throttle:auth')->name('auth.login');

    // Authenticated
    Route::middleware(['auth:sanctum', 'tenant'])->group(function () {
        Route::get('auth/me', [AuthController::class, 'me']);
        Route::post('auth/logout', [AuthController::class, 'logout']);
        Route::get('dashboard/chart/{type}', [ApiDashboardController::class, 'chart']);

        // Feature module API routes (auto-loaded — each file lives in routes/api/).
        foreach (glob(base_path('routes/api/*.php')) ?: [] as $moduleRoutes) {
            require $moduleRoutes;
        }
    });
});
