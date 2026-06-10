<?php

use App\Http\Controllers\Api\Assets\AssetController;
use Illuminate\Support\Facades\Route;

/*
| Assets (API v1). Mounted inside the auth:sanctum + tenant group in
| routes/api.php.
*/
Route::middleware('feature:assets')
    ->apiResource('assets', AssetController::class);
