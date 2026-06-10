<?php

declare(strict_types=1);

namespace App\Services\Accounting;

use App\Models\JournalEntry;
use App\Models\JournalLine;
use App\Models\LedgerAccount;
use App\Repositories\Contracts\JournalEntryRepositoryInterface;
use App\Services\BaseService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AccountingService extends BaseService
{
    public function __construct(JournalEntryRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Create a journal entry with its lines.
     * Validates that sum(debit) == sum(credit) > 0 before persisting.
     *
     * @param  array  $data   Entry-level fields (entry_date, narration, type, source, status)
     * @param  array  $lines  Array of ['ledger_account_id', 'debit', 'credit', 'memo']
     * @throws ValidationException
     */
    public function createEntry(array $data, array $lines): JournalEntry
    {
        $totalDebit  = array_sum(array_column($lines, 'debit'));
        $totalCredit = array_sum(array_column($lines, 'credit'));

        if ($totalDebit <= 0 || abs($totalDebit - $totalCredit) >= 0.001) {
            throw ValidationException::withMessages([
                'lines' => ['Journal entry is not balanced: total debits must equal total credits and be greater than zero.'],
            ]);
        }

        return DB::transaction(function () use ($data, $lines, $totalDebit) {
            /** @var JournalEntry $entry */
            $entry = $this->repository->create([
                'reference'  => $this->generateReference(),
                'entry_date' => $data['entry_date'] ?? now()->toDateString(),
                'narration'  => $data['narration'] ?? null,
                'type'       => $data['type'] ?? 'journal',
                'status'     => $data['status'] ?? 'draft',
                'amount'     => $totalDebit,
                'created_by' => $data['created_by'] ?? auth()->id(),
                'source'     => $data['source'] ?? null,
            ]);

            foreach ($lines as $line) {
                JournalLine::create([
                    'journal_entry_id'  => $entry->id,
                    'ledger_account_id' => $line['ledger_account_id'],
                    'debit'             => (float) ($line['debit'] ?? 0),
                    'credit'            => (float) ($line['credit'] ?? 0),
                    'memo'              => $line['memo'] ?? null,
                ]);
            }

            return $entry->refresh();
        });
    }

    /**
     * Post a draft journal entry (mark as posted).
     */
    public function post(JournalEntry $entry, int $userId): JournalEntry
    {
        if (! $entry->isBalanced()) {
            throw ValidationException::withMessages([
                'entry' => ['Cannot post an unbalanced journal entry.'],
            ]);
        }

        return DB::transaction(function () use ($entry, $userId) {
            $entry->update([
                'status'    => 'posted',
                'posted_by' => $userId,
                'posted_at' => now(),
            ]);

            return $entry->refresh();
        });
    }

    /**
     * Trial balance: per-account totals of all posted lines up to $asOf.
     * Returns array keyed by ledger_account_id with debit, credit, net, name, type.
     */
    public function trialBalance(?Carbon $asOf = null): array
    {
        $query = JournalLine::query()
            ->join('journal_entries', 'journal_lines.journal_entry_id', '=', 'journal_entries.id')
            ->join('ledger_accounts', 'journal_lines.ledger_account_id', '=', 'ledger_accounts.id')
            ->where('journal_entries.status', 'posted')
            ->select(
                'ledger_accounts.id as account_id',
                'ledger_accounts.name as account_name',
                'ledger_accounts.code as account_code',
                'ledger_accounts.type as account_type',
                DB::raw('SUM(journal_lines.debit) as total_debit'),
                DB::raw('SUM(journal_lines.credit) as total_credit'),
            )
            ->groupBy('ledger_accounts.id', 'ledger_accounts.name', 'ledger_accounts.code', 'ledger_accounts.type');

        if ($asOf) {
            $query->whereDate('journal_entries.entry_date', '<=', $asOf);
        }

        return $query->get()->map(function ($row) {
            $debit  = (float) $row->total_debit;
            $credit = (float) $row->total_credit;

            return [
                'account_id'   => $row->account_id,
                'account_name' => $row->account_name,
                'account_code' => $row->account_code,
                'account_type' => $row->account_type,
                'total_debit'  => $debit,
                'total_credit' => $credit,
                'net'          => $debit - $credit,
            ];
        })->values()->all();
    }

    /**
     * Profit & Loss: income accounts (credit normal) vs expense accounts (debit normal)
     * for posted entries within [from, to].
     */
    public function profitAndLoss(Carbon $from, Carbon $to): array
    {
        $rows = JournalLine::query()
            ->join('journal_entries', 'journal_lines.journal_entry_id', '=', 'journal_entries.id')
            ->join('ledger_accounts', 'journal_lines.ledger_account_id', '=', 'ledger_accounts.id')
            ->where('journal_entries.status', 'posted')
            ->whereIn('ledger_accounts.type', ['income', 'expense'])
            ->whereBetween('journal_entries.entry_date', [$from->toDateString(), $to->toDateString()])
            ->select(
                'ledger_accounts.id as account_id',
                'ledger_accounts.name as account_name',
                'ledger_accounts.type as account_type',
                DB::raw('SUM(journal_lines.debit) as total_debit'),
                DB::raw('SUM(journal_lines.credit) as total_credit'),
            )
            ->groupBy('ledger_accounts.id', 'ledger_accounts.name', 'ledger_accounts.type')
            ->get();

        $income   = [];
        $expenses = [];
        $totalIncome  = 0.0;
        $totalExpense = 0.0;

        foreach ($rows as $row) {
            $debit  = (float) $row->total_debit;
            $credit = (float) $row->total_credit;

            if ($row->account_type === 'income') {
                $net = $credit - $debit;
                $income[] = ['account_id' => $row->account_id, 'name' => $row->account_name, 'amount' => $net];
                $totalIncome += $net;
            } else {
                $net = $debit - $credit;
                $expenses[] = ['account_id' => $row->account_id, 'name' => $row->account_name, 'amount' => $net];
                $totalExpense += $net;
            }
        }

        return [
            'income'         => $income,
            'expenses'       => $expenses,
            'total_income'   => $totalIncome,
            'total_expense'  => $totalExpense,
            'net_surplus'    => $totalIncome - $totalExpense,
        ];
    }

    /**
     * Balance sheet as of a date: assets, liabilities, equity + P&L surplus.
     */
    public function balanceSheet(Carbon $asOf): array
    {
        $rows = JournalLine::query()
            ->join('journal_entries', 'journal_lines.journal_entry_id', '=', 'journal_entries.id')
            ->join('ledger_accounts', 'journal_lines.ledger_account_id', '=', 'ledger_accounts.id')
            ->where('journal_entries.status', 'posted')
            ->whereIn('ledger_accounts.type', ['asset', 'liability', 'equity', 'income', 'expense'])
            ->whereDate('journal_entries.entry_date', '<=', $asOf)
            ->select(
                'ledger_accounts.id as account_id',
                'ledger_accounts.name as account_name',
                'ledger_accounts.type as account_type',
                DB::raw('SUM(journal_lines.debit) as total_debit'),
                DB::raw('SUM(journal_lines.credit) as total_credit'),
            )
            ->groupBy('ledger_accounts.id', 'ledger_accounts.name', 'ledger_accounts.type')
            ->get();

        $assets      = [];
        $liabilities = [];
        $equity      = [];
        $totalIncome   = 0.0;
        $totalExpense  = 0.0;

        foreach ($rows as $row) {
            $debit  = (float) $row->total_debit;
            $credit = (float) $row->total_credit;

            $entry = ['account_id' => $row->account_id, 'name' => $row->account_name];

            match ($row->account_type) {
                'asset'     => ($assets[]      = $entry + ['amount' => $debit - $credit]) && true,
                'liability' => ($liabilities[] = $entry + ['amount' => $credit - $debit]) && true,
                'equity'    => ($equity[]      = $entry + ['amount' => $credit - $debit]) && true,
                'income'    => ($totalIncome  += $credit - $debit) || true,
                'expense'   => ($totalExpense += $debit - $credit) || true,
                default     => null,
            };
        }

        $surplus = $totalIncome - $totalExpense;

        return [
            'assets'            => $assets,
            'liabilities'       => $liabilities,
            'equity'            => $equity,
            'surplus'           => $surplus,
            'total_assets'      => array_sum(array_column($assets, 'amount')),
            'total_liabilities' => array_sum(array_column($liabilities, 'amount')),
            'total_equity'      => array_sum(array_column($equity, 'amount')) + $surplus,
        ];
    }

    protected function generateReference(): string
    {
        do {
            $ref = 'JE-'.now()->format('ym').'-'.str_pad((string) random_int(1, 99999), 5, '0', STR_PAD_LEFT);
        } while (JournalEntry::withTrashed()->where('reference', $ref)->exists());

        return $ref;
    }
}
