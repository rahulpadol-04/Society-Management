@extends('layouts.app')
@section('title', 'Notice Board')

@section('page-actions')
    @can('create', App\Models\Notice::class)
        <a href="{{ route('notices.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> New Notice</a>
    @endcan
@endsection

@section('content')
{{-- Category filter tabs --}}
<ul class="nav nav-tabs mb-4">
    <li class="nav-item">
        <a class="nav-link {{ request('category') === null ? 'active' : '' }}" href="{{ route('notices.index') }}">All</a>
    </li>
    @foreach (['notice' => 'Notices', 'announcement' => 'Announcements', 'circular' => 'Circulars', 'event' => 'Events'] as $cat => $label)
        <li class="nav-item">
            <a class="nav-link {{ request('category') === $cat ? 'active' : '' }}"
               href="{{ route('notices.index', ['category' => $cat]) }}">
                {{ $label }}
            </a>
        </li>
    @endforeach
</ul>

{{-- Pinned notices --}}
@if ($pinned->isNotEmpty())
    <h2 class="h6 text-uppercase text-muted mb-2"><i class="bi bi-pin-angle-fill text-warning me-1"></i> Pinned</h2>
    <div class="row g-3 mb-4">
        @foreach ($pinned as $notice)
            @include('notices._card', ['notice' => $notice, 'isPinned' => true])
        @endforeach
    </div>
@endif

{{-- All other notices --}}
@if ($notices->isNotEmpty())
    <h2 class="h6 text-uppercase text-muted mb-2">Recent</h2>
    <div class="row g-3">
        @foreach ($notices as $notice)
            @include('notices._card', ['notice' => $notice, 'isPinned' => false])
        @endforeach
    </div>
@elseif ($pinned->isEmpty())
    <div class="text-center text-muted py-5">
        <i class="bi bi-megaphone fs-2"></i>
        <p class="mt-2 mb-0">No notices yet.</p>
    </div>
@endif
@endsection
