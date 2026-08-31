<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'WRPlumb Admin Panel')</title>

    <link rel="icon" type="image/png" href="{{ asset('image/logo.png') }}">

    {{-- Apply the saved desktop sidebar state BEFORE the page paints.
         This prevents the sidebar from flashing open on every navigation. --}}
    <script>
        (function () {
            if (
                window.innerWidth >= 992 &&
                localStorage.getItem('wrAdminSidebarCollapsed') === '1'
            ) {
                document.documentElement.classList.add('wr-admin-sidebar-collapsed-initial');
            }

            document.documentElement.classList.add('wr-sidebar-preload');
        })();
    </script>

    <style>
        /* Pre-paint bridge: mirrors body.admin-sidebar-collapsed before <body> exists. */
        @media (min-width: 992px) {
            html.wr-admin-sidebar-collapsed-initial .sidebar {
                width: 74px !important;
            }

            html.wr-admin-sidebar-collapsed-initial .sidebar .brand-copy,
            html.wr-admin-sidebar-collapsed-initial .sidebar .sidebar-label,
            html.wr-admin-sidebar-collapsed-initial .sidebar .sidebar-link span {
                display: none !important;
            }

            html.wr-admin-sidebar-collapsed-initial .main {
                margin-left: 74px !important;
            }
        }

        /* Do not animate the initial state restoration. */
        html.wr-sidebar-preload .sidebar,
        html.wr-sidebar-preload .main {
            transition: none !important;
        }
    </style>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <link rel="stylesheet" href="{{ asset('css/common.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/layout.css') }}">
    @stack('styles')
</head>
<body>
<script>
    (function () {
        const isDesktop = window.innerWidth >= 992;
        const collapsed = localStorage.getItem('wrAdminSidebarCollapsed') === '1';

        if (isDesktop && collapsed) {
            document.body.classList.add('admin-sidebar-collapsed');
        }
    })();
</script>
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

        <a href="{{ route('admin.quotations.index') }}"
           data-title="Service Requests"
           class="sidebar-link {{ request()->routeIs('admin.quotations.*') && !request()->routeIs('admin.quotations.archived') ? 'active' : '' }}">
            <i class="fas fa-list-check"></i>
            <span>Service Requests</span>
        </a>

        <a href="{{ route('admin.generated-quotations.index') }}"
           data-title="Generated Quotations"
           class="sidebar-link {{ request()->routeIs('admin.generated-quotations.*') ? 'active' : '' }}">
            <i class="fas fa-file-invoice-dollar"></i>
            <span>Generated Quotations</span>
        </a>

        <a href="{{ route('admin.quotations.archived') }}" data-title="Archived Records" class="sidebar-link {{ request()->routeIs('admin.quotations.archived') || request()->routeIs('admin.archives.*') ? 'active' : '' }}">
            <i class="fas fa-box-archive"></i>
            <span>Archived Records</span>
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
    </aside>

    <main class="main">
        @php
            $currentUser = auth()->user();
            $adminUnreadAlerts = $currentUser ? $currentUser->alerts()->where('is_read', false)->count() : 0;
            $displayName = $currentUser?->name
                ?: trim(($currentUser?->first_name ?? '') . ' ' . ($currentUser?->last_name ?? ''));
            $displayName = $displayName ?: 'Admin User';
            $displayFirstName = $currentUser?->first_name ?: explode(' ', $displayName)[0] ?? 'Admin';
            $roleLabel = $currentUser?->role ? ucfirst(str_replace('_', ' ', $currentUser->role)) : 'Administrator';
            $initials = collect(explode(' ', $displayName))->filter()->take(2)->map(fn ($part) => strtoupper(substr($part, 0, 1)))->implode('') ?: 'A';
            $profilePhotoUrl = ($currentUser && !empty($currentUser->profile_photo_path))
                ? asset('storage/' . $currentUser->profile_photo_path)
                : null;
        @endphp

        <div class="topbar">
            <div class="topbar-left">
                <div class="topbar-heading">
                    <h1 class="topbar-title">@yield('topbar_title', 'Admin Panel')</h1>
                    <p class="topbar-subtitle">@yield('topbar_subtitle', 'Manage system operations and monitor activity.')</p>
                </div>
            </div>

            <div class="topbar-actions">
                <a href="{{ route('admin.alerts.index') }}" class="topbar-user text-decoration-none">
                    <i class="fas fa-bell"></i>
                    <span>Alerts</span>
                    @if ($adminUnreadAlerts > 0)
                        <span class="badge bg-danger rounded-pill">{{ $adminUnreadAlerts }}</span>
                    @endif
                </a>

                <div class="dropdown admin-profile-dropdown">
                    <button class="topbar-user admin-profile-trigger dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <span class="admin-avatar-sm">
                            @if ($profilePhotoUrl)
                                <img src="{{ $profilePhotoUrl }}" alt="{{ $displayName }}">
                            @else
                                {{ $initials }}
                            @endif
                        </span>
                        <span>{{ $displayFirstName }}</span>
                    </button>

                    <div class="dropdown-menu dropdown-menu-end admin-profile-menu">
                        <div class="admin-profile-menu-head">
                            <span class="admin-avatar-lg">
                                @if ($profilePhotoUrl)
                                    <img src="{{ $profilePhotoUrl }}" alt="{{ $displayName }}">
                                @else
                                    {{ $initials }}
                                @endif
                            </span>
                            <div class="admin-profile-menu-copy">
                                <strong>{{ $displayName }}</strong>
                                <span>{{ $currentUser?->email ?? 'No email listed' }}</span>
                                <em>{{ $roleLabel }}</em>
                            </div>
                        </div>

                        <button type="button" class="dropdown-item admin-profile-item" data-bs-toggle="modal" data-bs-target="#adminViewProfileModal">
                            <i class="fas fa-user"></i>
                            <span>View Profile</span>
                        </button>

                        <a href="{{ route('admin.profile.edit') }}" class="dropdown-item admin-profile-item">
                            <i class="fas fa-sliders"></i>
                            <span>Account Settings</span>
                        </a>

                        <div class="dropdown-divider"></div>

                        <form method="POST" action="{{ route('admin.logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item admin-profile-item danger">
                                <i class="fas fa-right-from-bracket"></i>
                                <span>Logout</span>
                            </button>
                        </form>
                    </div>
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

