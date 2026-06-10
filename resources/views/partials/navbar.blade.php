@php $user = auth()->user(); @endphp
<header class="app-navbar navbar navbar-expand bg-body border-bottom px-3 px-lg-4">
    <button class="btn btn-link text-body d-lg-none p-0 me-2" id="sidebarToggle" type="button">
        <i class="bi bi-list fs-3"></i>
    </button>

    <div class="ms-auto d-flex align-items-center gap-2">
        <button class="btn btn-sm btn-outline-secondary border-0 fs-5 py-0" id="themeToggle" type="button" title="Toggle dark / light mode">
            <i class="bi bi-moon-stars-fill" data-theme-icon="dark"></i>
            <i class="bi bi-sun-fill d-none" data-theme-icon="light"></i>
        </button>

        @if ($user->isSuperAdmin() && \Route::has('societies.index'))
            <a href="{{ route('societies.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-building"></i> Societies
            </a>
        @endif

        <div class="dropdown">
            <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle" data-bs-toggle="dropdown">
                <img src="{{ $user->avatar_url }}" alt="avatar" width="34" height="34" class="rounded-circle me-2">
                <span class="d-none d-sm-inline">
                    <span class="fw-semibold">{{ $user->name }}</span><br>
                    <small class="text-muted">{{ $user->roles->pluck('name')->join(', ') ?: 'Member' }}</small>
                </span>
            </a>
            <ul class="dropdown-menu dropdown-menu-end shadow">
                <li><a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="bi bi-person me-2"></i>Profile</a></li>
                <li><a class="dropdown-item" href="{{ route('two-factor.settings') }}"><i class="bi bi-shield-lock me-2"></i>Two-Factor Auth</a></li>
                @if (\Route::has('settings.index') && auth()->user()->can('settings.view'))
                    <li><a class="dropdown-item" href="{{ route('settings.index') }}"><i class="bi bi-gear me-2"></i>Settings</a></li>
                @endif
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form method="POST" action="{{ route('logout') }}">@csrf
                        <button class="dropdown-item text-danger" type="submit"><i class="bi bi-box-arrow-right me-2"></i>Logout</button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</header>
