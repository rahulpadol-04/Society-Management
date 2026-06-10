<?php

declare(strict_types=1);

namespace Tests\Feature\SuperAdmin;

use App\Models\Society;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuperAdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedPlatform();
    }

    protected function makeSuperAdmin(): User
    {
        $su = User::withoutGlobalScopes()->create([
            'name'                => 'SU',
            'email'               => 'su@test.com',
            'password'            => 'Password@123',
            'status'              => 'active',
            'email_verified_at'   => now(),
            'password_changed_at' => now(),
        ]);
        $su->assignRole('super-admin');

        return $su;
    }

    public function test_super_admin_can_list_societies(): void
    {
        $su = $this->makeSuperAdmin();

        // Create a society so the list is non-empty.
        $this->makeSociety('Alpha Society', 'alpha@societies.test');

        $this->actingAs($su)
            ->get('/societies')
            ->assertOk()
            ->assertSee('Alpha Society');
    }

    public function test_super_admin_can_create_a_plan(): void
    {
        $su = $this->makeSuperAdmin();

        $this->actingAs($su)->post('/plans', [
            'name'          => 'Basic Plan',
            'slug'          => 'basic',
            'billing_cycle' => 'monthly',
            'price'         => 999,
            'is_active'     => 1,
            'is_featured'   => 0,
        ])->assertRedirect(route('plans.index'));

        $this->assertDatabaseHas('subscription_plans', ['slug' => 'basic']);
    }

    public function test_regular_society_admin_is_forbidden_from_societies_list(): void
    {
        $society = $this->makeSociety('Beta Society', 'beta@test.test');
        $admin   = $this->admin($society);

        $this->actingAs($admin)
            ->get('/societies')
            ->assertForbidden();
    }

    public function test_regular_society_admin_is_forbidden_from_plans(): void
    {
        $society = $this->makeSociety('Gamma Society', 'gamma@test.test');
        $admin   = $this->admin($society);

        $this->actingAs($admin)
            ->get('/plans')
            ->assertForbidden();
    }

    public function test_super_admin_can_impersonate_a_society(): void
    {
        $su      = $this->makeSuperAdmin();
        $society = $this->makeSociety('Delta Society', 'delta@test.test');

        $response = $this->actingAs($su)
            ->post(route('societies.impersonate', $society));

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('impersonate_society_id', $society->id);
    }

    public function test_super_admin_can_stop_impersonating(): void
    {
        $su = $this->makeSuperAdmin();

        $response = $this->actingAs($su)
            ->withSession(['impersonate_society_id' => 999])
            ->post(route('societies.stop-impersonating'));

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionMissing('impersonate_society_id');
    }

    public function test_super_admin_can_view_plans_index(): void
    {
        $su = $this->makeSuperAdmin();

        $this->actingAs($su)
            ->get('/plans')
            ->assertOk();
    }

    public function test_super_admin_can_view_subscriptions_index(): void
    {
        $su = $this->makeSuperAdmin();

        $this->actingAs($su)
            ->get('/subscriptions')
            ->assertOk();
    }

    public function test_super_admin_can_view_platform_analytics(): void
    {
        $su = $this->makeSuperAdmin();

        $this->actingAs($su)
            ->get('/platform-analytics')
            ->assertOk();
    }
}
