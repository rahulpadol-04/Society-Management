<?php

declare(strict_types=1);

namespace App\Support\Tenancy;

use App\Models\Society;
use Closure;

/**
 * Holds the "current tenant" (Society) for the lifetime of a request or queued
 * job. Registered as a singleton in the container (alias: "tenancy"). Models
 * using the BelongsToTenant trait read from here to scope their queries.
 */
class TenantManager
{
    protected ?Society $tenant = null;

    /** When true the global tenant scope is suspended (Super Admin / system jobs). */
    protected bool $suppressed = false;

    public function set(Society $society): static
    {
        $this->tenant = $society;

        return $this;
    }

    public function setById(int|string $id): ?Society
    {
        $society = Society::query()->withoutGlobalScopes()->find($id);

        if ($society) {
            $this->set($society);
        }

        return $society;
    }

    public function current(): ?Society
    {
        return $this->tenant;
    }

    public function id(): ?int
    {
        return $this->tenant?->id;
    }

    public function check(): bool
    {
        return $this->tenant !== null;
    }

    public function forget(): void
    {
        $this->tenant = null;
    }

    public function isSuppressed(): bool
    {
        return $this->suppressed;
    }

    /**
     * Run a callback with the global tenant scope disabled. Used by the Super
     * Admin panel and cross-tenant queue jobs (e.g. nightly bill generation).
     */
    public function withoutScope(Closure $callback): mixed
    {
        $previous = $this->suppressed;
        $this->suppressed = true;

        try {
            return $callback();
        } finally {
            $this->suppressed = $previous;
        }
    }

    /**
     * Temporarily switch to another tenant (restores the previous one after).
     */
    public function forTenant(Society $society, Closure $callback): mixed
    {
        $previous = $this->tenant;
        $this->set($society);

        try {
            return $callback($society);
        } finally {
            $this->tenant = $previous;
        }
    }

    /** Cache key namespaced to the active tenant. */
    public function cacheKey(string $key): string
    {
        $prefix = config('tenancy.cache_prefix', 'tenant');

        return $this->id() ? "{$prefix}:{$this->id()}:{$key}" : "global:{$key}";
    }
}
