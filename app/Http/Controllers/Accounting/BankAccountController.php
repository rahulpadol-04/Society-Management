<?php

declare(strict_types=1);

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Http\Requests\Accounting\StoreBankAccountRequest;
use App\Http\Requests\Accounting\UpdateBankAccountRequest;
use App\Models\BankAccount;
use App\Models\LedgerAccount;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BankAccountController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', BankAccount::class);

        $accounts = BankAccount::with('ledgerAccount')->latest()->get();

        return view('accounting.bank-accounts.index', compact('accounts'));
    }

    public function create(): View
    {
        $this->authorize('create', BankAccount::class);

        $ledgerAccounts = LedgerAccount::active()
            ->whereIn('type', ['asset'])
            ->whereIn('subtype', ['bank', 'cash'])
            ->orderBy('name')
            ->get();

        return view('accounting.bank-accounts.create', compact('ledgerAccounts'));
    }

    public function store(StoreBankAccountRequest $request): RedirectResponse
    {
        BankAccount::create($request->validated());

        return redirect()->route('accounting.bank.index')
            ->with('success', 'Bank account created.');
    }

    public function edit(BankAccount $bank): View
    {
        $this->authorize('update', $bank);

        $ledgerAccounts = LedgerAccount::active()
            ->whereIn('type', ['asset'])
            ->orderBy('name')
            ->get();

        return view('accounting.bank-accounts.edit', compact('bank', 'ledgerAccounts'));
    }

    public function update(UpdateBankAccountRequest $request, BankAccount $bank): RedirectResponse
    {
        $bank->update($request->validated());

        return redirect()->route('accounting.bank.index')
            ->with('success', 'Bank account updated.');
    }

    public function destroy(BankAccount $bank): RedirectResponse
    {
        $this->authorize('delete', $bank);

        $bank->delete();

        return redirect()->route('accounting.bank.index')
            ->with('success', 'Bank account deleted.');
    }
}
