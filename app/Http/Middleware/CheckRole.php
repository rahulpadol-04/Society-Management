<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Route guard: `role:society-admin,sub-admin`. Passes if the user holds any of
 * the listed roles (Super Admin always passes).
 */
class CheckRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(401);
        }

        if ($user->isSuperAdmin() || $user->hasAnyRole($roles)) {
            return $next($request);
        }

        abort(403, 'You do not have the required role to access this resource.');
    }
}
