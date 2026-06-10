<?php

declare(strict_types=1);

use App\Http\Controllers\Api\Helpdesk\TicketController;
use Illuminate\Support\Facades\Route;

/*
| Helpdesk (API v1). Mounted inside the auth:sanctum + tenant group in
| routes/api.php.
*/
Route::middleware('feature:helpdesk')->group(function () {
    Route::post('helpdesk/{ticket}/reply',  [TicketController::class, 'reply'])->name('api.helpdesk.reply');
    Route::post('helpdesk/{ticket}/assign', [TicketController::class, 'assign'])->name('api.helpdesk.assign');
    Route::apiResource('helpdesk', TicketController::class);
});
