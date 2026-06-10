<?php

use App\Http\Controllers\Communication\BroadcastController;
use App\Http\Controllers\Communication\CommunicationController;
use App\Http\Controllers\Communication\MessageController;
use App\Http\Controllers\Communication\TemplateController;
use Illuminate\Support\Facades\Route;

/*
| Communication (web). Mounted inside the authenticated, tenant-scoped group
| defined in routes/web.php. Gated by the "communication" plan feature.
*/
Route::middleware('feature:communication')->group(function () {
    // Hub
    Route::get('communication', [CommunicationController::class, 'index'])->name('communication.index');

    // Broadcasts
    Route::post('communication/broadcasts/{broadcast}/send', [BroadcastController::class, 'send'])
        ->name('communication.broadcasts.send');
    Route::resource('communication/broadcasts', BroadcastController::class)
        ->names('communication.broadcasts');

    // Templates
    Route::resource('communication/templates', TemplateController::class)
        ->only(['index', 'store', 'update', 'destroy'])
        ->names('communication.templates');

    // Internal messages / conversations
    Route::get('communication/messages', [MessageController::class, 'inbox'])
        ->name('communication.messages.index');
    Route::get('communication/messages/{conversation}', [MessageController::class, 'show'])
        ->name('communication.messages.show');
    Route::post('communication/messages', [MessageController::class, 'store'])
        ->name('communication.messages.store');
});
