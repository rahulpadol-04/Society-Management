<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="dark">
<head>
    {{-- Apply the saved theme before paint to avoid a flash of the wrong theme. --}}
    <script>(function(){try{var t=localStorage.getItem('co-theme')||'dark';document.documentElement.setAttribute('data-bs-theme',t);}catch(e){}})();</script>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — {{ config('app.name') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}?v={{ @filemtime(public_path('css/app.css')) }}" rel="stylesheet">
    @stack('styles')
</head>
<body>
<div class="app-wrapper">
    @include('partials.sidebar')

    <div class="app-main">
        @include('partials.navbar')

        <main class="app-content p-3 p-lg-4">
            @include('partials.flash')

            <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
                <div>
                    <h1 class="h4 mb-1 page-title">@yield('page-title', View::yieldContent('title', 'Dashboard'))</h1>
                    @hasSection('breadcrumb')
                        <nav aria-label="breadcrumb"><ol class="breadcrumb small mb-0">@yield('breadcrumb')</ol></nav>
                    @endif
                </div>
                <div>@yield('page-actions')</div>
            </div>

            @yield('content')
        </main>

        <footer class="app-footer text-center text-muted small py-3">
            &copy; {{ date('Y') }} {{ config('app.name') }} · Multi-tenant Society Management SaaS
        </footer>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script src="{{ asset('js/app.js') }}?v={{ @filemtime(public_path('js/app.js')) }}"></script>
@stack('scripts')
</body>
</html>
