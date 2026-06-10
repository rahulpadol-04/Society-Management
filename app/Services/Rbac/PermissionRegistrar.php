<?php

declare(strict_types=1);

namespace App\Services\Rbac;

use App\Models\Permission;
use App\Models\Role;
use App\Models\Society;
use Illuminate\Support\Str;

/**
 * Builds the RBAC data from config/communityos.php:
 *
 *  - syncCatalogue()           : the global permission catalogue ("{module}.{ability}")
 *  - createGlobalRoles()       : platform roles (Super Admin)
 *  - provisionSocietyRoles()   : per-tenant copies of the society roles with
 *                                their default permission grants
 *
 * Because every society gets its own role rows, a Society Admin can later add /
 * remove permissions per role without affecting other tenants — the dynamic,
 * configurable RBAC requirement.
 */
class PermissionRegistrar
{
    /** Create or update every permission declared by the module config. */
    public function syncCatalogue(): void
    {
        foreach ((array) config('communityos.modules', []) as $module => $def) {
            foreach ($def['abilities'] ?? [] as $ability) {
                Permission::updateOrCreate(
                    ['slug' => "{$module}.{$ability}"],
                    [
                        'name'    => Str::headline($ability).' '.Str::headline($module),
                        'module'  => $module,
                        'ability' => $ability,
                        'group'   => $def['group'] ?? 'General',
                    ]
                );
            }
        }
    }

    /** Create global platform roles (Super Admin) and grant them everything. */
    public function createGlobalRoles(): void
    {
        foreach ((array) config('communityos.roles', []) as $slug => $def) {
            if (($def['scope'] ?? 'society') !== 'global') {
                continue;
            }

            $role = Role::updateOrCreate(
                ['society_id' => null, 'slug' => $slug],
                [
                    'name'        => $def['name'],
                    'scope'       => 'global',
                    'level'       => $def['level'] ?? 100,
                    'description' => $def['description'] ?? null,
                    'is_system'   => true,
                ]
            );

            $role->permissions()->sync(Permission::pluck('id'));
        }
    }

    /** Provision the society-scoped roles for a single tenant. */
    public function provisionSocietyRoles(Society $society): void
    {
        foreach ((array) config('communityos.roles', []) as $slug => $def) {
            if (($def['scope'] ?? 'society') !== 'society') {
                continue;
            }

            $role = Role::updateOrCreate(
                ['society_id' => $society->id, 'slug' => $slug],
                [
                    'name'        => $def['name'],
                    'scope'       => 'society',
                    'level'       => $def['level'] ?? 10,
                    'description' => $def['description'] ?? null,
                    'is_system'   => true,
                ]
            );

            $role->permissions()->sync($this->permissionIdsFor($slug));
        }
    }

    /**
     * Resolve which permission ids a society role should receive by default.
     *
     * A module's "roles" entry may be either:
     *   - a string  ("resident")            -> the role gets ALL the module abilities
     *   - a key=>array ("resident"=>['view','create']) -> the role gets that SUBSET
     * This keeps sensible least-privilege defaults (e.g. a resident can request a
     * visitor pass but cannot approve one) while remaining fully reconfigurable
     * per society at runtime.
     */
    protected function permissionIdsFor(string $roleSlug): array
    {
        $slugs = [];

        foreach ((array) config('communityos.modules', []) as $module => $def) {
            $isPlatform = ($def['group'] ?? null) === 'Platform';
            $allAbilities = $def['abilities'] ?? [];

            // Society Admin owns every non-platform module entirely.
            if ($roleSlug === 'society-admin' && ! $isPlatform) {
                $abilities = $allAbilities;
            } else {
                $abilities = $this->roleAbilities($roleSlug, $def['roles'] ?? [], $allAbilities);
                if ($abilities === null) {
                    continue;
                }
            }

            foreach ($abilities as $ability) {
                $slugs[] = "{$module}.{$ability}";
            }
        }

        return Permission::whereIn('slug', array_unique($slugs))->pluck('id')->all();
    }

    /**
     * Determine the abilities a role receives for a module from its "roles"
     * definition. Returns null when the role is not granted the module at all.
     *
     * @param  array<int|string, string|array<int,string>>  $roles
     * @param  array<int,string>  $allAbilities
     * @return array<int,string>|null
     */
    protected function roleAbilities(string $roleSlug, array $roles, array $allAbilities): ?array
    {
        foreach ($roles as $key => $value) {
            // ["resident"] -> full module access for that role.
            if (is_int($key) && $value === $roleSlug) {
                return $allAbilities;
            }

            // ["resident" => ['view','create']] -> explicit subset.
            if ($key === $roleSlug && is_array($value)) {
                return $value;
            }
        }

        return null;
    }
}
