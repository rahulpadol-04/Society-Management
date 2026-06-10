<?php

use App\Http\Controllers\Vendors\VendorController;
use App\Http\Controllers\Vendors\WorkOrderController;
use Illuminate\Support\Facades\Route;

/*
| Vendors (web). Mounted inside the authenticated, tenant-scoped group
| defined in routes/web.php. Gated by the "vendors" plan feature.
*/
Route::middleware('feature:vendors')->group(function () {
    // Vendor resource
    Route::resource('vendors', VendorController::class);

    // Nested: contracts under a vendor
    Route::post('vendors/{vendor}/contracts', [VendorController::class, 'storeContract'])
        ->name('vendors.contracts.store');
    Route::delete('vendors/{vendor}/contracts/{contract}', [VendorController::class, 'destroyContract'])
        ->name('vendors.contracts.destroy');

    // Nested: payments under a vendor (vendors.pay ability)
    Route::post('vendors/{vendor}/payments', [VendorController::class, 'storePayment'])
        ->name('vendors.pay');

    // Nested: ratings under a vendor (vendors.rate ability)
    Route::post('vendors/{vendor}/ratings', [VendorController::class, 'storeRating'])
        ->name('vendors.rate');

    // Work orders
    Route::get('work-orders', [WorkOrderController::class, 'index'])
        ->name('work-orders.index');
    Route::get('work-orders/{workOrder}', [WorkOrderController::class, 'show'])
        ->name('work-orders.show');
    Route::post('vendors/{vendor}/work-orders', [WorkOrderController::class, 'store'])
        ->name('vendors.workorders.store');
    Route::post('work-orders/{workOrder}/status', [WorkOrderController::class, 'updateStatus'])
        ->name('work-orders.status');
});
