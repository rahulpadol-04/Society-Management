@php
    $categoryColors = [
        'notice'       => 'primary',
        'announcement' => 'success',
        'circular'     => 'info',
        'event'        => 'warning',
    ];
    $color = $categoryColors[$notice->category] ?? 'secondary';
@endphp
<div class="col-md-6 col-lg-4">
    <div class="card h-100 shadow-sm border-0 {{ $isPinned ? 'border-start border-4 border-warning' : '' }}">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <span class="badge text-bg-{{ $color }} text-capitalize">{{ $notice->category }}</span>
                <div class="d-flex gap-1">
                    @if ($notice->pinned)
                        <i class="bi bi-pin-angle-fill text-warning" title="Pinned"></i>
                    @endif
                    @if (! $notice->is_published)
                        <span class="badge text-bg-secondary">Draft</span>
                    @endif
                </div>
            </div>

            <h3 class="h6 mb-1">
                <a href="{{ route('notices.show', $notice) }}" class="text-decoration-none text-dark stretched-link">
                    {{ $notice->title }}
                </a>
            </h3>

            <p class="text-muted small mb-2">{{ \Illuminate\Support\Str::limit(strip_tags($notice->body), 80) }}</p>

            @if ($notice->category === 'event' && $notice->event_at)
                <div class="small text-warning fw-semibold">
                    <i class="bi bi-calendar-event me-1"></i>{{ $notice->event_at->format('d M Y H:i') }}
                </div>
            @endif

            @if ($notice->poll)
                <div class="mt-2">
                    <span class="badge text-bg-light border"><i class="bi bi-bar-chart me-1"></i>Poll attached</span>
                </div>
            @endif
        </div>
        <div class="card-footer bg-transparent border-0 small text-muted d-flex justify-content-between">
            <span>{{ $notice->author?->name ?? 'Admin' }}</span>
            <span>{{ $notice->published_at?->format('d M Y') ?? $notice->created_at->format('d M Y') }}</span>
        </div>
    </div>
</div>
