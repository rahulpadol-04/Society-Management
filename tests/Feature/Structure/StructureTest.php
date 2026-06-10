<?php

declare(strict_types=1);

namespace Tests\Feature\Structure;

use App\Models\Flat;
use App\Models\Tower;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StructureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedPlatform();
    }

    public function test_admin_can_create_a_tower_and_scaffold_units(): void
    {
        $society = $this->makeSociety();
        $admin = $this->admin($society);

        $this->actingAs($admin)->post('/towers', [
            'name'            => 'Tower A',
            'code'            => 'A',
            'type'            => 'tower',
            'total_floors'    => 3,
            'units_per_floor' => 2,
            'status'          => 'active',
            'scaffold'        => 1,
        ])->assertRedirect();

        $tower = Tower::first();
        $this->assertNotNull($tower);
        $this->assertEquals(3, $tower->floors()->count());
        $this->assertEquals(6, Flat::count());
    }

    public function test_structure_is_tenant_isolated(): void
    {
        $alpha = $this->makeSociety('Alpha', 'alpha@s.com');
        Tower::create(['society_id' => $alpha->id, 'name' => 'Alpha Tower', 'code' => 'AT', 'type' => 'tower', 'status' => 'active']);

        $beta = $this->makeSociety('Beta', 'beta@s.com');

        $this->actingAs($this->admin($beta))
            ->get('/structure')
            ->assertOk()
            ->assertDontSee('Alpha Tower');

        $this->assertEquals(1, Tower::withoutGlobalScopes()->count());
    }

    public function test_resident_without_permission_cannot_create_tower(): void
    {
        $society = $this->makeSociety();
        $resident = $this->makeUser($society, 'resident');

        $this->actingAs($resident)->post('/towers', [
            'name' => 'X', 'type' => 'tower', 'status' => 'active',
        ])->assertForbidden();
    }
}
