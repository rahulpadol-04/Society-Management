<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use App\Models\Scopes\TenantScope;
use App\Models\Society;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Drop-in trait that makes an Eloquent model tenant-isolated:
 *   - applies the TenantScope global scope (read isolation)
 *   - auto-stamps society_id on create (write isolation)
 *   - exposes the society() relationship
 *
 * The tenant key column defaults to config('tenancy.column') = "society_id"
 * but may be overridden per-model via $tenantColumn.
 */
trait BelongsToTenant
{
    public static function bootBelongsToTenant(): void
    {
        static::addGlobalScope(new TenantScope);

        static::creating(function ($model): void {
            $tenancy = app('tenancy');

            if (! $model->getAttribute($model->getTenantColumn()) && $tenancy->check()) {
                $model->setAttribute($model->getTenantColumn(), $tenancy->id());
            }
        });
    }

    public function getTenantColumn(): string
    {
        return property_exists($this, 'tenantColumn')
            ? $this->tenantColumn
            : config('tenancy.column', 'society_id');
    }

    public function society(): BelongsTo
    {
        return $this->belongsTo(Society::class, $this->getTenantColumn());
    }

    /** Query helper to deliberately query across all tenants. */
    public function scopeAcrossTenants($query)
    {
        return $query->withoutGlobalScope(TenantScope::class);
    }
}
