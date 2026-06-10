<?php

declare(strict_types=1);

use App\Http\Controllers\Helpdesk\EscalationController;
use App\Http\Controllers\Helpdesk\TicketController;
use Illuminate\Support\Facades\Route;

/*
| Helpdesk (web). Mounted inside the authenticated, tenant-scoped group
| defined in routes/web.php. Gated by the "helpdesk" plan feature.
*/
Route::middleware('feature:helpdesk')->group(function () {
    // Escalation matrix (declared BEFORE the wildcard resource to avoid route swallowing)
    Route::resource('helpdesk/escalation-rules', EscalationController::class)
        ->only(['index', 'store', 'update', 'destroy'])
        ->names([
            'index'   => 'helpdesk.escalation.index',
            'store'   => 'helpdesk.escalation.store',
            'update'  => 'helpdesk.escalation.update',
            'destroy' => 'helpdesk.escalation.destroy',
        ])
        ->parameters(['helpdesk/escalation-rules' => 'escalationRule']);

    // Extra ticket actions
    Route::post('helpdesk/{helpdeskTicket}/reply',    [TicketController::class, 'reply'])->name('helpdesk.reply');
    Route::post('helpdesk/{helpdeskTicket}/assign',   [TicketController::class, 'assign'])->name('helpdesk.assign');
    Route::post('helpdesk/{helpdeskTicket}/escalate', [TicketController::class, 'escalate'])->name('helpdesk.escalate');
    Route::post('helpdesk/{helpdeskTicket}/close',    [TicketController::class, 'close'])->name('helpdesk.close');

    // Resource routes (helpdesk.index, helpdesk.create, helpdesk.store,
    //   helpdesk.show, helpdesk.edit, helpdesk.update, helpdesk.destroy)
    Route::resource('helpdesk', TicketController::class)
        ->parameters(['helpdesk' => 'helpdeskTicket']);
});
