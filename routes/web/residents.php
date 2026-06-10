<?php

use App\Http\Controllers\Residents\DirectoryController;
use App\Http\Controllers\Residents\ResidentController;
use App\Http\Controllers\Residents\VehicleController;
use Illuminate\Support\Facades\Route;

/*
| Residents, Vehicles & Directory (web). Mounted inside the authenticated,
| tenant-scoped group in routes/web.php. All three are CORE modules
| (feature = null) so NO feature middleware is applied.
*/

// ---- Residents ---------------------------------------------------------------

// Named export route (must come before the resource to avoid {resident} binding).
Route::get('residents/export', [ResidentController::class, 'export'])->name('residents.export');

Route::resource('residents', ResidentController::class);

// Family member sub-resource.
Route::post('residents/{resident}/family', [ResidentController::class, 'storeFamilyMember'])
    ->name('residents.family.store');
Route::delete('residents/{resident}/family/{member}', [ResidentController::class, 'destroyFamilyMember'])
    ->name('residents.family.destroy');

// Emergency contact sub-resource.
Route::post('residents/{resident}/emergency-contacts', [ResidentController::class, 'storeEmergencyContact'])
    ->name('residents.emergency-contacts.store');
Route::delete('residents/{resident}/emergency-contacts/{contact}', [ResidentController::class, 'destroyEmergencyContact'])
    ->name('residents.emergency-contacts.destroy');

// ---- Vehicles ---------------------------------------------------------------

Route::resource('vehicles', VehicleController::class);

// ---- Directory --------------------------------------------------------------

Route::get('directory', [DirectoryController::class, 'index'])->name('directory.index');
Route::get('directory/export', [DirectoryController::class, 'download'])->name('directory.export');
