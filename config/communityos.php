<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| CommunityOS Platform Configuration
|--------------------------------------------------------------------------
|
| Central, single-source-of-truth definition of the platform's roles,
| modules and the permission matrix. The RolePermissionSeeder and the
| PermissionServiceProvider both read from here so that adding a module or
| permission is a one-line change that automatically flows into the dynamic
| RBAC layer, the Gate definitions and the navigation menu.
|
*/

return [

    /*
    |--------------------------------------------------------------------------
    | System Roles
    |--------------------------------------------------------------------------
    | scope: "global"  -> platform level role, not bound to a society (tenant)
    |        "society" -> created per society (tenant) on registration
    */
    'roles' => [
        'super-admin'       => ['name' => 'Super Admin',       'scope' => 'global',  'level' => 100, 'description' => 'Full platform owner. Manages all societies, plans and billing.'],
        'society-admin'     => ['name' => 'Society Admin',      'scope' => 'society', 'level' => 90,  'description' => 'Owner/Secretary of a society. Full control inside the tenant.'],
        'sub-admin'         => ['name' => 'Sub Admin',          'scope' => 'society', 'level' => 80,  'description' => 'Committee member with delegated administrative rights.'],
        'accountant'        => ['name' => 'Accountant',         'scope' => 'society', 'level' => 70,  'description' => 'Manages billing, payments and the accounting ledgers.'],
        'security-guard'    => ['name' => 'Security Guard',     'scope' => 'society', 'level' => 40,  'description' => 'Operates the gate / visitor management desk.'],
        'maintenance-staff' => ['name' => 'Maintenance Staff',  'scope' => 'society', 'level' => 40,  'description' => 'Resolves assigned complaints and asset maintenance tasks.'],
        'resident'          => ['name' => 'Resident',           'scope' => 'society', 'level' => 30,  'description' => 'Flat owner. Raises requests, pays bills, books facilities.'],
        'tenant'            => ['name' => 'Tenant',             'scope' => 'society', 'level' => 25,  'description' => 'Renter occupying a flat under a resident owner.'],
        'family-member'     => ['name' => 'Family Member',      'scope' => 'society', 'level' => 20,  'description' => 'Dependent linked to a resident/tenant account.'],
        'vendor'            => ['name' => 'Vendor',             'scope' => 'society', 'level' => 20,  'description' => 'External service provider fulfilling work orders.'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Modules & Permission Matrix
    |--------------------------------------------------------------------------
    | Each module declares:
    |   group        -> sidebar grouping
    |   name / icon  -> presentation
    |   feature      -> the plan feature flag that must be enabled for the
    |                   tenant's subscription to access the module (null = core)
    |   abilities    -> the permission "actions" available; final permission
    |                   slug is "{module}.{ability}" e.g. "complaints.create"
    |   roles        -> default roles granted *all* abilities at seed time
    */
    'modules' => [
        // ---- Platform (Super Admin) ----
        'societies'      => ['group' => 'Platform', 'name' => 'Societies',        'icon' => 'bi-building',        'feature' => null,           'abilities' => ['view', 'create', 'update', 'delete', 'suspend', 'impersonate'], 'roles' => ['super-admin']],
        'plans'          => ['group' => 'Platform', 'name' => 'Subscription Plans','icon' => 'bi-stack',          'feature' => null,           'abilities' => ['view', 'create', 'update', 'delete'],                          'roles' => ['super-admin']],
        'subscriptions'  => ['group' => 'Platform', 'name' => 'Subscriptions',    'icon' => 'bi-receipt',         'feature' => null,           'abilities' => ['view', 'create', 'update', 'cancel', 'refund'],                'roles' => ['super-admin']],
        'cms'            => ['group' => 'Platform', 'name' => 'CMS Pages',         'icon' => 'bi-file-earmark-text','feature' => null,          'abilities' => ['view', 'create', 'update', 'delete', 'publish'],               'roles' => ['super-admin']],
        'blog'           => ['group' => 'Platform', 'name' => 'Blog',             'icon' => 'bi-newspaper',       'feature' => null,           'abilities' => ['view', 'create', 'update', 'delete', 'publish'],               'roles' => ['super-admin']],
        'inquiries'      => ['group' => 'Platform', 'name' => 'Contact Inquiries','icon' => 'bi-envelope-paper',  'feature' => null,           'abilities' => ['view', 'update', 'delete'],                                    'roles' => ['super-admin']],
        'platform-analytics' => ['group' => 'Platform', 'name' => 'Usage Analytics','icon' => 'bi-graph-up-arrow','feature' => null,          'abilities' => ['view'],                                                        'roles' => ['super-admin']],

        // ---- Society Structure ----
        'society-profile'=> ['group' => 'Society',  'name' => 'Society Profile',  'icon' => 'bi-house-gear',      'feature' => null,           'abilities' => ['view', 'update'],                                              'roles' => ['society-admin', 'sub-admin']],
        'structure'      => ['group' => 'Society',  'name' => 'Towers & Flats',   'icon' => 'bi-diagram-3',       'feature' => null,           'abilities' => ['view', 'create', 'update', 'delete'],                          'roles' => ['society-admin', 'sub-admin']],
        'parking'        => ['group' => 'Society',  'name' => 'Parking Slots',    'icon' => 'bi-p-square',        'feature' => null,           'abilities' => ['view', 'create', 'update', 'delete', 'assign'],                'roles' => ['society-admin', 'sub-admin']],
        'documents'      => ['group' => 'Society',  'name' => 'Documents',        'icon' => 'bi-folder',          'feature' => null,           'abilities' => ['view', 'create', 'delete'],                                    'roles' => ['society-admin', 'sub-admin']],

        // ---- People ----
        'residents'      => ['group' => 'People',   'name' => 'Residents',        'icon' => 'bi-people',          'feature' => null,           'abilities' => ['view', 'create', 'update', 'delete', 'approve', 'export'],     'roles' => ['society-admin', 'sub-admin']],
        'vehicles'       => ['group' => 'People',   'name' => 'Vehicles',         'icon' => 'bi-car-front',       'feature' => null,           'abilities' => ['view', 'create', 'update', 'delete'],                          'roles' => ['society-admin', 'sub-admin', 'resident' => ['view', 'create', 'update'], 'tenant' => ['view', 'create', 'update']]],
        'directory'      => ['group' => 'People',   'name' => 'Resident Directory','icon' => 'bi-journal-text',   'feature' => null,           'abilities' => ['view', 'export'],                                              'roles' => ['society-admin', 'sub-admin', 'resident' => ['view'], 'tenant' => ['view']]],
        'society-staff'  => ['group' => 'People',   'name' => 'Staff',            'icon' => 'bi-person-badge',    'feature' => 'staff',        'abilities' => ['view', 'create', 'update', 'delete', 'attendance', 'payroll'], 'roles' => ['society-admin', 'sub-admin']],

        // ---- Gate / Operations ----
        'visitors'       => ['group' => 'Operations','name' => 'Visitors',        'icon' => 'bi-door-open',       'feature' => 'visitors',     'abilities' => ['view', 'create', 'approve', 'checkin', 'checkout', 'export'],  'roles' => ['society-admin', 'sub-admin', 'security-guard' => ['view', 'create', 'checkin', 'checkout'], 'resident' => ['view', 'create'], 'tenant' => ['view', 'create']]],
        'complaints'     => ['group' => 'Operations','name' => 'Complaints',      'icon' => 'bi-exclamation-octagon','feature' => 'complaints', 'abilities' => ['view', 'create', 'update', 'assign', 'resolve', 'close', 'feedback', 'export'], 'roles' => ['society-admin', 'sub-admin', 'maintenance-staff' => ['view', 'update', 'resolve', 'close'], 'resident' => ['view', 'create', 'feedback'], 'tenant' => ['view', 'create', 'feedback']]],
        'facilities'     => ['group' => 'Operations','name' => 'Facility Booking','icon' => 'bi-calendar-check',  'feature' => 'facilities',   'abilities' => ['view', 'create', 'update', 'delete', 'book', 'approve', 'cancel'], 'roles' => ['society-admin', 'sub-admin', 'resident' => ['view', 'book', 'cancel'], 'tenant' => ['view', 'book', 'cancel']]],
        'notices'        => ['group' => 'Operations','name' => 'Notice Board',    'icon' => 'bi-megaphone',       'feature' => 'notices',      'abilities' => ['view', 'create', 'update', 'delete', 'publish', 'vote'],        'roles' => ['society-admin', 'sub-admin', 'resident' => ['view', 'vote'], 'tenant' => ['view', 'vote']]],
        'assets'         => ['group' => 'Operations','name' => 'Assets',          'icon' => 'bi-box-seam',        'feature' => 'assets',       'abilities' => ['view', 'create', 'update', 'delete', 'schedule'],              'roles' => ['society-admin', 'sub-admin', 'maintenance-staff']],

        // ---- Finance ----
        'maintenance'    => ['group' => 'Finance',  'name' => 'Maintenance Billing','icon' => 'bi-cash-coin',     'feature' => 'billing',      'abilities' => ['view', 'create', 'update', 'delete', 'generate', 'collect', 'waive', 'export'], 'roles' => ['society-admin', 'sub-admin', 'accountant', 'resident' => ['view'], 'tenant' => ['view']]],
        'accounting'     => ['group' => 'Finance',  'name' => 'Accounting',       'icon' => 'bi-journals',        'feature' => 'accounting',   'abilities' => ['view', 'create', 'update', 'delete', 'post', 'reports'],        'roles' => ['society-admin', 'accountant']],
        'vendors'        => ['group' => 'Finance',  'name' => 'Vendors',          'icon' => 'bi-truck',           'feature' => 'vendors',      'abilities' => ['view', 'create', 'update', 'delete', 'pay', 'rate'],            'roles' => ['society-admin', 'sub-admin', 'accountant']],

        // ---- Communication & Support ----
        'communication'  => ['group' => 'Engagement','name' => 'Communication',   'icon' => 'bi-chat-dots',       'feature' => 'communication','abilities' => ['view', 'send', 'broadcast', 'templates'],                      'roles' => ['society-admin', 'sub-admin']],
        'helpdesk'       => ['group' => 'Engagement','name' => 'Helpdesk',        'icon' => 'bi-life-preserver',  'feature' => 'helpdesk',     'abilities' => ['view', 'create', 'update', 'assign', 'escalate', 'close'],     'roles' => ['society-admin', 'sub-admin', 'resident' => ['view', 'create'], 'tenant' => ['view', 'create']]],
        'reports'        => ['group' => 'Engagement','name' => 'Reports',         'icon' => 'bi-file-earmark-bar-graph','feature' => 'reports', 'abilities' => ['view', 'export'],                                              'roles' => ['society-admin', 'sub-admin', 'accountant']],
        'settings'       => ['group' => 'Engagement','name' => 'Settings',        'icon' => 'bi-gear',            'feature' => null,           'abilities' => ['view', 'update', 'roles', 'permissions'],                      'roles' => ['society-admin']],
    ],

    /*
    |--------------------------------------------------------------------------
    | Plan Feature Catalogue
    |--------------------------------------------------------------------------
    | The complete set of toggleable features a subscription plan may grant.
    | A "true" value here is the default for the Enterprise plan in the seeder.
    */
    'features' => [
        'visitors', 'complaints', 'facilities', 'notices', 'assets',
        'billing', 'accounting', 'vendors', 'communication', 'helpdesk',
        'reports', 'staff',
    ],

    /*
    |--------------------------------------------------------------------------
    | Operational Defaults
    |--------------------------------------------------------------------------
    */
    'billing' => [
        'gst_percentage'        => 18.0,
        'late_fee_percentage'   => 2.0,     // per month on outstanding
        'late_fee_grace_days'   => 10,
        'invoice_prefix'        => 'INV',
        'receipt_prefix'        => 'RCPT',
    ],

    'complaints' => [
        'default_sla_hours' => 48,
        'statuses'          => ['open', 'assigned', 'in_progress', 'resolved', 'closed'],
        'priorities'        => ['low', 'medium', 'high', 'critical'],
    ],

    'visitors' => [
        'pass_validity_minutes' => 720,
        'types'                 => ['guest', 'delivery', 'cab', 'service', 'vendor'],
    ],

    'pagination' => 15,
];
