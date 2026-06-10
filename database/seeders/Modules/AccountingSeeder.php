<?php

declare(strict_types=1);

namespace Database\Seeders\Modules;

use App\Models\BankAccount;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use App\Models\LedgerAccount;
use App\Models\Society;
use App\Models\User;
use Illuminate\Database\Seeder;

class AccountingSeeder extends Seeder
{
    public function run(): void
    {
        $society = Society::where('slug', 'green-valley')->first();
        if (! $society) {
            return;
        }

        tenancy()->set($society);

        if (LedgerAccount::where('society_id', $society->id)->exists()) {
            tenancy()->forget();
            return;
        }

        $admin = User::withoutGlobalScopes()
            ->where('email', 'admin@greenvalley.test')
            ->first();

        // ----------------------------------------------------------------
        // 1. Chart of Accounts — standard set across all 5 types
        // ----------------------------------------------------------------
        $accounts = $this->seedChartOfAccounts($society->id);

        // ----------------------------------------------------------------
        // 2. Bank Accounts
        // ----------------------------------------------------------------
        BankAccount::create([
            'society_id'      => $society->id,
            'ledger_account_id' => $accounts['bank']->id,
            'name'            => 'HDFC Current Account',
            'account_type'    => 'bank',
            'bank_name'       => 'HDFC Bank',
            'account_number'  => '50100123456789',
            'ifsc'            => 'HDFC0001234',
            'opening_balance' => 50000.00,
            'current_balance' => 50000.00,
        ]);

        BankAccount::create([
            'society_id'      => $society->id,
            'ledger_account_id' => $accounts['cash']->id,
            'name'            => 'Petty Cash',
            'account_type'    => 'cash',
            'opening_balance' => 5000.00,
            'current_balance' => 5000.00,
        ]);

        // ----------------------------------------------------------------
        // 3. Balanced journal entries — posted
        // ----------------------------------------------------------------

        // Jan: Opening balance — Members Receivable debit, Members Equity credit
        $this->postEntry($society->id, $admin?->id, [
            'entry_date' => now()->subMonths(5)->startOfMonth()->toDateString(),
            'narration'  => 'Opening balances for the financial year',
            'type'       => 'opening',
            'source'     => 'manual',
        ], [
            ['account' => $accounts['bank'],        'debit' => 50000, 'credit' => 0],
            ['account' => $accounts['cash'],         'debit' => 5000,  'credit' => 0],
            ['account' => $accounts['receivable'],   'debit' => 20000, 'credit' => 0],
            ['account' => $accounts['equity'],       'debit' => 0,     'credit' => 75000],
        ]);

        // Feb: Maintenance income received
        $this->postEntry($society->id, $admin?->id, [
            'entry_date' => now()->subMonths(4)->toDateString(),
            'narration'  => 'Maintenance collected for February',
            'type'       => 'income',
            'source'     => 'maintenance',
        ], [
            ['account' => $accounts['bank'],             'debit' => 30000, 'credit' => 0],
            ['account' => $accounts['maintenance_income'], 'debit' => 0,   'credit' => 30000],
        ]);

        // Feb: Electricity expense
        $this->postEntry($society->id, $admin?->id, [
            'entry_date' => now()->subMonths(4)->toDateString(),
            'narration'  => 'Electricity bill for common areas — February',
            'type'       => 'expense',
            'source'     => 'manual',
        ], [
            ['account' => $accounts['electricity'], 'debit' => 8500, 'credit' => 0],
            ['account' => $accounts['bank'],          'debit' => 0,   'credit' => 8500],
        ]);

        // Mar: Salary expense
        $this->postEntry($society->id, $admin?->id, [
            'entry_date' => now()->subMonths(3)->toDateString(),
            'narration'  => 'Staff salaries for March',
            'type'       => 'expense',
            'source'     => 'manual',
        ], [
            ['account' => $accounts['salaries'], 'debit' => 15000, 'credit' => 0],
            ['account' => $accounts['bank'],      'debit' => 0,    'credit' => 15000],
        ]);

        // Mar: Maintenance income
        $this->postEntry($society->id, $admin?->id, [
            'entry_date' => now()->subMonths(3)->toDateString(),
            'narration'  => 'Maintenance collected for March',
            'type'       => 'income',
            'source'     => 'maintenance',
        ], [
            ['account' => $accounts['bank'],             'debit' => 32000, 'credit' => 0],
            ['account' => $accounts['maintenance_income'], 'debit' => 0,   'credit' => 32000],
        ]);

        // Apr: Repairs expense — paid from petty cash
        $this->postEntry($society->id, $admin?->id, [
            'entry_date' => now()->subMonths(2)->toDateString(),
            'narration'  => 'Plumbing repairs in Block A',
            'type'       => 'expense',
            'source'     => 'manual',
        ], [
            ['account' => $accounts['repairs'], 'debit' => 3200, 'credit' => 0],
            ['account' => $accounts['cash'],     'debit' => 0,   'credit' => 3200],
        ]);

        // Apr: Sinking fund contribution
        $this->postEntry($society->id, $admin?->id, [
            'entry_date' => now()->subMonths(2)->toDateString(),
            'narration'  => 'Sinking fund income for April',
            'type'       => 'income',
            'source'     => 'maintenance',
        ], [
            ['account' => $accounts['bank'],          'debit' => 10000, 'credit' => 0],
            ['account' => $accounts['sinking_fund'],  'debit' => 0,     'credit' => 10000],
        ]);

        // May: Current month income (draft — not yet posted)
        $ref = 'JE-'.now()->format('ym').'-99001';
        $entry = JournalEntry::create([
            'society_id' => $society->id,
            'reference'  => $ref,
            'entry_date' => now()->startOfMonth()->toDateString(),
            'narration'  => 'Maintenance collected for current month (draft)',
            'type'       => 'income',
            'status'     => 'draft',
            'amount'     => 28000,
            'created_by' => $admin?->id,
            'source'     => 'maintenance',
        ]);

        JournalLine::create([
            'society_id'        => $society->id,
            'journal_entry_id'  => $entry->id,
            'ledger_account_id' => $accounts['bank']->id,
            'debit'             => 28000,
            'credit'            => 0,
        ]);
        JournalLine::create([
            'society_id'        => $society->id,
            'journal_entry_id'  => $entry->id,
            'ledger_account_id' => $accounts['maintenance_income']->id,
            'debit'             => 0,
            'credit'            => 28000,
        ]);

        tenancy()->forget();
    }

