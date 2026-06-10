<?php

declare(strict_types=1);

namespace Tests\Feature\Assets;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetMaintenanceSchedule;
use App\Services\Assets\AssetService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssetTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedPlatform();
    }

    public function test_admin_can_create_an_asset(): void
    {
        $society = $this->makeSociety('Green Valley', 'admin@gv.test');
        $admin   = $this->admin($society);

        $response = $this->actingAs($admin)->post('/assets', [
            'name'          => 'Main Elevator',
            'purchase_cost' => 1200000,
            'salvage_value' => 120000,
            'purchase_date' => '2020-01-01',
        ]);

        $asset = Asset::first();

        $this->assertNotNull($asset);
        $response->assertRedirect("/assets/{$asset->id}");
        $this->assertEquals('Main Elevator', $asset->name);
        $this->assertEquals($society->id, $asset->society_id);
        // Initial current_value should be set.
        $this->assertNotNull($asset->current_value);
    }

    public function test_admin_can_create_maintenance_schedule_for_asset(): void
    {
        $society = $this->makeSociety('Green Valley', 'admin@gv.test');
        $admin   = $this->admin($society);

        $this->actingAs($admin)->post('/assets', [
            'name'          => 'Generator 1',
            'purchase_cost' => 500000,
            'purchase_date' => '2022-06-01',
        ]);

        $asset = Asset::first();
        $this->assertNotNull($asset);

        $response = $this->actingAs($admin)->post("/assets/{$asset->id}/schedules", [
            'title'         => 'Monthly Inspection',
            'frequency'     => 'monthly',
            'next_due_date' => now()->addMonth()->toDateString(),
        ]);

        $response->assertRedirect("/assets/{$asset->id}");

        $this->assertDatabaseHas('asset_maintenance_schedules', [
            'asset_id' => $asset->id,
            'title'    => 'Monthly Inspection',
            'frequency'=> 'monthly',
        ]);
    }

    public function test_depreciation_reduces_current_value_over_time(): void
    {
        $society = $this->makeSociety('Green Valley', 'admin@gv.test');
        $admin   = $this->admin($society);

        // Create a category with straight-line depreciation.
        $category = AssetCategory::create([
            'society_id'        => $society->id,
            'name'              => 'Test Category',
            'depreciation_rate' => 10.0,
            'useful_life_years' => 10,
        ]);

        // Create asset with purchase date in the past so depreciation applies.
        $service = app(AssetService::class);
        $asset   = $service->create([
            'name'                => 'Old Pump',
            'asset_category_id'   => $category->id,
            'purchase_date'       => now()->subYears(5)->toDateString(),
            'purchase_cost'       => 100000,
            'salvage_value'       => 10000,
            'depreciation_method' => 'straight_line',
        ]);

        $this->assertNotNull($asset->current_value);
        // After 5 years with straight-line: 100000 - (90000/10*5) = 55000
        $this->assertLessThan($asset->purchase_cost, $asset->current_value);
        $this->assertGreaterThanOrEqual($asset->salvage_value, $asset->current_value);
    }

    public function test_assets_are_isolated_between_societies(): void
    {
        $alpha = $this->makeSociety('Alpha Society', 'alpha@test.com');
        $this->actingAs($this->admin($alpha))->post('/assets', [
            'name'          => 'Alpha Elevator',
            'purchase_cost' => 500000,
        ]);

        $beta = $this->makeSociety('Beta Society', 'beta@test.com');

        // Flush session before cross-tenant GET.
        $this->flushSession();

        $this->actingAs($this->admin($beta))
            ->get('/assets')
            ->assertOk()
            ->assertDontSee('Alpha Elevator');

        $this->assertEquals(1, Asset::withoutGlobalScopes()->count());
    }

    public function test_resident_cannot_create_an_asset(): void
    {
        $society  = $this->makeSociety('Perm Test', 'perm@test.com');
        $resident = $this->makeUser($society, 'resident');

        $this->actingAs($resident)->post('/assets', [
            'name'          => 'Sneaky Asset',
            'purchase_cost' => 100,
        ])->assertForbidden();
    }
}
