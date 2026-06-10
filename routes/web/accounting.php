<?php

use App\Http\Controllers\Accounting\AccountController;
use App\Http\Controllers\Accounting\AccountingController;
use App\Http\Controllers\Accounting\BankAccountController;
use App\Http\Controllers\Accounting\JournalController;
use App\Http\Controllers\Accounting\ReportController;
use Illuminate\Support\Facades\Route;

/*
| Accounting (web). Mounted inside the authenticated, tenant-scoped group
| defined in routes/web.php. Gated by the "accounting" plan feature.
*/
Route::middleware('feature:accounting')->group(function () {
    // Overview / landing (required by sidebar: accounting.index)
    Route::get('accounting', [AccountingController::class, 'index'])->name('accounting.index');

    // Chart of Accounts
    Route::resource('accounting/accounts', AccountController::class)
        ->names('accounting.accounts')
        ->parameters(['accounts' => 'account']);

    // Journal Entries
    Route::post('accounting/journals/{journal}/post', [JournalController::class, 'post'])
        ->name('accounting.journals.post');
    Route::resource('accounting/journals', JournalController::class)
        ->names('accounting.journals')
        ->parameters(['journals' => 'journal']);

    // Bank / Cash Accounts
    Route::resource('accounting/bank-accounts', BankAccountController::class)
        ->names('accounting.bank')
        ->parameters(['bank-accounts' => 'bank']);

    // Reports
    Route::get('accounting/reports/trial-balance', [ReportController::class, 'trialBalance'])
        ->name('accounting.reports.trial');
    Route::get('accounting/reports/profit-loss', [ReportController::class, 'profitLoss'])
        ->name('accounting.reports.pl');
    Route::get('accounting/reports/balance-sheet', [ReportController::class, 'balanceSheet'])
        ->name('accounting.reports.bs');
});
