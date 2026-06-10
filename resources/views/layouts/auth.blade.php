<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Sign in') — {{ config('app.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>
<body class="auth-body d-flex align-items-center justify-content-center min-vh-100 bg-light">
    <div class="auth-card card shadow-sm border-0" style="max-width: @yield('card-width', '420px'); width: 100%;">
        <div class="card-body p-4 p-md-5">
            <div class="text-center mb-4">
                <i class="bi bi-buildings-fill fs-1 text-primary"></i>
                <h1 class="h4 mt-2 mb-0">{{ config('app.name') }}</h1>
                <p class="text-muted small">Society & Apartment Management</p>
            </div>

            @include('partials.flash')

            @yield('content')
        </div>
    </div>
</body>
</html>
