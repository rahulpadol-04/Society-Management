@php
    $items = [
        'Configuration' => [
            ['settings.index',   'General Settings',      'bi-sliders',          'Currency, timezone, contact & formats', true],
            ['settings.billing', 'Billing Configuration', 'bi-receipt-cutoff',   'How maintenance bills are calculated', true],
        ],
        'Master Data' => [
            ['maintenance.heads.index', 'Billing Components', 'bi-list-check',  'Charge heads (water, sinking fund…)', \Route::has('maintenance.heads.index')],
            ['structure.index',         'Towers & Units',     'bi-diagram-3',   'Society structure master', \Route::has('structure.index')],
            ['society-profile.index',   'Society Profile',    'bi-house-gear',  'Name, address, logo & registration', \Route::has('society-profile.index')],
        ],
        'Access Control' => [
            ['settings.roles', 'Roles & Permissions', 'bi-shield-lock', 'Configure what each role can do', auth()->user()->can('settings.roles')],
        ],
    ];
@endphp
<div class="card settings-nav">
    <div class="card-body p-2">
        @foreach ($items as $group => $links)
            <div class="settings-nav-group px-2 pt-2 pb-1 text-uppercase small fw-bold text-muted">{{ $group }}</div>
            @foreach ($links as [$route, $label, $icon, $desc, $show])
                @if ($show)
                    <a href="{{ route($route) }}" class="settings-nav-link {{ request()->routeIs($route) || (str_starts_with($route,'settings.roles') && request()->routeIs('settings.roles*')) ? 'active' : '' }}">
                        <i class="bi {{ $icon }}"></i>
                        <span>
                            <span class="d-block fw-semibold">{{ $label }}</span>
                            <span class="d-block small opacity-75">{{ $desc }}</span>
                        </span>
                    </a>
                @endif
            @endforeach
        @endforeach
    </div>
</div>
