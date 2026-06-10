<?php

use App\Http\Controllers\Api\Complaints\ComplaintController;
use Illuminate\Support\Facades\Route;

/*
| Complaints (API v1). Mounted inside the auth:sanctum + tenant group in
| routes/api.php.
*/
Route::middleware('feature:complaints')
    ->apiResource('complaints', ComplaintController::class);
