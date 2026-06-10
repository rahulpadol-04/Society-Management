<?php

declare(strict_types=1);

namespace Tests\Feature\Vendors;

use App\Models\Vendor;
use App\Models\VendorRating;
use App\Models\WorkOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VendorTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedPlatform();
    }

    public function test_admin_can_create_a_vendor(): void
    {
        $society = $this->makeSociety('Alpha Society', 'alpha@test.com');
        $admin   = $this->admin($society);

        $response = $this->actingAs($admin)->post('/vendors', [
            'name'     => 'Test Plumber Co.',
            'category' => 'plumbing',
            'phone'    => '9900001111',
            'status'   => 'active',
        ]);

        $vendor = Vendor::first();
        $this->assertNotNull($vendor);
        $response->assertRedirect("/vendors/{$vendor->id}");
        $this->assertEquals('Test Plumber Co.', $vendor->name);
        $this->assertEquals('plumbing', $vendor->category);
        $this->assertEquals('active', $vendor->status);
    }

    public function test_admin_can_create_a_work_order_for_a_vendor(): void
    {
        $society = $this->makeSociety('Alpha Society', 'alpha@test.com');
        $admin   = $this->admin($society);

        $vendor = Vendor::create([
            'name'     => 'Electrical Works Ltd',
            'category' => 'electrical',
            'status'   => 'active',
        ]);

        $response = $this->actingAs($admin)->post("/vendors/{$vendor->id}/work-orders", [
            'title'    => 'Replace fuse board',
            'priority' => 'high',
            'amount'   => 15000,
        ]);

        $response->assertRedirect();

        $workOrder = WorkOrder::first();
        $this->assertNotNull($workOrder);
        $this->assertStringStartsWith('WO-', $workOrder->reference);
        $this->assertEquals($vendor->id, $workOrder->vendor_id);
        $this->assertEquals('open', $workOrder->status);
        $this->assertEquals('high', $workOrder->priority);
    }

    public function test_admin_can_record_a_vendor_payment(): void
    {
        $society = $this->makeSociety('Alpha Society', 'alpha@test.com');
        $admin   = $this->admin($society);

        $vendor = Vendor::create([
            'name'     => 'Plumb Co',
            'category' => 'plumbing',
            'status'   => 'active',
        ]);

        $response = $this->actingAs($admin)->post("/vendors/{$vendor->id}/payments", [
            'amount'  => 5000,
            'method'  => 'bank_transfer',
            'paid_at' => now()->format('Y-m-d H:i:s'),
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('vendor_payments', [
            'vendor_id' => $vendor->id,
            'amount'    => 5000,
            'method'    => 'bank_transfer',
        ]);
    }

    public function test_adding_a_rating_updates_vendor_average_rating(): void
    {
        $society = $this->makeSociety('Alpha Society', 'alpha@test.com');
        $admin   = $this->admin($society);

        $vendor = Vendor::create([
            'name'     => 'Sparkle Cleaners',
            'category' => 'housekeeping',
            'status'   => 'active',
        ]);

        $this->actingAs($admin)->post("/vendors/{$vendor->id}/ratings", [
            'rating'  => 4,
            'comment' => 'Very clean result.',
        ]);

        $this->actingAs($admin)->post("/vendors/{$vendor->id}/ratings", [
            'rating'  => 2,
            'comment' => 'Could improve.',
        ]);

        $vendor->refresh();

        $this->assertEquals(2, $vendor->ratings_count);
        $this->assertEquals(3.0, $vendor->rating); // avg of 4+2 = 3
        $this->assertEquals(2, VendorRating::count());
    }

    public function test_vendors_are_isolated_between_tenants(): void
    {
        $alpha = $this->makeSociety('Alpha Society', 'alpha@test.com');
        $this->actingAs($this->admin($alpha))->post('/vendors', [
            'name'     => 'Alpha Vendor',
            'category' => 'general',
        ]);

        $beta = $this->makeSociety('Beta Society', 'beta@test.com');

        $this->flushSession();

        // Beta admin must not see Alpha's vendor (tenant scope).
        $this->actingAs($this->admin($beta))
            ->get('/vendors')
            ->assertOk()
            ->assertDontSee('Alpha Vendor');

        $this->assertEquals(1, Vendor::withoutGlobalScopes()->count());
    }

    public function test_non_privileged_user_cannot_create_vendor(): void
    {
        $society  = $this->makeSociety('Alpha Society', 'alpha@test.com');
        $resident = $this->makeUser($society, 'resident');

        $response = $this->actingAs($resident)->post('/vendors', [
            'name'     => 'Unauthorized Vendor',
            'category' => 'general',
        ]);

        $response->assertForbidden();
        $this->assertEquals(0, Vendor::count());
    }
}
