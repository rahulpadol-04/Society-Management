<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Support\Tenancy\TenantManager;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Blocks tenant access when the society has no active (or trialing)
 * subscription. Super Admins and the billing pages themselves are exempt so an
 * admin can always renew.
 */
class EnsureSubscriptionActive
{
    public function __construct(protected TenantManager $tenancy) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || $user->isSuperAdmin() || ! $this->tenancy->check()) {
            return $next($request);
        }

        if ($request->routeIs('billing.*', 'subscription.*')) {
            return $next($request);
        }

        $society = $this->tenancy->current();

        if (! $society->hasActiveSubscription()) {
            if ($request->expectsJson()) {
                abort(402, 'Your subscription has expired. Please renew to continue.');
            }

            return redirect()->route('subscription.expired')
                ->with('warning', 'Your subscription has expired. Please renew to continue.');
        }

        return $next($request);
    }
}
