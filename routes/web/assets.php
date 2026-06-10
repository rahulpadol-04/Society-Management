<?php

use App\Http\Controllers\Assets\AssetController;
use App\Http\Controllers\Assets\CategoryController;
use App\Http\Controllers\Assets\ScheduleController;
use Illuminate\Support\Facades\Route;

/*
| Assets (web). Mounted inside the authenticated, tenant-scoped group
| defined in routes/web.php. Gated by the "assets" plan feature.
*/
Route::middleware('feature:assets')->group(function () {
    // Asset categories
    Route::resource('assets/categories', CategoryController::class)
        ->names('assets.categories')
        ->except(['create', 'edit', 'show']);

    // Depreciation recompute
    Route::post('assets/{asset}/depreciate', [AssetController::class, 'depreciate'])
        ->name('assets.depreciate');

    // Maintenance schedules
    Route::post('assets/{asset}/schedules', [ScheduleController::class, 'store'])
        ->name('assets.schedule');
    Route::post('schedules/{schedule}/complete', [ScheduleController::class, 'complete'])
        ->name('assets.schedule.complete');

    // Asset resource (must come after named sub-routes to avoid route collision)
    Route::resource('assets', AssetController::class);
});
