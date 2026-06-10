<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Multi-Tenancy Configuration
|--------------------------------------------------------------------------
|
| CommunityOS uses a SHARED DATABASE with row-level tenant isolation. Every
| tenant-scoped table carries a `society_id` foreign key. The BelongsToTenant
| trait applies a global Eloquent scope so queries are automatically filtered
| to the current society, and stamps `society_id` on insert.
|
*/

return [

    /*
    | The Eloquent model that represents a tenant.
    */
    'model' => \App\Models\Society::class,

    /*
    | The foreign key column used on every tenant-scoped table.
    */
    'column' => 'society_id',

    /*
    | How the current tenant is resolved for an incoming request:
    |   auth      -> from the authenticated user's society_id (default)
    |   subdomain -> from {society}.communityos.test
    |   header    -> from a request header (machine-to-machine APIs)
    */
    'resolver' => env('TENANCY_RESOLVER', 'auth'),

    'central_domain' => env('APP_CENTRAL_DOMAIN', 'communityos.test'),

    'header' => env('TENANCY_HEADER', 'X-Society-Id'),

    /*
    | Roles that bypass the tenant scope entirely (operate across all
    | societies). The Super Admin can optionally "impersonate" a society by
    | supplying the tenant header / query string.
    */
    'global_roles' => ['super-admin'],

    /*
    | Cache key prefix is suffixed with the tenant id so cached data never
    | bleeds between societies.
    */
    'cache_prefix' => 'tenant',

    /*
    | Tables that are intentionally global (never scoped by society_id).
    */
    'central_tables' => [
        'societies', 'subscription_plans', 'subscriptions', 'users',
        'roles', 'permissions', 'permission_role', 'cms_pages', 'blogs',
        'contact_inquiries', 'payment_gateways', 'migrations',
        'personal_access_tokens', 'jobs', 'cache', 'sessions',
    ],
];
