<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'WRPlumb Inspector Panel')</title>

    <link rel="icon" type="image/png" href="{{ asset('image/logo.png') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/common.css') }}?v=compact-ui-1">
    <link rel="stylesheet" href="{{ asset('css/inspector/layout.css') }}?v=inspector-layout-02">

    @stack('styles')
</head>

<body>
<div class="app-shell">
    <aside class="sidebar">
        <div class="brand-wrap">
            <img src="{{ asset('image/294539416_407599744767669_1937739510480713048_n.jpg') }}" alt="WRPlumb Logo" class="brand-logo">

            <div>
                <div class="brand-title">WRPlumb</div>
                <div class="brand-subtitle">Inspector Panel</div>
            </div>
        </div>

        <div class="sidebar-label">Navigation</div>

        <a href="{{ route('inspector.dashboard') }}" class="sidebar-link {{ request()->routeIs('inspector.dashboard') ? 'active' : '' }}">
            <i class="fas fa-chart-line"></i>
            <span>Dashboard</span>
        </a>

        <a href="{{ route('inspector.quotations.index') }}" class="sidebar-link {{ request()->routeIs('inspector.quotations.*') ? 'active' : '' }}">
            <i class="fas fa-file-signature"></i>
            <span>Assigned Requests</span>
        </a>

        <a href="{{ route('inspector.availability.index') }}" class="sidebar-link {{ request()->routeIs('inspector.availability.*') ? 'active' : '' }}">
            <i class="fas fa-calendar-check"></i>
            <span>Availability</span>
        </a>

        <div class="sidebar-footer">
            <form method="POST" action="{{ route('inspector.logout') }}">
                @csrf

                <button type="submit" class="logout-btn">
                    <i class="fas fa-right-from-bracket me-2"></i>
                    Logout
                </button>
            </form>
        </div>
    </aside>

    <main class="main">
        <div class="topbar">
            <div>
                @hasSection('topbar_title')
                    @if (trim($__env->yieldContent('topbar_title')) !== '')
                        <h1 class="topbar-title">@yield('topbar_title')</h1>
                    @endif
                @endif

                @hasSection('topbar_subtitle')
                    @if (trim($__env->yieldContent('topbar_subtitle')) !== '')
                        <p class="topbar-subtitle">@yield('topbar_subtitle')</p>
                    @endif
                @endif
            </div>

            @php
                $inspectorUnreadAlerts = auth()->check()
                    ? auth()->user()->alerts()->where('is_read', false)->count()
                    : 0;
            @endphp

            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('inspector.alerts.index') }}" class="topbar-user text-decoration-none">
                    <i class="fas fa-bell"></i>
                    <span>Alerts</span>

                    @if ($inspectorUnreadAlerts > 0)
                        <span class="badge bg-danger rounded-pill">{{ $inspectorUnreadAlerts }}</span>
                    @endif
                </a>

                <div class="topbar-user">
                    <i class="fas fa-user"></i>
                    <span>{{ auth()->user()->first_name ?? 'Inspector' }}</span>
                </div>
            </div>
        </div>

        <div class="content">
            @if (session('success'))
                <div class="alert alert-success mb-4">{{ session('success') }}</div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger mb-4">
                    Please check the form and try again.
                </div>
            @endif

            @yield('content')
        </div>
    </main>
</div>

@stack('scripts')
</body>
</html>