    /**
     * Create chart of accounts and return keyed by alias.
     *
     * @return array<string, LedgerAccount>
     */
    private function seedChartOfAccounts(int $societyId): array
    {
        $defs = [
            'cash'               => ['code' => '1001', 'name' => 'Cash in Hand',          'type' => 'asset',     'subtype' => 'cash'],
            'bank'               => ['code' => '1002', 'name' => 'Bank Account (HDFC)',    'type' => 'asset',     'subtype' => 'bank'],
            'receivable'         => ['code' => '1003', 'name' => 'Members Receivable',     'type' => 'asset',     'subtype' => 'receivable'],
            'advance'            => ['code' => '1004', 'name' => 'Advance Deposits',       'type' => 'asset',     'subtype' => 'current'],
            'payable'            => ['code' => '2001', 'name' => 'Vendor Payable',         'type' => 'liability', 'subtype' => 'payable'],
            'deposits_received'  => ['code' => '2002', 'name' => 'Security Deposits Received', 'type' => 'liability', 'subtype' => 'current'],
            'equity'             => ['code' => '3001', 'name' => 'Corpus / General Fund',  'type' => 'equity',    'subtype' => null],
            'maintenance_income' => ['code' => '4001', 'name' => 'Maintenance Income',     'type' => 'income',    'subtype' => 'maintenance'],
            'sinking_fund'       => ['code' => '4002', 'name' => 'Sinking Fund',           'type' => 'income',    'subtype' => 'fund'],
            'interest_income'    => ['code' => '4003', 'name' => 'Interest Income',        'type' => 'income',    'subtype' => 'other'],
            'repairs'            => ['code' => '5001', 'name' => 'Repairs & Maintenance',  'type' => 'expense',   'subtype' => 'maintenance'],
            'salaries'           => ['code' => '5002', 'name' => 'Salaries & Wages',       'type' => 'expense',   'subtype' => 'payroll'],
            'electricity'        => ['code' => '5003', 'name' => 'Electricity Charges',   'type' => 'expense',   'subtype' => 'utility'],
            'housekeeping'       => ['code' => '5004', 'name' => 'Housekeeping & Cleaning','type' => 'expense',   'subtype' => 'maintenance'],
            'admin'              => ['code' => '5005', 'name' => 'Administrative Expenses', 'type' => 'expense',  'subtype' => 'admin'],
        ];

        $created = [];

        foreach ($defs as $key => $def) {
            $created[$key] = LedgerAccount::firstOrCreate(
                ['society_id' => $societyId, 'code' => $def['code']],
                [
                    'name'            => $def['name'],
                    'type'            => $def['type'],
                    'subtype'         => $def['subtype'],
                    'opening_balance' => 0,
                    'is_active'       => true,
                ]
            );
        }

        return $created;
    }

    /**
     * Create and post a balanced journal entry.
     *
     * @param  array  $entryData
     * @param  array<array{account: LedgerAccount, debit: float, credit: float}>  $lines
     */
    private function postEntry(int $societyId, ?int $userId, array $entryData, array $lines): JournalEntry
    {
        $totalDebit = array_sum(array_column($lines, 'debit'));

        $ref = 'JE-'.now()->format('ym').'-'.str_pad((string) random_int(1, 89999), 5, '0', STR_PAD_LEFT);

        // Ensure uniqueness in seed context
        while (JournalEntry::where('reference', $ref)->exists()) {
            $ref = 'JE-'.now()->format('ym').'-'.str_pad((string) random_int(1, 89999), 5, '0', STR_PAD_LEFT);
        }

        $entry = JournalEntry::create(array_merge($entryData, [
            'society_id' => $societyId,
            'reference'  => $ref,
            'status'     => 'posted',
            'amount'     => $totalDebit,
            'created_by' => $userId,
            'posted_by'  => $userId,
            'posted_at'  => now(),
        ]));

        foreach ($lines as $line) {
            JournalLine::create([
                'society_id'        => $societyId,
                'journal_entry_id'  => $entry->id,
                'ledger_account_id' => $line['account']->id,
                'debit'             => (float) $line['debit'],
                'credit'            => (float) $line['credit'],
            ]);
        }

        return $entry;
    }
}
