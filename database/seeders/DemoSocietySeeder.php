<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Society;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Services\Society\SocietyRegistrationService;
use Illuminate\Database\Seeder;

class DemoSocietySeeder extends Seeder
{
    public function run(SocietyRegistrationService $registration): void
    {
        if (Society::where('slug', 'green-valley')->exists()) {
            return;
        }

        $plan = SubscriptionPlan::where('slug', 'enterprise')->first();

        $society = $registration->register(
            societyData: [
                'name'  => 'Green Valley Residency',
                'slug'  => 'green-valley',
                'city'  => 'Pune',
                'state' => 'Maharashtra',
                'email' => 'office@greenvalley.test',
                'phone' => '+91-2000000000',
            ],
            adminData: [
                'name'     => 'Anita Sharma',
                'email'    => 'admin@greenvalley.test',
                'phone'    => '+91-9000000001',
                'password' => 'Password@123',
            ],
            plan: $plan,
        );

        // Additional demo users covering the key roles.
        $people = [
            ['accountant',        'Ravi Kumar',     'accountant@greenvalley.test'],
            ['security-guard',    'Suresh Gate',    'guard@greenvalley.test'],
            ['maintenance-staff', 'Manoj Fixit',    'maintenance@greenvalley.test'],
            ['resident',          'Priya Patel',    'resident@greenvalley.test'],
        ];

        foreach ($people as [$role, $name, $email]) {
            $user = User::updateOrCreate(
                ['email' => $email],
                [
                    'society_id'        => $society->id,
                    'name'              => $name,
                    'password'          => 'Password@123',
                    'status'            => 'active',
                    'email_verified_at' => now(),
                ]
            );
            $user->assignRole($role);
        }

        $this->command?->info("Demo society \"{$society->name}\" seeded (admin@greenvalley.test / Password@123).");
    }
}
