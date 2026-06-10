<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Seeder;

class SubscriptionPlanSeeder extends Seeder
{
    public function run(): void
    {
        $all = config('communityos.features');

        $plans = [
            [
                'name' => 'Free Trial', 'slug' => 'trial', 'billing_cycle' => 'trial', 'price' => 0,
                'trial_days' => 14, 'max_units' => 50, 'max_users' => 60, 'max_storage_mb' => 512,
                'features' => $all, 'is_active' => true, 'sort_order' => 0,
                'description' => '14-day full-feature trial for up to 50 units.',
            ],
            [
                'name' => 'Starter', 'slug' => 'starter', 'billing_cycle' => 'monthly', 'price' => 1999,
                'trial_days' => 0, 'max_units' => 100, 'max_users' => 150, 'max_storage_mb' => 2048,
                'features' => ['visitors', 'complaints', 'notices', 'billing', 'communication'],
                'is_active' => true, 'sort_order' => 1,
                'description' => 'Essentials for small societies up to 100 units.',
            ],
            [
                'name' => 'Professional', 'slug' => 'professional', 'billing_cycle' => 'monthly', 'price' => 4999,
                'trial_days' => 0, 'max_units' => 500, 'max_users' => 800, 'max_storage_mb' => 10240,
                'features' => ['visitors', 'complaints', 'facilities', 'notices', 'assets', 'billing', 'accounting', 'communication', 'helpdesk', 'reports'],
                'is_active' => true, 'is_featured' => true, 'sort_order' => 2,
                'description' => 'Full operations + accounting for growing communities.',
            ],
            [
                'name' => 'Enterprise', 'slug' => 'enterprise', 'billing_cycle' => 'annual', 'price' => 99999,
                'trial_days' => 0, 'max_units' => null, 'max_users' => null, 'max_storage_mb' => null,
                'features' => $all, 'is_active' => true, 'sort_order' => 3,
                'description' => 'Unlimited units, all modules, priority support.',
            ],
        ];

        foreach ($plans as $plan) {
            SubscriptionPlan::updateOrCreate(['slug' => $plan['slug']], $plan);
        }
    }
}
