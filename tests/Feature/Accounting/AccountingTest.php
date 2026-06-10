<?php

declare(strict_types=1);

namespace Tests\Feature\Accounting;

use App\Models\JournalEntry;
use App\Models\JournalLine;
use App\Models\LedgerAccount;
use App\Services\Accounting\AccountingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class AccountingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedPlatform();
    }

    // ----------------------------------------------------------------
    // Helpers
    // ----------------------------------------------------------------

    private function makeAccounts(int $societyId): array
    {
        $cash = LedgerAccount::create([
            'society_id' => $societyId,
            'code'       => '1001',
            'name'       => 'Cash',
            'type'       => 'asset',
            'subtype'    => 'cash',
        ]);

        $income = LedgerAccount::create([
            'society_id' => $societyId,
            'code'       => '4001',
            'name'       => 'Maintenance Income',
            'type'       => 'income',
        ]);

        $expense = LedgerAccount::create([
            'society_id' => $societyId,
            'code'       => '5001',
            'name'       => 'Repairs Expense',
            'type'       => 'expense',
        ]);

        return compact('cash', 'income', 'expense');
    }

    // ----------------------------------------------------------------
    // Test: create accounts and post a balanced journal entry
    // ----------------------------------------------------------------

    public function test_admin_can_create_accounts_and_post_balanced_entry(): void
    {
        $society = $this->makeSociety('Alpha Society', 'alpha@test.com');
        $admin   = $this->admin($society);

        $accounts = $this->makeAccounts($society->id);

        $service = app(AccountingService::class);

        $entry = $service->createEntry(
            ['entry_date' => now()->toDateString(), 'narration' => 'Test entry', 'type' => 'income'],
            [
                ['ledger_account_id' => $accounts['cash']->id,   'debit' => 5000, 'credit' => 0],
                ['ledger_account_id' => $accounts['income']->id, 'debit' => 0,    'credit' => 5000],
            ]
        );

        $this->assertNotNull($entry);
        $this->assertStringStartsWith('JE-', $entry->reference);
        $this->assertEquals('draft', $entry->status);
        $this->assertEquals(5000.0, $entry->amount);
        $this->assertTrue($entry->isBalanced());

        // Post the entry
        $posted = $service->post($entry, $admin->id);
        $this->assertEquals('posted', $posted->status);
        $this->assertNotNull($posted->posted_at);
        $this->assertEquals($admin->id, $posted->posted_by);
    }

    // ----------------------------------------------------------------
    // Test: unbalanced entry is rejected
    // ----------------------------------------------------------------

    public function test_unbalanced_journal_entry_is_rejected(): void
    {
        $society  = $this->makeSociety('Beta Society', 'beta@test.com');
        $accounts = $this->makeAccounts($society->id);

        $service = app(AccountingService::class);

        $this->expectException(ValidationException::class);

        $service->createEntry(
            ['entry_date' => now()->toDateString(), 'narration' => 'Unbalanced'],
            [
                ['ledger_account_id' => $accounts['cash']->id,    'debit' => 1000, 'credit' => 0],
                ['ledger_account_id' => $accounts['expense']->id, 'debit' => 0,    'credit' => 500], // mismatch
            ]
        );
    }

    // ----------------------------------------------------------------
    // Test: trial balance — sum debits == sum credits for posted entries
    // ----------------------------------------------------------------

    public function test_trial_balance_debits_equal_credits(): void
    {
        $society  = $this->makeSociety('Gamma Society', 'gamma@test.com');
        $accounts = $this->makeAccounts($society->id);

        $service = app(AccountingService::class);

        // Entry 1: 10,000 debit cash / 10,000 credit income
        $e1 = $service->createEntry(
            ['entry_date' => now()->toDateString(), 'type' => 'income'],
            [
                ['ledger_account_id' => $accounts['cash']->id,   'debit' => 10000, 'credit' => 0],
                ['ledger_account_id' => $accounts['income']->id, 'debit' => 0,     'credit' => 10000],
            ]
        );
        $service->post($e1, $this->admin($society)->id);

        // Entry 2: 3,000 debit expense / 3,000 credit cash
        $e2 = $service->createEntry(
            ['entry_date' => now()->toDateString(), 'type' => 'expense'],
            [
                ['ledger_account_id' => $accounts['expense']->id, 'debit' => 3000, 'credit' => 0],
                ['ledger_account_id' => $accounts['cash']->id,    'debit' => 0,    'credit' => 3000],
            ]
        );
        $service->post($e2, $this->admin($society)->id);

        $rows        = $service->trialBalance();
        $totalDebit  = array_sum(array_column($rows, 'total_debit'));
        $totalCredit = array_sum(array_column($rows, 'total_credit'));

        $this->assertEqualsWithDelta($totalDebit, $totalCredit, 0.001, 'Trial balance debits must equal credits');
        $this->assertGreaterThan(0, $totalDebit, 'Trial balance must have positive totals');
    }

    // ----------------------------------------------------------------
    // Test: tenant isolation
    // ----------------------------------------------------------------

    public function test_journal_entries_are_isolated_between_tenants(): void
    {
        $alpha    = $this->makeSociety('Alpha Society', 'alpha2@test.com');
        $accounts = $this->makeAccounts($alpha->id);

        $service = app(AccountingService::class);
        $service->createEntry(
            ['entry_date' => now()->toDateString(), 'narration' => 'Alpha only entry'],
            [
                ['ledger_account_id' => $accounts['cash']->id,   'debit' => 1000, 'credit' => 0],
                ['ledger_account_id' => $accounts['income']->id, 'debit' => 0,    'credit' => 1000],
            ]
        );

        // Switch to a new tenant — must not see Alpha's entry
        $beta = $this->makeSociety('Beta Society', 'beta2@test.com');

        $this->flushSession();

        $this->actingAs($this->admin($beta))
            ->get('/accounting')
            ->assertOk()
            ->assertDontSee('Alpha only entry');

        $this->assertEquals(1, JournalEntry::withoutGlobalScopes()->count());
    }

    // ----------------------------------------------------------------
    // Test: permission denied for non-accountant role without accounting.create
    // ----------------------------------------------------------------

    public function test_non_accountant_cannot_create_journal_entry(): void
    {
        $society  = $this->makeSociety('Delta Society', 'delta@test.com');
        $resident = $this->makeUser($society, 'resident');
        $accounts = $this->makeAccounts($society->id);

        $this->actingAs($resident)
            ->post('/accounting/journals', [
                'entry_date' => now()->toDateString(),
                'narration'  => 'Unauthorized',
                'lines'      => [
                    ['ledger_account_id' => $accounts['cash']->id,   'debit' => 100, 'credit' => 0],
                    ['ledger_account_id' => $accounts['income']->id, 'debit' => 0,   'credit' => 100],
                ],
            ])
            ->assertForbidden();
    }
}
