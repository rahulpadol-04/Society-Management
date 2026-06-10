<?php

use App\Http\Controllers\Api\Residents\ResidentController;
use Illuminate\Support\Facades\Route;

/*
| Residents (API v1). Mounted inside the auth:sanctum + tenant group in
| routes/api.php (names auto-prefixed "api.v1.").
*/
Route::apiResource('residents', ResidentController::class);
