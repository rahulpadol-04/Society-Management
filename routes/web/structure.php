<?php

use App\Http\Controllers\Structure\DocumentController;
use App\Http\Controllers\Structure\FlatController;
use App\Http\Controllers\Structure\ParkingSlotController;
use App\Http\Controllers\Structure\SocietyProfileController;
use App\Http\Controllers\Structure\StructureController;
use App\Http\Controllers\Structure\TowerController;
use Illuminate\Support\Facades\Route;

/*
| Society structure (web). Mounted inside the authenticated, tenant-scoped
| group in routes/web.php. These are core modules (no plan feature gate).
*/

// Society profile (view/update the tenant record).
Route::get('society-profile', [SocietyProfileController::class, 'edit'])->name('society-profile.index');
Route::put('society-profile', [SocietyProfileController::class, 'update'])->name('society-profile.update');

// Towers / floors / flats overview + management.
Route::get('structure', [StructureController::class, 'index'])->name('structure.index');
Route::post('towers/{tower}/scaffold', [TowerController::class, 'scaffold'])->name('towers.scaffold');
Route::resource('towers', TowerController::class)->except(['index']);
Route::resource('flats', FlatController::class)->except(['index']);

// Parking slots (resource param bound to ParkingSlot via {parking_slot}).
Route::resource('parking', ParkingSlotController::class)
    ->parameters(['parking' => 'parking_slot'])
    ->only(['index', 'store', 'update', 'destroy']);

// Society documents.
Route::get('documents/{document}/download', [DocumentController::class, 'download'])->name('documents.download');
Route::resource('documents', DocumentController::class)->only(['index', 'store', 'destroy']);
