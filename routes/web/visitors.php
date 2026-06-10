<?php

use App\Http\Controllers\Visitors\ExportController;
use App\Http\Controllers\Visitors\GateController;
use App\Http\Controllers\Visitors\VisitorController;
use Illuminate\Support\Facades\Route;

/*
| Visitors (web). Mounted inside the authenticated, tenant-scoped group
| defined in routes/web.php. Gated by the "visitors" plan feature.
|
| Route model binding uses the default {visitor} -> VisitorPass implicit
| binding (singular model name resolution).
*/
Route::middleware('feature:visitors')->group(function () {

    // ── Static paths FIRST (before {visitor} wildcard) ───────────────────
    Route::get('visitors/create',              [VisitorController::class, 'create'])->name('visitors.create');
    Route::get('visitors/gate/console',        [GateController::class, 'gate'])->name('visitors.gate');
    Route::post('visitors/gate/checkin',       [GateController::class, 'checkInWalkIn'])->name('visitors.checkin');
    Route::post('visitors/gate/checkin-code',  [GateController::class, 'checkInByCode'])->name('visitors.checkin.code');
    Route::get('visitors/logs/export',         [ExportController::class, 'export'])->name('visitors.export');
    Route::post('visitors/logs/{log}/checkout', [GateController::class, 'checkOut'])->name('visitors.checkout');

    // ── Visitor Pass resource routes ──────────────────────────────────────
    Route::get('visitors',                     [VisitorController::class, 'index'])->name('visitors.index');
    Route::post('visitors',                    [VisitorController::class, 'store'])->name('visitors.store');
    Route::get('visitors/{visitor}',           [VisitorController::class, 'show'])->name('visitors.show');
    Route::post('visitors/{visitor}/approve',  [VisitorController::class, 'approve'])->name('visitors.approve');
    Route::post('visitors/{visitor}/reject',   [VisitorController::class, 'reject'])->name('visitors.reject');
});
