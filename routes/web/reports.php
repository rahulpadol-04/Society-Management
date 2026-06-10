<?php

use App\Http\Controllers\Reports\ReportController;
use Illuminate\Support\Facades\Route;

/*
| Reports (web). Mounted inside the authenticated, tenant-scoped group
| defined in routes/web.php. Gated by the "reports" plan feature.
*/
Route::middleware('feature:reports')->group(function () {

    // Hub: grid of report cards + headline KPIs
    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');

    // Individual report (visitor, billing, collection, complaint, facility, occupancy, financial)
    Route::get('reports/{type}', [ReportController::class, 'show'])->name('reports.show');

    // CSV export
    Route::get('reports/{type}/export', [ReportController::class, 'export'])->name('reports.export');

    // Print / browser-to-PDF view
    Route::get('reports/{type}/print', [ReportController::class, 'print'])->name('reports.print');
});
