<?php

use App\Http\Controllers\Api\Structure\FlatController;
use Illuminate\Support\Facades\Route;

/*
| Society structure (API v1). Mounted inside the auth:sanctum + tenant group in
| routes/api.php (names already prefixed with "api.v1.").
*/
Route::apiResource('flats', FlatController::class);
