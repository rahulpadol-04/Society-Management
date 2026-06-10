<?php

use App\Http\Controllers\Complaints\ComplaintController;
use Illuminate\Support\Facades\Route;

/*
| Complaints (web). Mounted inside the authenticated, tenant-scoped group
| defined in routes/web.php. Gated by the "complaints" plan feature.
*/
Route::middleware('feature:complaints')->group(function () {
    Route::post('complaints/{complaint}/feedback', [ComplaintController::class, 'feedback'])->name('complaints.feedback');
    Route::resource('complaints', ComplaintController::class);
});