<div class="modal fade admin-profile-modal" id="adminViewProfileModal" tabindex="-1" aria-labelledby="adminViewProfileModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content admin-profile-modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title" id="adminViewProfileModalLabel">View Profile</h5>
                    <p class="modal-subtitle mb-0">Review your account information.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <div class="admin-profile-preview">
                    <span class="admin-avatar-xl">
                        @if ($profilePhotoUrl)
                            <img src="{{ $profilePhotoUrl }}" alt="{{ $displayName }}">
                        @else
                            {{ $initials }}
                        @endif
                    </span>
                    <div>
                        <h4>{{ $displayName }}</h4>
                        <p>{{ $currentUser?->email ?? 'No email listed' }}</p>
                        <span class="admin-role-badge">{{ $roleLabel }}</span>
                    </div>
                </div>

                <div class="admin-profile-info-grid">
                    <div class="admin-profile-info-item">
                        <span>Full Name</span>
                        <strong>{{ $displayName }}</strong>
                    </div>
                    <div class="admin-profile-info-item">
                        <span>Email Address</span>
                        <strong>{{ $currentUser?->email ?? '—' }}</strong>
                    </div>
                    <div class="admin-profile-info-item">
                        <span>Phone</span>
                        <strong>{{ $currentUser?->phone ?? '—' }}</strong>
                    </div>
                    <div class="admin-profile-info-item">
                        <span>Role</span>
                        <strong>{{ $roleLabel }}</strong>
                    </div>
                    <div class="admin-profile-info-item">
                        <span>Status</span>
                        <strong>{{ $currentUser?->is_active ? 'Active' : 'Inactive' }}</strong>
                    </div>
                    <div class="admin-profile-info-item">
                        <span>Joined</span>
                        <strong>{{ optional($currentUser?->created_at)->format('M d, Y') ?? '—' }}</strong>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                <a href="{{ route('admin.profile.edit') }}" class="btn btn-primary">
                    <i class="fas fa-sliders me-1"></i> Account Settings
                </a>
            </div>
        </div>
    </div>
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

        if (brandToggle) {
            brandToggle.addEventListener('click', toggleSidebar);
        }

        if (overlay) {
            overlay.addEventListener('click', closeMobileSidebar);
        }

        // The correct sidebar state is now applied; re-enable normal transitions.
        document.documentElement.classList.remove(
            'wr-sidebar-preload',
            'wr-admin-sidebar-collapsed-initial'
        );

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