<?php

use App\Http\Controllers\Api\Vendors\VendorController;
use App\Http\Controllers\Api\Vendors\WorkOrderController;
use Illuminate\Support\Facades\Route;

/*
| Vendors (API v1). Mounted inside the auth:sanctum + tenant group in
| routes/api.php.
*/
Route::middleware('feature:vendors')->group(function () {
    Route::apiResource('vendors', VendorController::class);

    Route::get('work-orders', [WorkOrderController::class, 'index']);
    Route::post('vendors/{vendor}/work-orders', [WorkOrderController::class, 'store']);
});
