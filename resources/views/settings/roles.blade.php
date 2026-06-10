@extends('layouts.app')
@section('title', 'Roles & Permissions')
@section('page-title', 'Settings')

@section('content')
<div class="row g-3">
    <div class="col-lg-3">@include('settings._nav')</div>

    <div class="col-lg-9">
        <div class="card"><div class="card-body">
            <h2 class="h5 mb-1">Roles &amp; Permissions</h2>
            <p class="text-muted small">Permissions are dynamic — adjust what each society role can do. System roles are provisioned per society and fully configurable.</p>

            <div class="table-responsive">
                <table class="table align-middle">
                    <thead><tr><th>Role</th><th>Scope</th><th>Level</th><th>Permissions</th><th>Users</th><th></th></tr></thead>
                    <tbody>
                    @foreach ($roles as $role)
                        <tr>
                            <td>
                                <span class="fw-semibold">{{ $role->name }}</span>
                                @if($role->is_system)<span class="badge text-bg-light ms-1">system</span>@endif
                                <div class="small text-muted">{{ $role->description }}</div>
                            </td>
                            <td><span class="badge text-bg-{{ $role->scope === 'global' ? 'dark' : 'secondary' }}">{{ $role->scope }}</span></td>
                            <td>{{ $role->level }}</td>
                            <td>{{ $role->permissions_count }}</td>
                            <td>{{ $role->users_count }}</td>
                            <td class="text-end">
                                @can('permissions', App\Models\Setting::class)
                                    @if ($role->society_id === current_society_id())
                                        <a href="{{ route('settings.roles.edit', $role) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i> Edit</a>
                                    @else
                                        <span class="text-muted small">Platform role</span>
                                    @endif
                                @endcan
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div></div>
    </div>
</div>
@endsection
