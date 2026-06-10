<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Platform foundation (order matters).
        $this->call([
            RolePermissionSeeder::class,
            SubscriptionPlanSeeder::class,
            SuperAdminSeeder::class,
            DemoSocietySeeder::class,
        ]);

        // Optional per-module demo seeders are auto-discovered when present so
        // each feature module can ship its own sample data independently.
        foreach ([
            'Database\\Seeders\\Modules\\StructureSeeder',
            'Database\\Seeders\\Modules\\ResidentSeeder',
            'Database\\Seeders\\Modules\\VisitorSeeder',
            'Database\\Seeders\\Modules\\ComplaintSeeder',
            'Database\\Seeders\\Modules\\MaintenanceSeeder',
            'Database\\Seeders\\Modules\\FacilitySeeder',
            'Database\\Seeders\\Modules\\NoticeSeeder',
            'Database\\Seeders\\Modules\\AssetSeeder',
            'Database\\Seeders\\Modules\\AccountingSeeder',
            'Database\\Seeders\\Modules\\VendorSeeder',
            'Database\\Seeders\\Modules\\StaffSeeder',
            'Database\\Seeders\\Modules\\HelpdeskSeeder',
            'Database\\Seeders\\Modules\\CommunicationSeeder',
            'Database\\Seeders\\Modules\\SaasSeeder',
        ] as $seeder) {
            if (class_exists($seeder)) {
                $this->call($seeder);
            }
        }
    }
}
