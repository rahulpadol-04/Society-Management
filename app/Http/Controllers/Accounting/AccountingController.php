<?php

declare(strict_types=1);

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use App\Models\JournalEntry;
use App\Models\LedgerAccount;
use App\Services\Accounting\AccountingService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AccountingController extends Controller
{
    public function __construct(protected AccountingService $service) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', LedgerAccount::class);

        // KPI: income / expense totals from P&L (current month)
        $pl = $this->service->profitAndLoss(
            now()->startOfMonth(),
            now()->endOfMonth(),
        );

        $bankBalance = BankAccount::where('is_active', true)->sum('current_balance');

        $recentEntries = JournalEntry::with(['creator'])
            ->latest()
            ->limit(10)
            ->get();

        return view('accounting.index', [
            'totalIncome'  => $pl['total_income'],
            'totalExpense' => $pl['total_expense'],
            'surplus'      => $pl['net_surplus'],
            'bankBalance'  => (float) $bankBalance,
            'recentEntries' => $recentEntries,
            'statusCounts' => $this->service->repository()->statusCounts(),
        ]);
    }
}
