<?php

declare(strict_types=1);

namespace App\Services\Society;

use App\Models\Society;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Services\Rbac\PermissionRegistrar;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Onboards a new tenant: creates the Society, provisions its RBAC roles, the
 * owning Society Admin user and an initial (trial) subscription. Used by public
 * self-registration, the Super Admin panel and the database seeder.
 */
class SocietyRegistrationService
{
    public function __construct(protected PermissionRegistrar $registrar) {}

    public function register(array $societyData, array $adminData, ?SubscriptionPlan $plan = null): Society
    {
        return DB::transaction(function () use ($societyData, $adminData, $plan) {
            $plan ??= $this->defaultPlan();

            $trialEndsAt = $plan && $plan->trial_days > 0 ? now()->addDays($plan->trial_days) : null;

            $society = Society::create([
                'name'                => $societyData['name'],
                'slug'                => $societyData['slug'] ?? $this->uniqueSlug($societyData['name']),
                'registration_number' => $societyData['registration_number'] ?? null,
                'email'               => $societyData['email'] ?? $adminData['email'],
                'phone'               => $societyData['phone'] ?? null,
                'address_line1'       => $societyData['address_line1'] ?? null,
                'city'                => $societyData['city'] ?? null,
                'state'               => $societyData['state'] ?? null,
                'country'             => $societyData['country'] ?? 'India',
                'postal_code'         => $societyData['postal_code'] ?? null,
                'timezone'            => $societyData['timezone'] ?? 'Asia/Kolkata',
                'current_plan_id'     => $plan?->id,
                'subscription_status' => $trialEndsAt ? 'trial' : 'active',
                'trial_ends_at'       => $trialEndsAt,
                'subscription_ends_at' => $trialEndsAt,
                'status'              => 'active',
            ]);

            // Per-tenant RBAC roles + default permission grants.
            $this->registrar->provisionSocietyRoles($society);

            $admin = User::create([
                'society_id'        => $society->id,
                'name'              => $adminData['name'],
                'email'             => $adminData['email'],
                'phone'             => $adminData['phone'] ?? null,
                'password'          => $adminData['password'],
                'designation'       => 'Society Administrator',
                'status'            => 'active',
                'email_verified_at' => now(),
                'password_changed_at' => now(),
            ]);

            $admin->assignRole('society-admin');

            if ($plan) {
                Subscription::create([
                    'society_id'           => $society->id,
                    'subscription_plan_id' => $plan->id,
                    'status'               => $trialEndsAt ? 'trial' : 'active',
                    'amount'               => $plan->price,
                    'currency'             => $plan->currency,
                    'billing_cycle'        => $plan->billing_cycle,
                    'starts_at'            => now(),
                    'ends_at'              => $trialEndsAt,
                ]);
            }

            return $society->refresh();
        });
    }

    protected function defaultPlan(): ?SubscriptionPlan
    {
        return SubscriptionPlan::query()->where('is_active', true)
            ->orderByRaw("FIELD(billing_cycle, 'trial') DESC")
            ->orderBy('price')
            ->first();
    }

    protected function uniqueSlug(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i = 1;

        while (Society::withTrashed()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }
}
