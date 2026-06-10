<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Dynamic, database-driven RBAC for the User model. Permissions are the union
 * of every permission attached to the user's roles plus any directly granted
 * permissions, cached per user (and invalidated on any role/permission change).
 */
trait HasRoles
{
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_user')->withTimestamps();
    }

    public function directPermissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'permission_user')->withTimestamps();
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole('super-admin');
    }

    public function assignRole(Role|string ...$roles): static
    {
        $ids = collect($roles)->map(fn ($role) => $this->resolveRoleId($role))->filter()->all();
        $this->roles()->syncWithoutDetaching($ids);
        $this->forgetCachedPermissions();

        return $this;
    }

    public function syncRoles(array $roles): static
    {
        $ids = collect($roles)->map(fn ($role) => $this->resolveRoleId($role))->filter()->all();
        $this->roles()->sync($ids);
        $this->forgetCachedPermissions();

        return $this;
    }

    public function removeRole(Role|string $role): static
    {
        if ($id = $this->resolveRoleId($role)) {
            $this->roles()->detach($id);
            $this->forgetCachedPermissions();
        }

        return $this;
    }

    public function hasRole(string|array $roles): bool
    {
        $slugs = $this->roles->pluck('slug');

        return collect((array) $roles)->contains(fn ($role) => $slugs->contains($role));
    }

    public function hasAnyRole(array $roles): bool
    {
        return $this->hasRole($roles);
    }

    public function hasAllRoles(array $roles): bool
    {
        $slugs = $this->roles->pluck('slug');

        return collect($roles)->every(fn ($role) => $slugs->contains($role));
    }

    public function givePermissionTo(Permission|string $permission): static
    {
        if ($id = $this->resolvePermissionId($permission)) {
            $this->directPermissions()->syncWithoutDetaching([$id]);
            $this->forgetCachedPermissions();
        }

        return $this;
    }

    public function revokePermissionTo(Permission|string $permission): static
    {
        if ($id = $this->resolvePermissionId($permission)) {
            $this->directPermissions()->detach($id);
            $this->forgetCachedPermissions();
        }

        return $this;
    }

    /** All effective permission slugs for this user (cached). */
    public function permissionSlugs(): Collection
    {
        $key = app('tenancy')->cacheKey("user:{$this->getKey()}:permissions");

        return Cache::remember($key, now()->addHours(6), function (): Collection {
            $fromRoles = $this->roles()
                ->with('permissions:id,slug')
                ->get()
                ->flatMap(fn (Role $role) => $role->permissions->pluck('slug'));

            $direct = $this->directPermissions()->pluck('slug');

            return $fromRoles->merge($direct)->unique()->values();
        });
    }

    public function hasPermissionTo(string $permission): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        return $this->permissionSlugs()->contains($permission);
    }

    public function forgetCachedPermissions(): void
    {
        Cache::forget(app('tenancy')->cacheKey("user:{$this->getKey()}:permissions"));
    }

    protected function resolveRoleId(Role|string $role): ?int
    {
        if ($role instanceof Role) {
            return $role->id;
        }

        // Prefer this user's society-specific role, falling back to a global one.
        return Role::query()
            ->where('slug', $role)
            ->where(fn ($q) => $q->where('society_id', $this->society_id)->orWhereNull('society_id'))
            ->orderByRaw('society_id IS NULL')
            ->value('id');
    }

    protected function resolvePermissionId(Permission|string $permission): ?int
    {
        if ($permission instanceof Permission) {
            return $permission->id;
        }

        return Permission::query()->where('slug', $permission)->value('id');
    }
}
