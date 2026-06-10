<?php

use App\Http\Controllers\Api\Visitors\VisitorController;
use Illuminate\Support\Facades\Route;

/*
| Visitors (API v1). Mounted inside the auth:sanctum + tenant group in
| routes/api.php. Gated by the "visitors" plan feature.
*/
Route::middleware('feature:visitors')->group(function () {

    // Visitor passes
    Route::get('passes',                   [VisitorController::class, 'index'])->name('api.visitors.index');
    Route::post('passes',                  [VisitorController::class, 'store'])->name('api.visitors.store');
    Route::post('passes/{pass}/approve',   [VisitorController::class, 'approve'])->name('api.visitors.approve');

    // Gate operations
    Route::post('gate/validate',           [VisitorController::class, 'validateCode'])->name('api.visitors.validate');
    Route::post('gate/checkin',            [VisitorController::class, 'checkIn'])->name('api.visitors.checkin');
    Route::post('gate/checkout/{log}',     [VisitorController::class, 'checkOut'])->name('api.visitors.checkout');
});
