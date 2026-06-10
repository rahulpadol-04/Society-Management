<?php

use App\Http\Controllers\Reports\ReportController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

/*
| Reports API. Mounted inside the authenticated (Sanctum), tenant-scoped
| group in routes/api.php. Gated by the "reports" plan feature.
*/
Route::middleware('feature:reports')->group(function () {

    Route::get('reports/{type}', function (Request $request, string $type) {
        abort_unless($request->user()->can('reports.view'), 403);

        $allowed = ['visitor', 'billing', 'collection', 'complaint', 'facility', 'occupancy', 'financial'];
        abort_unless(in_array($type, $allowed, true), 404);

        $from = $request->filled('from')
            ? \Illuminate\Support\Carbon::parse($request->input('from'))->startOfDay()
            : now()->subDays(90)->startOfDay();

        $to = $request->filled('to')
            ? \Illuminate\Support\Carbon::parse($request->input('to'))->endOfDay()
            : now()->endOfDay();

        $sid     = current_society_id();
        $service = app(\App\Services\Reports\ReportService::class);

        $report = match ($type) {
            'visitor'    => $service->visitorReport($from, $to, $sid),
            'billing'    => $service->billingReport($from, $to, $sid),
            'collection' => $service->collectionReport($from, $to, $sid),
            'complaint'  => $service->complaintReport($from, $to, $sid),
            'facility'   => $service->facilityReport($from, $to, $sid),
            'occupancy'  => $service->occupancyReport($sid),
            'financial'  => $service->financialReport($from, $to, $sid),
            default      => ['title' => '', 'columns' => [], 'rows' => [], 'summary' => [], 'chart' => ['labels' => [], 'datasets' => []], 'extras' => []],
        };

        return response()->json($report);
    })->name('api.v1.reports.show');
});
