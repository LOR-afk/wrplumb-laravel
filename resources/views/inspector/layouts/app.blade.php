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
    <style>
        :root {
            --wr-primary: #1d9bf0;
            --wr-primary-dark: #0f4c81;
            --wr-bg: #f5f8fc;
            --wr-surface: #ffffff;
            --wr-border: #e3ebf3;
            --wr-text: #1f2a37;
            --wr-muted: #6b7280;
            --wr-success: #16a34a;
            --wr-warning: #f59e0b;
            --wr-danger: #ef4444;
            --wr-shadow: 0 10px 30px rgba(15, 23, 42, 0.06);
            --sidebar-width: 240px;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: var(--wr-bg);
            color: var(--wr-text);
        }

        .app-shell {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: var(--sidebar-width);
            background: #ffffff;
            border-right: 1px solid var(--wr-border);
            padding: 22px 18px;
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            overflow-y: auto;
        }

        .brand-wrap {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 8px 22px;
            margin-bottom: 10px;
            border-bottom: 1px solid var(--wr-border);
        }

        .brand-logo {
            width: 46px;
            height: 46px;
            object-fit: cover;
            border-radius: 12px;
            border: 1px solid var(--wr-border);
            background: #fff;
            padding: 4px;
        }

        .brand-title {
            font-size: 1.45rem;
            font-weight: 800;
            color: var(--wr-primary-dark);
            line-height: 1;
            margin-bottom: 4px;
        }

        .brand-subtitle {
            font-size: 0.92rem;
            color: var(--wr-muted);
            font-weight: 600;
        }

        .sidebar-label {
            font-size: 0.78rem;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #94a3b8;
            font-weight: 800;
            margin: 18px 10px 12px;
        }

        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 13px 14px;
            border-radius: 14px;
            color: var(--wr-text);
            text-decoration: none;
            font-weight: 700;
            margin-bottom: 8px;
            transition: all 0.2s ease;
        }

        .sidebar-link i {
            width: 18px;
            text-align: center;
            color: #64748b;
        }

        .sidebar-link:hover {
            background: #eef6ff;
            color: var(--wr-primary-dark);
        }

        .sidebar-link:hover i {
            color: var(--wr-primary);
        }

        .sidebar-link.active {
            background: linear-gradient(90deg, #1d9bf0, #3bb6ff);
            color: #fff;
            box-shadow: 0 10px 25px rgba(29, 155, 240, 0.22);
        }

        .sidebar-link.active i {
            color: #fff;
        }

        .sidebar-footer {
            margin-top: 24px;
            padding-top: 18px;
            border-top: 1px solid var(--wr-border);
        }

        .logout-btn {
            width: 100%;
            border: none;
            border-radius: 14px;
            padding: 13px 16px;
            font-weight: 800;
            background: #fee2e2;
            color: #b91c1c;
            transition: all 0.2s ease;
        }

        .logout-btn:hover {
            background: #fecaca;
        }

        .main {
            flex: 1;
            margin-left: var(--sidebar-width);
            min-width: 0;
        }

        .topbar {
            height: 88px;
            background: rgba(255,255,255,0.9);
            backdrop-filter: blur(8px);
            border-bottom: 1px solid var(--wr-border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 28px;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .topbar-title {
            font-size: 2rem;
            font-weight: 800;
            margin: 0;
            color: #0f172a;
        }

        .topbar-subtitle {
            margin: 4px 0 0;
            color: var(--wr-muted);
            font-weight: 500;
        }

        .topbar-user {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: #f8fbff;
            border: 1px solid var(--wr-border);
            padding: 10px 14px;
            border-radius: 999px;
            font-weight: 700;
            color: var(--wr-primary-dark);
        }

        .topbar-user i {
            color: var(--wr-primary);
        }

        .content {
            padding: 28px;
        }

        .page-header {
            background: linear-gradient(135deg, #ffffff, #f7fbff);
            border: 1px solid var(--wr-border);
            border-radius: 22px;
            padding: 26px 28px;
            box-shadow: var(--wr-shadow);
            margin-bottom: 24px;
        }

        .page-header h1 {
            font-size: 2rem;
            font-weight: 800;
            margin: 0 0 8px;
        }

        .page-header p {
            margin: 0;
            color: var(--wr-muted);
            font-size: 1rem;
        }

        .panel {
            background: var(--wr-surface);
            border: 1px solid var(--wr-border);
            border-radius: 22px;
            box-shadow: var(--wr-shadow);
            overflow: hidden;
        }

        .panel-header {
            padding: 20px 24px;
            border-bottom: 1px solid var(--wr-border);
            background: #fbfdff;
        }

        .panel-header h5 {
            margin: 0;
            font-weight: 800;
        }

        .panel-body {
            padding: 24px;
        }

        .stat-card {
            background: #ffffff;
            border: 1px solid var(--wr-border);
            border-radius: 20px;
            box-shadow: var(--wr-shadow);
            padding: 22px;
            height: 100%;
        }

        .stat-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 14px;
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }

        .stat-icon.blue { background: #eaf4ff; color: #1d4ed8; }
        .stat-icon.green { background: #ecfdf3; color: #16a34a; }
        .stat-icon.orange { background: #fff7ed; color: #f97316; }

        .stat-label {
            color: var(--wr-muted);
            font-weight: 700;
            margin-bottom: 6px;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 800;
            color: #0f172a;
            line-height: 1;
        }

        .stat-helper {
            margin-top: 10px;
            color: var(--wr-muted);
            font-size: 0.92rem;
        }

        .accent-line {
            height: 4px;
            border-radius: 999px;
            margin-top: 16px;
            background: #eaf2fb;
            overflow: hidden;
        }

        .accent-line span {
            display: block;
            height: 100%;
            border-radius: 999px;
        }

        .table {
            margin-bottom: 0;
        }

        .table thead th {
            background: #f8fbff;
            color: #334155;
            font-size: 0.92rem;
            font-weight: 800;
            border-bottom: 1px solid var(--wr-border);
            padding: 16px 14px;
            white-space: nowrap;
        }

        .table tbody td {
            padding: 16px 14px;
            vertical-align: middle;
            border-color: #edf2f7;
        }

        .badge-soft {
            display: inline-flex;
            align-items: center;
            padding: 7px 12px;
            border-radius: 999px;
            font-weight: 700;
            font-size: 0.82rem;
        }

        .badge-soft.blue { background: #eaf4ff; color: #1d4ed8; }
        .badge-soft.green { background: #ecfdf3; color: #15803d; }
        .badge-soft.orange { background: #fff7ed; color: #c2410c; }
        .badge-soft.gray { background: #eef2f7; color: #475569; }

        .btn-primary {
            background: linear-gradient(90deg, #1d9bf0, #3bb6ff);
            border: none;
            border-radius: 12px;
            font-weight: 700;
            padding: 10px 16px;
        }

        .btn-outline-secondary,
        .btn-outline-primary,
        .btn-outline-danger,
        .btn-outline-success {
            border-radius: 12px;
            font-weight: 700;
            padding: 10px 16px;
        }

        .form-control,
        .form-select,
        textarea {
            border-radius: 14px !important;
            border: 1px solid var(--wr-border) !important;
            padding: 11px 14px !important;
            box-shadow: none !important;
        }

        .form-control:focus,
        .form-select:focus,
        textarea:focus {
            border-color: #7dd3fc !important;
            box-shadow: 0 0 0 0.15rem rgba(29, 155, 240, 0.14) !important;
        }

        .alert {
            border: none;
            border-radius: 16px;
            box-shadow: var(--wr-shadow);
        }

        @media (max-width: 991.98px) {
            .sidebar {
                position: static;
                width: 100%;
                border-right: none;
                border-bottom: 1px solid var(--wr-border);
            }

            .main {
                margin-left: 0;
            }

            .app-shell {
                flex-direction: column;
            }

            .topbar {
                height: auto;
                padding: 18px;
                flex-direction: column;
                align-items: flex-start;
                gap: 14px;
            }

            .content {
                padding: 18px;
            }

            .topbar-title,
            .page-header h1 {
                font-size: 1.6rem;
            }
        }
    </style>
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
                    <i class="fas fa-right-from-bracket me-2"></i>Logout
                </button>
            </form>
        </div>
    </aside>

    <main class="main">
        <div class="topbar">
            <div>
                <h1 class="topbar-title">@yield('topbar_title', 'Inspector Panel')</h1>
                <p class="topbar-subtitle">@yield('topbar_subtitle', 'Manage assigned requests and update field availability.')</p>
            </div>

@php
    $clientUnreadAlerts = auth()->check() ? auth()->user()->alerts()->where('is_read', false)->count() : 0;
@endphp

<div class="d-flex align-items-center gap-2">
    <a href="{{ route('client.alerts.index') }}" class="topbar-user text-decoration-none">
        <i class="fas fa-bell"></i>
        <span>Alerts</span>
        @if ($clientUnreadAlerts > 0)
            <span class="badge bg-danger rounded-pill">{{ $clientUnreadAlerts }}</span>
        @endif
    </a>

    <div class="topbar-user">
        <i class="fas fa-user"></i>
        <span>{{ auth()->user()->first_name ?? 'Client' }}</span>
    </div>
</div>
        </div>

        <div class="content">
            @if (session('success'))
                <div class="alert alert-success mb-4">
                    {{ session('success') }}
                </div>
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
</body>
</html>