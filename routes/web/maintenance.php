<?php

use App\Http\Controllers\Maintenance\HeadController;
use App\Http\Controllers\Maintenance\MaintenanceController;
use Illuminate\Support\Facades\Route;

/*
| Maintenance Billing (web). Mounted inside the authenticated, tenant-scoped
| group defined in routes/web.php. Gated by the "billing" plan feature.
*/
Route::middleware('feature:billing')->group(function () {

    // Dashboard / bills list
    Route::get('maintenance', [MaintenanceController::class, 'index'])->name('maintenance.index');

    // Generate bills
    Route::get('maintenance/generate', [MaintenanceController::class, 'generateForm'])->name('maintenance.generate');
    Route::post('maintenance/generate', [MaintenanceController::class, 'generate'])->name('maintenance.generate.post');

    // Export CSV
    Route::get('maintenance/export', [MaintenanceController::class, 'export'])->name('maintenance.export');

    // Maintenance heads (charge definitions)
    Route::resource('maintenance/heads', HeadController::class)
        ->names('maintenance.heads')
        ->parameters(['heads' => 'head']);

    // Bill detail
    Route::get('maintenance/bills/{bill}', [MaintenanceController::class, 'show'])->name('maintenance.bills.show');

    // Record payment
    Route::post('maintenance/bills/{bill}/pay', [MaintenanceController::class, 'recordPayment'])->name('maintenance.pay');

    // Waive
    Route::post('maintenance/bills/{bill}/waive', [MaintenanceController::class, 'waive'])->name('maintenance.waive');

    // Invoice PDF (printable)
    Route::get('maintenance/bills/{bill}/invoice', [MaintenanceController::class, 'invoice'])->name('maintenance.invoice');

    // Receipt (printable)
    Route::get('maintenance/bills/{bill}/receipt/{payment}', [MaintenanceController::class, 'receipt'])->name('maintenance.receipt');
});
