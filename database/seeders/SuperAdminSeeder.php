<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::updateOrCreate(
            ['email' => 'super@communityos.io'],
            [
                'society_id'        => null,
                'name'              => 'Platform Owner',
                'password'          => 'Password@123',
                'status'            => 'active',
                'email_verified_at' => now(),
                'password_changed_at' => now(),
            ]
        );

        $admin->assignRole('super-admin');
    }
}
