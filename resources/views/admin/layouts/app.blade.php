<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'WRPlumb Admin Panel')</title>

    <link rel="icon" type="image/png" href="{{ asset('image/logo.png') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <link rel="stylesheet" href="{{ asset('css/common.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/layout.css') }}">
    @stack('styles')
</head>
<body>
<div class="admin-sidebar-overlay" id="adminSidebarOverlay"></div>

<div class="app-shell">
    <aside class="sidebar" id="adminSidebar">
        <div class="brand-wrap">
            <button type="button" class="brand-toggle" id="adminSidebarBrandToggle" aria-label="Toggle sidebar">
                <img src="{{ asset('image/294539416_407599744767669_1937739510480713048_n.jpg') }}" alt="WRPlumb Logo" class="brand-logo">
            </button>
            <div class="brand-copy">
                <div class="brand-title">WRPlumb</div>
                <div class="brand-subtitle">Admin Panel</div>
            </div>
        </div>

        <div class="sidebar-label">Navigation</div>

        <a href="{{ route('admin.dashboard') }}" data-title="Dashboard" class="sidebar-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <i class="fas fa-chart-line"></i>
            <span>Dashboard</span>
        </a>

        <a href="{{ route('admin.clients.index') }}" data-title="User Management" class="sidebar-link {{ request()->routeIs('admin.clients.*') ? 'active' : '' }}">
            <i class="fas fa-users-gear"></i>
            <span>User Management</span>
        </a>

        <a href="{{ route('admin.quotations.index') }}" data-title="View Quotations" class="sidebar-link {{ request()->routeIs('admin.quotations.*') ? 'active' : '' }}">
            <i class="fas fa-file-signature"></i>
            <span>View Quotations</span>
        </a>

        <a href="{{ route('admin.job-orders.index') }}" data-title="Job Orders" class="sidebar-link {{ request()->routeIs('admin.job-orders.*') ? 'active' : '' }}">
            <i class="fas fa-clipboard-list"></i>
            <span>Job Orders</span>
        </a>

        <a href="{{ route('admin.warranty-claims.index') }}" data-title="Warranty Claims" class="sidebar-link {{ request()->routeIs('admin.warranty-claims.*') ? 'active' : '' }}">
            <i class="fas fa-shield-alt"></i>
            <span>Warranty Claims</span>
        </a>

        <a href="{{ route('admin.backjobs.index') }}" data-title="Backjobs" class="sidebar-link {{ request()->routeIs('admin.backjobs.*') ? 'active' : '' }}">
            <i class="fas fa-rotate-left"></i>
            <span>Backjobs</span>
        </a>

        <a href="{{ route('admin.inspectors.availability') }}" data-title="Inspector Availability" class="sidebar-link {{ request()->routeIs('admin.inspectors.availability') ? 'active' : '' }}">
            <i class="fas fa-calendar-check"></i>
            <span>Inspector Availability</span>
        </a>

        <a href="{{ route('admin.reports.index') }}" data-title="Reports" class="sidebar-link {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
            <i class="fas fa-chart-column"></i>
            <span>Reports</span>
        </a>

        <a href="{{ route('admin.audit-logs.index') }}" data-title="Audit Logs" class="sidebar-link {{ request()->routeIs('admin.audit-logs.*') ? 'active' : '' }}">
            <i class="fas fa-shield-halved"></i>
            <span>Audit Logs</span>
        </a>

        <a href="{{ route('admin.support.index') }}" data-title="Support Requests" class="sidebar-link {{ request()->routeIs('admin.support.*') ? 'active' : '' }}">
            <i class="fas fa-comments"></i>
            <span>Support Requests</span>
        </a>

        <div class="sidebar-footer">
            <form method="POST" action="{{ route('admin.logout') }}">
                @csrf
                <button type="submit" data-title="Logout" class="logout-btn">
                    <i class="fas fa-right-from-bracket me-2"></i>
                    <span>Logout</span>
                </button>
            </form>
        </div>
    </aside>

    <main class="main">
        <div class="topbar">
            <div class="topbar-left">
                <div class="topbar-heading">
                    <h1 class="topbar-title">@yield('topbar_title', 'Admin Panel')</h1>
                    <p class="topbar-subtitle">@yield('topbar_subtitle', 'Manage system operations and monitor activity.')</p>
                </div>
            </div>

            @php
                /** @var \App\Models\User $currentUser */
                $currentUser = auth()->user();
                $adminUnreadAlerts = $currentUser ? $currentUser->alerts()->where('is_read', false)->count() : 0;
            @endphp

            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('admin.alerts.index') }}" class="topbar-user text-decoration-none">
                    <i class="fas fa-bell"></i>
                    <span>Alerts</span>
                    @if ($adminUnreadAlerts > 0)
                        <span class="badge bg-danger rounded-pill">{{ $adminUnreadAlerts }}</span>
                    @endif
                </a>

                <div class="topbar-user">
                    <i class="fas fa-user-shield"></i>
                    <span>{{ auth()->user()->first_name ?? 'Admin' }}</span>
                </div>
            </div>
        </div>

        <div class="content">
            @if (session('success') || session('info') || $errors->any())
                <div class="wr-toast-stack" id="wrToastStack">
                    @if (session('success'))
                        <div class="wr-toast wr-toast-success">
                            <div class="wr-toast-icon"><i class="fas fa-check-circle"></i></div>
                            <div class="wr-toast-body">
                                <div class="wr-toast-title">Success</div>
                                <div class="wr-toast-message">{{ session('success') }}</div>
                            </div>
                            <button type="button" class="wr-toast-close" onclick="this.closest('.wr-toast').remove()" aria-label="Close notification">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    @endif

                    @if (session('info'))
                        <div class="wr-toast wr-toast-info">
                            <div class="wr-toast-icon"><i class="fas fa-info-circle"></i></div>
                            <div class="wr-toast-body">
                                <div class="wr-toast-title">Notice</div>
                                <div class="wr-toast-message">{{ session('info') }}</div>
                            </div>
                            <button type="button" class="wr-toast-close" onclick="this.closest('.wr-toast').remove()" aria-label="Close notification">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="wr-toast wr-toast-danger">
                            <div class="wr-toast-icon"><i class="fas fa-exclamation-circle"></i></div>
                            <div class="wr-toast-body">
                                <div class="wr-toast-title">Action Needed</div>
                                <div class="wr-toast-message">{{ $errors->first() }}</div>
                            </div>
                            <button type="button" class="wr-toast-close" onclick="this.closest('.wr-toast').remove()" aria-label="Close notification">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    @endif
                </div>
            @endif

            @yield('content')
        </div>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const body = document.body;
        const brandToggle = document.getElementById('adminSidebarBrandToggle');
        const overlay = document.getElementById('adminSidebarOverlay');
        const mobileBreakpoint = 992;

        function isMobile() {
            return window.innerWidth < mobileBreakpoint;
        }

        function closeMobileSidebar() {
            body.classList.remove('admin-sidebar-open');
        }

        function toggleSidebar() {
            if (isMobile()) {
                body.classList.toggle('admin-sidebar-open');
                return;
            }

            body.classList.toggle('admin-sidebar-collapsed');
            localStorage.setItem(
                'wrAdminSidebarCollapsed',
                body.classList.contains('admin-sidebar-collapsed') ? '1' : '0'
            );
        }

        if (localStorage.getItem('wrAdminSidebarCollapsed') === '1' && !isMobile()) {
            body.classList.add('admin-sidebar-collapsed');
        }

        if (brandToggle) {
            brandToggle.addEventListener('click', toggleSidebar);
        }

        if (overlay) {
            overlay.addEventListener('click', closeMobileSidebar);
        }

        document.querySelectorAll('.sidebar-link').forEach(function (link) {
            link.addEventListener('click', function () {
                if (isMobile()) {
                    closeMobileSidebar();
                }
            });
        });

        window.addEventListener('resize', function () {
            if (!isMobile()) {
                body.classList.remove('admin-sidebar-open');

                if (localStorage.getItem('wrAdminSidebarCollapsed') === '1') {
                    body.classList.add('admin-sidebar-collapsed');
                }
            } else {
                body.classList.remove('admin-sidebar-collapsed');
            }
        });

        setTimeout(function () {
            document.querySelectorAll('.wr-toast').forEach(function (toast) {
                toast.style.opacity = '0';
                toast.style.transform = 'translateY(-8px) translateX(12px)';
                toast.style.transition = 'all 0.25s ease';

                setTimeout(function () {
                    toast.remove();
                }, 250);
            });
        }, 4500);
    });
</script>

@stack('scripts')
</body>
</html>
