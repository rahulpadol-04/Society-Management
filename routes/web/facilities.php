<?php

use App\Http\Controllers\Facilities\BookingController;
use App\Http\Controllers\Facilities\FacilityController;
use Illuminate\Support\Facades\Route;

/*
| Facility Booking (web). Mounted inside the authenticated, tenant-scoped group
| defined in routes/web.php. Gated by the "facilities" plan feature.
*/
Route::middleware('feature:facilities')->group(function () {
    // Overview index (facility cards + KPIs + bookings table)
    Route::get('facilities', [FacilityController::class, 'index'])->name('facilities.index');

    // Facility management CRUD (admin) — index already declared above
    Route::resource('facilities', FacilityController::class)->except(['index']);

    // Booking actions on a specific facility
    Route::post('facilities/{facility}/book', [BookingController::class, 'store'])->name('facilities.book');

    // Booking management
    Route::get('bookings', [BookingController::class, 'index'])->name('bookings.index');
    Route::post('bookings/{booking}/approve', [BookingController::class, 'approve'])->name('bookings.approve');
    Route::post('bookings/{booking}/reject', [BookingController::class, 'reject'])->name('bookings.reject');
    Route::post('bookings/{booking}/cancel', [BookingController::class, 'cancel'])->name('bookings.cancel');
});
