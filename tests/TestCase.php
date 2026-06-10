<?php

declare(strict_types=1);

namespace Tests;

use App\Models\Society;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Services\Rbac\PermissionRegistrar;
use App\Services\Society\SocietyRegistrationService;
use Database\Seeders\SubscriptionPlanSeeder;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Str;

abstract class TestCase extends BaseTestCase
{
    /**
     * Build the platform RBAC catalogue + subscription plans so feature gates,
     * permissions and tenant provisioning behave exactly as in production.
     * Call from a test's setUp() after parent::setUp() when the test touches
     * tenant-scoped, permission-gated or feature-gated routes.
     */
    protected function seedPlatform(): void
    {
        $registrar = app(PermissionRegistrar::class);
        $registrar->syncCatalogue();
        $registrar->createGlobalRoles();

        $this->seed(SubscriptionPlanSeeder::class);
    }

    /**
     * Register a fresh tenant (Society) with an owning Society Admin and an
     * all-features Enterprise plan, then bind it as the current tenant.
     */
    protected function makeSociety(string $name = 'Test Society', ?string $email = null): Society
    {
        $email ??= 'admin+'.Str::random(6).'@test.com';
        $plan = SubscriptionPlan::where('slug', 'enterprise')->first();

        $society = app(SocietyRegistrationService::class)->register(
            ['name' => $name, 'email' => $email],
            ['name' => 'Admin', 'email' => $email, 'password' => 'Password@123'],
            $plan,
        );

        tenancy()->set($society);

        return $society;
    }

    /** The owning Society Admin of a society. */
    protected function admin(Society $society): User
    {
        return $society->users()->first();
    }

    /**
     * Create an additional user in a society holding the given role and return
     * it (useful for asserting role-specific authorization).
     */
    protected function makeUser(Society $society, string $role, array $attributes = []): User
    {
        $user = User::withoutGlobalScopes()->create(array_merge([
            'society_id'        => $society->id,
            'name'              => ucfirst($role),
            'email'             => $role.'+'.Str::random(6).'@test.com',
            'password'          => 'Password@123',
            'status'            => 'active',
            'email_verified_at' => now(),
        ], $attributes));

        $user->assignRole($role);

        return $user;
    }
}
