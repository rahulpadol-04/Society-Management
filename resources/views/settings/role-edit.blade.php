@extends('layouts.app')
@section('title', 'Edit Role — '.$role->name)
@section('page-title', 'Settings')

@section('content')
<div class="row g-3">
    <div class="col-lg-3">@include('settings._nav')</div>

    <div class="col-lg-9">
        <form method="POST" action="{{ route('settings.roles.update', $role) }}">
            @csrf @method('PUT')
            <div class="card mb-3"><div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="h5 mb-0">{{ $role->name }} — Permissions</h2>
                    <p class="text-muted small mb-0">Tick the actions this role is allowed to perform.</p>
                </div>
                <div>
                    <a href="{{ route('settings.roles') }}" class="btn btn-link">Cancel</a>
                    <button class="btn btn-primary"><i class="bi bi-save me-1"></i> Save Permissions</button>
                </div>
            </div></div>

            @foreach ($modules as $group => $mods)
                <div class="card mb-3"><div class="card-body">
                    <h3 class="h6 text-uppercase text-muted">{{ $group }}</h3>
                    @foreach ($mods as $module => $def)
                        <div class="mb-3">
                            <div class="fw-semibold mb-1"><i class="bi {{ $def['icon'] ?? 'bi-dot' }} me-1 text-primary"></i>{{ $def['name'] }}</div>
                            <div class="d-flex flex-wrap gap-3">
                                @foreach ($def['abilities'] as $ability)
                                    @php $slug = $module.'.'.$ability; @endphp
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $slug }}" id="p_{{ $slug }}" @checked(in_array($slug, $granted, true))>
                                        <label class="form-check-label text-capitalize" for="p_{{ $slug }}">{{ str_replace('_',' ',$ability) }}</label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div></div>
            @endforeach

            <button class="btn btn-primary"><i class="bi bi-save me-1"></i> Save Permissions</button>
        </form>
    </div>
</div>
@endsection
