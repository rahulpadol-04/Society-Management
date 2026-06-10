<?php

use App\Http\Controllers\Api\Staff\StaffController;
use Illuminate\Support\Facades\Route;

/*
| Staff Management (API v1). Mounted inside the auth:sanctum + tenant group
| in routes/api.php. Gated by the "staff" plan feature.
*/
Route::middleware('feature:staff')->group(function () {
    Route::apiResource('staff', StaffController::class)
        ->parameters(['staff' => 'staffMember']);
});
