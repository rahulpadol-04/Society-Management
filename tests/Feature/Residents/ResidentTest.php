<?php

declare(strict_types=1);

namespace Tests\Feature\Residents;

use App\Models\Resident;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResidentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedPlatform();
    }

    public function test_admin_can_create_a_resident(): void
    {
        $society = $this->makeSociety('Green Valley', 'admin@gv.test');
        $admin   = $this->admin($society);

        $response = $this->actingAs($admin)->post('/residents', [
            'name'   => 'Rajesh Kumar',
            'type'   => 'owner',
            'phone'  => '9876543210',
            'status' => 'active',
        ]);

        $resident = Resident::first();

        $this->assertNotNull($resident);
        $response->assertRedirect("/residents/{$resident->id}");
        $this->assertEquals('Rajesh Kumar', $resident->name);
        $this->assertEquals('owner', $resident->type);
        $this->assertEquals($society->id, $resident->society_id);
    }

    public function test_residents_are_isolated_between_societies(): void
    {
        $alpha = $this->makeSociety('Alpha Society', 'alpha@test.com');
        $this->actingAs($this->admin($alpha))->post('/residents', [
            'name' => 'Alpha Resident', 'type' => 'owner', 'status' => 'active',
        ]);

        $beta = $this->makeSociety('Beta Society', 'beta@test.com');

        // Fresh session so Alpha's "created" flash message doesn't leak into
        // Beta's page (a different user/browser would be a different session).
        $this->flushSession();

        // Beta admin must not see Alpha's resident (tenant scope).
        $this->actingAs($this->admin($beta))
            ->get('/residents')
            ->assertOk()
            ->assertDontSee('Alpha Resident');

        // Exactly one resident exists across all tenants.
        $this->assertEquals(1, Resident::withoutGlobalScopes()->count());
    }

    public function test_security_guard_cannot_create_resident(): void
    {
        $society = $this->makeSociety('Guard Test Society', 'guard@test.com');
        $guard   = $this->makeUser($society, 'security-guard');

        $this->actingAs($guard)->post('/residents', [
            'name' => 'Sneaky Resident', 'type' => 'owner', 'status' => 'active',
        ])->assertForbidden();
    }

    public function test_admin_can_add_family_member_to_resident(): void
    {
        $society = $this->makeSociety('Family Test', 'fam@test.com');
        $admin   = $this->admin($society);

        $this->actingAs($admin)->post('/residents', [
            'name' => 'Parent User', 'type' => 'owner', 'status' => 'active',
        ]);

        $parent = Resident::first();

        $this->actingAs($admin)->post("/residents/{$parent->id}/family", [
            'name'     => 'Child User',
            'type'     => 'family_member',
            'relation' => 'child',
            'status'   => 'active',
        ])->assertRedirect("/residents/{$parent->id}");

        $parent->load('familyMembers');

        $this->assertEquals(1, $parent->familyMembers->count());
        $this->assertEquals('Child User', $parent->familyMembers->first()->name);
        $this->assertEquals('family_member', $parent->familyMembers->first()->type);
    }
}
