<?php

use App\Http\Controllers\Notices\NoticeController;
use App\Http\Controllers\Notices\PollController;
use Illuminate\Support\Facades\Route;

/*
| Notices / Notice Board (web). Mounted inside the authenticated, tenant-scoped
| group defined in routes/web.php. Gated by the "notices" plan feature.
*/
Route::middleware('feature:notices')->group(function () {
    // Notice board index (required by sidebar navigation).
    Route::get('notices', [NoticeController::class, 'index'])->name('notices.index');

    // Publish action before resource so it takes precedence.
    Route::post('notices/{notice}/publish', [NoticeController::class, 'publish'])->name('notices.publish');

    // Full CRUD resource (index handled separately above to keep name clean).
    Route::resource('notices', NoticeController::class)->except(['index']);

    // Polls nested under notices.
    Route::post('notices/{notice}/polls', [PollController::class, 'store'])->name('notices.polls.store');

    // Poll voting and management.
    Route::post('polls/{poll}/vote', [PollController::class, 'vote'])->name('notices.poll.vote');
    Route::post('polls/{poll}/close', [PollController::class, 'close'])->name('notices.poll.close');
});
