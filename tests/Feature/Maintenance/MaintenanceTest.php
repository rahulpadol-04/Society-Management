<?php

declare(strict_types=1);

namespace Tests\Feature\Maintenance;

use App\Models\Flat;
use App\Models\MaintenanceBill;
use App\Models\MaintenanceHead;
use App\Models\Tower;
use App\Services\Maintenance\BillingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MaintenanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedPlatform();
    }

    /** Create an occupied flat (flats require a tower). */
    private function makeFlat(\App\Models\Society $society, array $attrs = []): Flat
    {
        $tower = Tower::firstOrCreate(
            ['society_id' => $society->id, 'code' => 'A'],
            ['name' => 'Tower A', 'total_floors' => 2, 'units_per_floor' => 2, 'status' => 'active']
        );

        return Flat::create(array_merge([
            'society_id' => $society->id,
            'tower_id'   => $tower->id,
            'number'     => 'A-101',
            'status'     => 'occupied',
        ], $attrs));
    }

    public function test_admin_can_create_a_maintenance_head(): void
    {
        $society = $this->makeSociety('Alpha Society', 'alpha@test.com');
        $admin   = $this->admin($society);

        $response = $this->actingAs($admin)->post('/maintenance/heads', [
            'name'      => 'Maintenance Charge',
            'code'      => 'MC',
            'type'      => 'fixed',
            'amount'    => 2000,
            'frequency' => 'monthly',
            'is_active' => 1,
        ]);

        $response->assertRedirect(route('maintenance.heads.index'));

        $this->assertDatabaseHas('maintenance_heads', [
            'name'       => 'Maintenance Charge',
            'society_id' => $society->id,
            'amount'     => 2000,
        ]);
    }

    public function test_generate_bills_for_period_creates_one_bill_per_flat_with_gst(): void
    {
        $society = $this->makeSociety('Alpha Society', 'alpha@test.com');
        $admin   = $this->admin($society);

        // Create a tower and two flats
        $tower = Tower::create([
            'society_id'   => $society->id,
            'name'         => 'Tower A',
            'code'         => 'A',
            'total_floors' => 2,
            'units_per_floor' => 2,
        ]);

        Flat::create(['society_id' => $society->id, 'tower_id' => $tower->id, 'number' => 'A-101', 'status' => 'occupied']);
        Flat::create(['society_id' => $society->id, 'tower_id' => $tower->id, 'number' => 'A-102', 'status' => 'occupied']);

        // Create a taxable maintenance head
        MaintenanceHead::create([
            'society_id'     => $society->id,
            'name'           => 'Maintenance Charge',
            'type'           => 'fixed',
            'amount'         => 1000,
            'is_taxable'     => true,
            'gst_percentage' => 18,
            'frequency'      => 'monthly',
            'is_active'      => true,
        ]);

        $period  = now()->format('Y-m');
        $service = app(BillingService::class);
        $result  = $service->generateBillsForPeriod($period);

        // One bill per flat
        $this->assertEquals(2, $result['count']);
        $this->assertDatabaseCount('maintenance_bills', 2);

        // GST applied: 1000 base + 18% = 1180 total
        $bill = MaintenanceBill::first();
        $this->assertEquals(1000.0, $bill->subtotal);
        $this->assertEquals(180.0, $bill->tax_amount);
        $this->assertEquals(1180.0, $bill->total);
        $this->assertEquals('unpaid', $bill->status);
        $this->assertStringStartsWith('INV-', $bill->bill_number);
    }

    public function test_generate_bills_skips_flats_that_already_have_a_bill_for_period(): void
    {
        $society = $this->makeSociety('Alpha Society', 'alpha@test.com');

        $this->makeFlat($society);

        MaintenanceHead::create([
            'society_id' => $society->id,
            'name'       => 'MC',
            'type'       => 'fixed',
            'amount'     => 500,
            'frequency'  => 'monthly',
            'is_active'  => true,
        ]);

        $period  = now()->format('Y-m');
        $service = app(BillingService::class);

        $first  = $service->generateBillsForPeriod($period);
        $second = $service->generateBillsForPeriod($period);

        $this->assertEquals(1, $first['count']);
        $this->assertEquals(0, $second['count']);  // skipped
        $this->assertDatabaseCount('maintenance_bills', 1);
    }

    public function test_record_payment_marks_bill_paid_when_full_amount(): void
    {
        $society = $this->makeSociety('Alpha Society', 'alpha@test.com');

        $this->makeFlat($society);

        MaintenanceHead::create([
            'society_id' => $society->id,
            'name'       => 'MC',
            'type'       => 'fixed',
            'amount'     => 2000,
            'frequency'  => 'monthly',
            'is_active'  => true,
        ]);

        $service = app(BillingService::class);
        $service->generateBillsForPeriod(now()->format('Y-m'));

        $bill = MaintenanceBill::first();
        $this->assertEquals('unpaid', $bill->status);

        $service->recordPayment($bill, $bill->total, 'upi', 'TXN123456');

        $bill->refresh();
        $this->assertEquals('paid', $bill->status);
        $this->assertEquals($bill->total, $bill->paid_amount);

        $this->assertDatabaseHas('maintenance_payments', [
            'maintenance_bill_id' => $bill->id,
            'amount'              => $bill->total,
            'method'              => 'upi',
        ]);
    }

    public function test_record_payment_marks_bill_partial_when_partial_amount(): void
    {
        $society = $this->makeSociety('Alpha Society', 'alpha@test.com');

        $this->makeFlat($society);

        MaintenanceHead::create([
            'society_id' => $society->id,
            'name'       => 'MC',
            'type'       => 'fixed',
            'amount'     => 2000,
            'frequency'  => 'monthly',
            'is_active'  => true,
        ]);

        $service = app(BillingService::class);
        $service->generateBillsForPeriod(now()->format('Y-m'));

        $bill = MaintenanceBill::first();
        $service->recordPayment($bill, 1000.0, 'cash');

        $bill->refresh();
        $this->assertEquals('partial', $bill->status);
        $this->assertEquals(1000.0, $bill->paid_amount);
    }

    public function test_late_fee_applies_to_overdue_bill(): void
    {
        $society = $this->makeSociety('Alpha Society', 'alpha@test.com');

        $this->makeFlat($society);

        MaintenanceHead::create([
            'society_id' => $society->id,
            'name'       => 'MC',
            'type'       => 'fixed',
            'amount'     => 2000,
            'frequency'  => 'monthly',
            'is_active'  => true,
        ]);

        $service = app(BillingService::class);
        $service->generateBillsForPeriod(now()->format('Y-m'));

        $bill = MaintenanceBill::first();

        // Force the bill to be overdue
        $bill->update(['due_date' => now()->subDays(15), 'status' => 'overdue']);

        $originalTotal = $bill->total;
        $service->applyLateFee($bill);

        $bill->refresh();

        // Late fee should have been applied: 2% of balance
        $expectedFee = round($originalTotal * 0.02, 2);
        $this->assertEquals($expectedFee, $bill->late_fee);
        $this->assertGreaterThan($originalTotal, $bill->total);
        $this->assertDatabaseHas('late_fees', [
            'maintenance_bill_id' => $bill->id,
        ]);
    }

    public function test_bills_are_isolated_between_societies(): void
    {
        $alpha = $this->makeSociety('Alpha Society', 'alpha@test.com');

        $this->makeFlat($alpha);
        MaintenanceHead::create([
            'society_id' => $alpha->id,
            'name'       => 'MC',
            'type'       => 'fixed',
            'amount'     => 1000,
            'frequency'  => 'monthly',
            'is_active'  => true,
        ]);

        app(BillingService::class)->generateBillsForPeriod(now()->format('Y-m'));

        $alphaAdmin = $this->admin($alpha);
        $this->actingAs($alphaAdmin)->get('/maintenance')->assertOk();

        // Switch to Beta society
        $beta      = $this->makeSociety('Beta Society', 'beta@test.com');
        $betaAdmin = $this->admin($beta);

        // Flush session so the tenancy resolves for beta
        $this->flushSession();

        $this->actingAs($betaAdmin)
            ->get('/maintenance')
            ->assertOk()
            ->assertDontSee('A-101');

        // Beta sees zero bills
        $this->assertEquals(0, MaintenanceBill::count());
    }

    public function test_resident_can_only_view_their_own_bills(): void
    {
        $society = $this->makeSociety('Alpha Society', 'alpha@test.com');

        $resident = $this->makeUser($society, 'resident', ['email' => 'resident@test.com']);

        $this->makeFlat($society, ['owner_id' => $resident->id]);

        MaintenanceHead::create([
            'society_id' => $society->id,
            'name'       => 'MC',
            'type'       => 'fixed',
            'amount'     => 1000,
            'frequency'  => 'monthly',
            'is_active'  => true,
        ]);

        $service = app(BillingService::class);
        $service->generateBillsForPeriod(now()->format('Y-m'));

        $bill = MaintenanceBill::where('user_id', $resident->id)->first();
        $this->assertNotNull($bill);

        // Resident can view their bill
        $this->actingAs($resident)
            ->get(route('maintenance.bills.show', $bill))
            ->assertOk();
    }

    public function test_resident_cannot_generate_bills(): void
    {
        $society  = $this->makeSociety('Alpha Society', 'alpha@test.com');
        $resident = $this->makeUser($society, 'resident');

        $this->actingAs($resident)
            ->post('/maintenance/generate', ['period' => now()->format('Y-m')])
            ->assertForbidden();
    }
}
