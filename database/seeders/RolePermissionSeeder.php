<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Services\Rbac\PermissionRegistrar;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    public function run(PermissionRegistrar $registrar): void
    {
        $registrar->syncCatalogue();      // build the permission catalogue
        $registrar->createGlobalRoles();  // Super Admin (global) + grant all
    }
}
