@php
    // Build the navigation from the module config. An item appears only when its
    // route exists, the user holds the "{module}.view" permission and the plan
    // feature (if any) is enabled. Grouped by the module "group".
    $user = auth()->user();
    $society = current_society();
    $groups = collect(config('communityos.modules'))
        ->filter(function ($def, $module) use ($user, $society) {
            if (! \Route::has($module.'.index')) {
                return false;
            }
            // A Super Admin operating without an active tenant (no impersonation)
            // only sees Platform modules — society-scoped pages need a tenant.
            if ($user->isSuperAdmin() && ! $society && ($def['group'] ?? null) !== 'Platform') {
                return false;
            }
            if (($def['feature'] ?? null) && ! $user->isSuperAdmin() && ! feature_enabled($def['feature'])) {
                return false;
            }
            return $user->can($module.'.view');
        })
        // preserveKeys:true keeps the module slug as the key inside each group
        // so route($module.'.index') resolves correctly (groupBy reindexes by default).
        ->groupBy('group', true);
@endphp
<aside class="app-sidebar" id="appSidebar">
    <div class="sidebar-brand d-flex align-items-center px-3 py-3">
        <i class="bi bi-buildings-fill fs-4 text-primary me-2"></i>
        <span class="fw-bold fs-5">{{ config('app.name') }}</span>
    </div>

    @auth
        @php $soc = current_society(); @endphp
        <div class="sidebar-society px-3 py-2 mx-2 mb-1">
            <div class="text-white fw-semibold text-truncate">{{ $soc?->name ?? 'Platform Administration' }}</div>
            <div class="small text-muted">{{ $soc?->registration_number ?? $soc?->slug ?? 'Super Admin' }}</div>
        </div>
    @endauth

    <nav class="sidebar-nav">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                    <i class="bi bi-speedometer2 me-2"></i> Dashboard
                </a>
            </li>

            @foreach ($groups as $group => $modules)
                <li class="nav-section text-uppercase small text-muted px-3 mt-3 mb-1">{{ $group }}</li>
                @foreach ($modules as $module => $def)
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs($module.'.*') ? 'active' : '' }}"
                           href="{{ route($module.'.index') }}">
                            <i class="bi {{ $def['icon'] ?? 'bi-dot' }} me-2"></i> {{ $def['name'] }}
                        </a>
                    </li>
                @endforeach
            @endforeach
        </ul>
    </nav>
</aside>
