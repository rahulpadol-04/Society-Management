<?php

declare(strict_types=1);

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Http\Requests\Accounting\StoreLedgerAccountRequest;
use App\Http\Requests\Accounting\UpdateLedgerAccountRequest;
use App\Models\LedgerAccount;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', LedgerAccount::class);

        $accounts = LedgerAccount::query()
            ->orderBy('type')
            ->orderBy('code')
            ->orderBy('name')
            ->get();

        return view('accounting.accounts.index', compact('accounts'));
    }

    public function create(): View
    {
        $this->authorize('create', LedgerAccount::class);

        return view('accounting.accounts.create');
    }

    public function store(StoreLedgerAccountRequest $request): RedirectResponse
    {
        LedgerAccount::create($request->validated());

        return redirect()->route('accounting.accounts.index')
            ->with('success', 'Ledger account created.');
    }

    public function edit(LedgerAccount $account): View
    {
        $this->authorize('update', $account);

        return view('accounting.accounts.edit', compact('account'));
    }

    public function update(UpdateLedgerAccountRequest $request, LedgerAccount $account): RedirectResponse
    {
        $account->update($request->validated());

        return redirect()->route('accounting.accounts.index')
            ->with('success', 'Ledger account updated.');
    }

    public function destroy(LedgerAccount $account): RedirectResponse
    {
        $this->authorize('delete', $account);

        $account->delete();

        return redirect()->route('accounting.accounts.index')
            ->with('success', 'Ledger account deleted.');
    }
}
