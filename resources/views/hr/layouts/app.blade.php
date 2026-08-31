<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'WRPlumb HR Panel')</title>

    <link rel="icon" type="image/png" href="{{ asset('image/logo.png') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <link rel="stylesheet" href="{{ asset('css/common.css') }}">
    <link rel="stylesheet" href="{{ asset('css/hr/layout.css') }}">
    <link rel="stylesheet" href="{{ asset('css/hr/alerts.css') }}?v=hr-alerts-01">

    @stack('styles')
</head>
<body>
@php
    $hrUnreadAlerts = auth()->check()
        ? auth()->user()->alerts()->where('is_read', false)->count()
        : 0;
@endphp

<div class="app-shell" id="hrAppShell">
    <aside class="sidebar" id="hrSidebar">
        <button type="button"
                class="brand-wrap hr-sidebar-toggle"
                id="hrSidebarToggle"
                aria-label="Toggle sidebar">
            <img src="{{ asset('image/294539416_407599744767669_1937739510480713048_n.jpg') }}"
                 alt="WRPlumb Logo"
                 class="brand-logo">

            <div class="brand-copy">
                <div class="brand-title">WRPlumb</div>
                <div class="brand-subtitle">HR Panel</div>
            </div>
        </button>

        <div class="sidebar-label">Navigation</div>

        <a href="{{ route('hr.dashboard') }}"
           data-title="Dashboard"
           class="sidebar-link {{ request()->routeIs('hr.dashboard') ? 'active' : '' }}">
            <i class="fas fa-chart-line"></i>
            <span>Dashboard</span>
        </a>

        <a href="{{ route('hr.inspection-reports.index') }}"
            data-title="Inspection Reports"
            class="sidebar-link {{ request()->routeIs('hr.inspection-reports.*') ? 'active' : '' }}">
                <i class="fas fa-clipboard-check"></i>
                <span>Inspection Reports</span>
        </a>



        <a href="{{ route('hr.quotations.index') }}"
           data-title="Quotations"
           class="sidebar-link {{ request()->routeIs('hr.quotations.*') ? 'active' : '' }}">
            <i class="fas fa-file-invoice-dollar"></i>
            <span>Quotations</span>
        </a>

        <a href="{{ route('hr.contracts.index') }}"
           data-title="Contracts"
           class="sidebar-link {{ request()->routeIs('hr.contracts.*') ? 'active' : '' }}">
            <i class="fas fa-file-contract"></i>
            <span>Contracts</span>
        </a>

        <a href="{{ route('hr.invoices.index') }}"
           data-title="Invoices"
           class="sidebar-link {{ request()->routeIs('hr.invoices.*') ? 'active' : '' }}">
            <i class="fas fa-file-invoice"></i>
            <span>Invoices</span>
        </a>

        <a href="{{ route('hr.payments.index') }}"
           data-title="Payments"
           class="sidebar-link {{ request()->routeIs('hr.payments.*') ? 'active' : '' }}">
            <i class="fas fa-credit-card"></i>
            <span>Payments</span>
        </a>

        <a href="{{ route('hr.receipts.index') }}"
           data-title="Receipts"
           class="sidebar-link {{ request()->routeIs('hr.receipts.*') ? 'active' : '' }}">
            <i class="fas fa-receipt"></i>
            <span>Receipts</span>
        </a>

        <a href="{{ route('hr.quotations.archived') }}"
           data-title="Archived"
           class="sidebar-link {{ request()->routeIs('hr.quotations.archived') ? 'active' : '' }}">
            <i class="fas fa-box-archive"></i>
            <span>Archived</span>
        </a>

        <a href="{{ route('hr.reports.index') }}"
           data-title="Reports"
           class="sidebar-link {{ request()->routeIs('hr.reports.*') ? 'active' : '' }}">
            <i class="fas fa-chart-column"></i>
            <span>Reports</span>
        </a>

        <a href="{{ route('hr.support.index') }}"
           data-title="Support Queue"
           class="sidebar-link {{ request()->routeIs('hr.support.*') ? 'active' : '' }}">
            <i class="fas fa-comments"></i>
            <span>Support Queue</span>
        </a>

        <div class="sidebar-footer">
            <form method="POST" action="{{ route('hr.logout') }}">
                @csrf

                <button type="submit" class="logout-btn" title="Logout">
                    <i class="fas fa-right-from-bracket"></i>
                    <span>Logout</span>
                </button>
            </form>
        </div>
    </aside>

    <main class="main">
        <div class="topbar">
            <div>
                <h1 class="topbar-title">
                    @yield('topbar_title', 'HR Panel')
                </h1>

                <p class="topbar-subtitle">
                    @yield('topbar_subtitle', 'Handle routed support concerns and client communication.')
                </p>
            </div>

            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('hr.alerts.index') }}"
                   class="hr-topbar-alert">
                    <i class="fas fa-bell"></i>
                    <span>Alerts</span>

                    @if ($hrUnreadAlerts > 0)
                        <strong class="hr-topbar-alert-count">
                            {{ $hrUnreadAlerts > 99 ? '99+' : $hrUnreadAlerts }}
                        </strong>
                    @endif
                </a>

                <div class="topbar-user">
                    <i class="fas fa-user-tie"></i>
                    <span>{{ auth()->user()->first_name ?? 'HR' }}</span>
                </div>
            </div>
        </div>

        <div class="content">
            @if (session('success'))
                <div id="hrToast" class="hr-toast hr-toast-success">
                    <div class="hr-toast-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>

                    <div class="hr-toast-body">
                        <strong>Success</strong>
                        <span>{{ session('success') }}</span>
                    </div>

                    <button type="button"
                            class="hr-toast-close"
                            onclick="document.getElementById('hrToast')?.remove()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            @endif

            @if ($errors->any())
                <div id="hrToastError" class="hr-toast hr-toast-error">
                    <div class="hr-toast-icon">
                        <i class="fas fa-exclamation-circle"></i>
                    </div>

                    <div class="hr-toast-body">
                        <strong>Action Needed</strong>
                        <span>Please check the form and try again.</span>
                    </div>

                    <button type="button"
                            class="hr-toast-close"
                            onclick="document.getElementById('hrToastError')?.remove()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            @endif

            @yield('content')
        </div>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    ['hrToast', 'hrToastError'].forEach(function (toastId) {
        const toast = document.getElementById(toastId);

        if (toast) {
            setTimeout(function () {
                toast.style.opacity = '0';
                toast.style.transform = 'translateY(-8px) translateX(8px)';
                toast.style.transition = 'all 0.25s ease';

                setTimeout(function () {
                    toast.remove();
                }, 300);
            }, 3500);
        }
    });

    const appShell = document.getElementById('hrAppShell');
    const sidebarToggle = document.getElementById('hrSidebarToggle');

    if (appShell && sidebarToggle) {
        const storedState = localStorage.getItem('hrSidebarCollapsed');

        if (storedState === '1') {
            appShell.classList.add('sidebar-collapsed');
        }

        sidebarToggle.addEventListener('click', function () {
            appShell.classList.toggle('sidebar-collapsed');

            localStorage.setItem(
                'hrSidebarCollapsed',
                appShell.classList.contains('sidebar-collapsed')
                    ? '1'
                    : '0'
            );
        });
    }
});
</script>

@stack('scripts')
</body>
</html>