<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

/**
 * Registers the dynamic RBAC Gate layer.
 *
 *  - Gate::before grants Super Admins everything.
 *  - Every "{module}.{ability}" permission declared in config/communityos.php
 *    is registered as a Gate ability backed by the user's effective (dynamic,
 *    DB-driven) permission set. This means @can('complaints.create') and
 *    $user->can('complaints.create') work everywhere with zero per-module glue.
 *
 * Model policies are auto-discovered by Laravel's naming convention
 * (App\Models\Complaint -> App\Policies\ComplaintPolicy).
 */
class AuthServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Gate::before(function (User $user, string $ability) {
            return $user->isSuperAdmin() ? true : null;
        });

        foreach ((array) config('communityos.modules', []) as $module => $definition) {
            foreach ($definition['abilities'] ?? [] as $ability) {
                $permission = "{$module}.{$ability}";

                Gate::define($permission, fn (User $user) => $user->hasPermissionTo($permission));
            }
        }
    }
}
