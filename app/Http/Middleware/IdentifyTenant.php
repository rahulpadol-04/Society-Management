<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Society;
use App\Support\Tenancy\TenantManager;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Resolves the current tenant (Society) and binds it to the TenantManager.
 *
 *  - Super Admins are NOT bound to a tenant (they operate across all societies)
 *    unless they explicitly impersonate one via the configured header / query.
 *  - Everyone else is bound to their own user->society_id.
 *
 * Resolution strategy is configurable (auth | subdomain | header).
 */
class IdentifyTenant
{
    public function __construct(protected TenantManager $tenancy) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Super Admin: cross-tenant by default, may impersonate a society.
        if ($user && $user->isSuperAdmin()) {
            // Session is only available on stateful (web) requests — guard it so
            // stateless API requests don't throw "Session store not set".
            $impersonate = $request->header(config('tenancy.header'))
                ?? $request->query('society_id')
                ?? ($request->hasSession() ? $request->session()->get('impersonate_society_id') : null);

            if ($impersonate) {
                $this->tenancy->setById($impersonate);
            }

            return $next($request);
        }

        $society = match (config('tenancy.resolver')) {
            'subdomain' => $this->fromSubdomain($request),
            'header'    => $this->fromHeader($request),
            default     => $this->fromUser($request),
        };

        if ($society) {
            abort_if($society->isSuspended(), 423, 'This society account is suspended.');
            $this->tenancy->set($society);
        }

        return $next($request);
    }

    protected function fromUser(Request $request): ?Society
    {
        $id = $request->user()?->society_id;

        return $id ? Society::query()->withoutGlobalScopes()->find($id) : null;
    }

    protected function fromHeader(Request $request): ?Society
    {
        $id = $request->header(config('tenancy.header'));

        return $id ? Society::query()->withoutGlobalScopes()->find($id) : $this->fromUser($request);
    }

    protected function fromSubdomain(Request $request): ?Society
    {
        $central = config('tenancy.central_domain');
        $host = $request->getHost();

        if ($host === $central || ! str_ends_with($host, '.'.$central)) {
            return $this->fromUser($request);
        }

        $slug = str($host)->before('.'.$central)->toString();

        return Society::query()->withoutGlobalScopes()->where('slug', $slug)->first()
            ?? $this->fromUser($request);
    }
}
