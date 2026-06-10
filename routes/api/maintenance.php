<?php

use App\Http\Controllers\Api\Maintenance\BillController;
use Illuminate\Support\Facades\Route;

/*
| Maintenance Billing (API v1). Mounted inside the auth:sanctum + tenant group
| in routes/api.php. Gated by the "billing" plan feature.
*/
Route::middleware('feature:billing')->group(function () {
    Route::get('maintenance/bills', [BillController::class, 'index'])->name('api.maintenance.bills.index');
    Route::get('maintenance/bills/{bill}', [BillController::class, 'show'])->name('api.maintenance.bills.show');
    Route::post('maintenance/bills/{bill}/pay', [BillController::class, 'pay'])->name('api.maintenance.bills.pay');
});
