<?php

use App\Http\Controllers\Api\Facilities\BookingController;
use App\Http\Controllers\Api\Facilities\FacilityController;
use Illuminate\Support\Facades\Route;

/*
| Facilities (API v1). Mounted inside the auth:sanctum + tenant group in
| routes/api.php. Names are auto-prefixed with "api.v1." by the parent group.
*/
Route::middleware('feature:facilities')->group(function () {
    Route::get('facilities', [FacilityController::class, 'index'])->name('facilities.index');

    Route::prefix('facilities')->name('facilities.')->group(function () {
        Route::post('{facility}/book', [BookingController::class, 'store'])->name('book');
    });

    Route::prefix('bookings')->name('bookings.')->group(function () {
        Route::get('/', [BookingController::class, 'index'])->name('index');
        Route::post('{booking}/approve', [BookingController::class, 'approve'])->name('approve');
        Route::post('{booking}/cancel', [BookingController::class, 'cancel'])->name('cancel');
    });
});
