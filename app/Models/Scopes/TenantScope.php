<?php

declare(strict_types=1);

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Global scope that constrains every query on a tenant-aware model to the
 * currently resolved society. When tenancy is suppressed (Super Admin or
 * system jobs) or no tenant is resolved, the scope is a no-op so platform
 * level code can see across every society.
 */
class TenantScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $tenancy = app('tenancy');

        if ($tenancy->isSuppressed() || ! $tenancy->check()) {
            return;
        }

        $builder->where(
            $model->getTable().'.'.$model->getTenantColumn(),
            $tenancy->id()
        );
    }
}
