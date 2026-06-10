<?php

use App\Http\Controllers\Settings\SettingsController;
use Illuminate\Support\Facades\Route;

/*
| Society master configuration (web). Mounted inside the authenticated,
| tenant-scoped group in routes/web.php. Core module (no plan feature gate).
*/
Route::prefix('settings')->name('settings.')->group(function () {
    Route::get('/', [SettingsController::class, 'index'])->name('index');
    Route::put('/', [SettingsController::class, 'update'])->name('update');

    Route::get('billing', [SettingsController::class, 'billing'])->name('billing');
    Route::put('billing', [SettingsController::class, 'updateBilling'])->name('billing.update');

    Route::get('roles', [SettingsController::class, 'roles'])->name('roles');
    Route::get('roles/{role}', [SettingsController::class, 'editRole'])->name('roles.edit');
    Route::put('roles/{role}', [SettingsController::class, 'updateRole'])->name('roles.update');
});
