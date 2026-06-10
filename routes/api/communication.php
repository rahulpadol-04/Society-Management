<?php

use App\Http\Controllers\Api\Communication\BroadcastController;
use App\Http\Controllers\Api\Communication\MessageController;
use Illuminate\Support\Facades\Route;

/*
| Communication (API v1). Mounted inside the auth:sanctum + tenant group in
| routes/api.php.
*/
Route::middleware('feature:communication')->group(function () {
    // Broadcasts
    Route::get('communication/broadcasts', [BroadcastController::class, 'index']);
    Route::post('communication/broadcasts', [BroadcastController::class, 'store']);
    Route::post('communication/broadcasts/{broadcast}/send', [BroadcastController::class, 'send']);

    // Conversations / messages
    Route::get('communication/messages', [MessageController::class, 'inbox']);
    Route::post('communication/messages', [MessageController::class, 'store']);
    Route::get('communication/messages/{conversation}', [MessageController::class, 'show']);
});
