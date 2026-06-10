<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gates a route behind a plan feature flag, e.g. `feature:accounting`.
 * Super Admins always pass.
 */
class EnsureFeatureEnabled
{
    public function handle(Request $request, Closure $next, string $feature): Response
    {
        if ($request->user()?->isSuperAdmin()) {
            return $next($request);
        }

        if (! feature_enabled($feature)) {
            abort(403, "The \"{$feature}\" module is not included in your current plan.");
        }

        return $next($request);
    }
}
