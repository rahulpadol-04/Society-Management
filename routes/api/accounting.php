<?php

use App\Http\Controllers\Api\Accounting\JournalController;
use Illuminate\Support\Facades\Route;

/*
| Accounting (API v1). Mounted inside the auth:sanctum + tenant group in
| routes/api.php.
*/
Route::middleware('feature:accounting')
    ->apiResource('accounting/journals', JournalController::class)
    ->names('api.accounting.journals')
    ->parameters(['journals' => 'journal']);
