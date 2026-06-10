<?php

use App\Http\Controllers\Api\Notices\NoticeController;
use App\Http\Controllers\Api\Notices\PollController;
use Illuminate\Support\Facades\Route;

/*
| Notices (API v1). Mounted inside the auth:sanctum + tenant group in
| routes/api.php.
*/
Route::middleware('feature:notices')->group(function () {
    Route::apiResource('notices', NoticeController::class)->only(['index', 'show']);
    Route::post('polls/{poll}/vote', [PollController::class, 'vote'])->name('notices.api.poll.vote');
});
