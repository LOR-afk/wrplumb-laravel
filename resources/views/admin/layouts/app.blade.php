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

    <link rel="stylesheet" href="{{ asset('css/admin/layout.css') }}?v=admin-modern-01">

    @stack('styles')

</head>



<body>

    @php

        /** @var \App\Models\User|null $currentUser */

        $currentUser = auth()->user();

        $adminUnreadAlerts = $currentUser ? $currentUser->alerts()->where('is_read', false)->count() : 0;

        $displayName =
            $currentUser?->name ?:
            trim(
                ($currentUser?->first_name ?? '') .
                    ' ' .
                    ($currentUser?->middle_initial ?? '') .
                    ' ' .
                    ($currentUser?->last_name ?? ''),
            );

        $displayName = trim(preg_replace('/\s+/', ' ', $displayName ?: 'Admin User'));

        $displayFirstName = $currentUser?->first_name ?: explode(' ', $displayName)[0] ?? 'Admin';

        $roleLabel = $currentUser?->role ? ucfirst(str_replace('_', ' ', $currentUser->role)) : 'Administrator';

        $initials =
            collect(explode(' ', $displayName))
                ->filter()

                ->take(2)

                ->map(fn($part) => strtoupper(substr($part, 0, 1)))

                ->implode('') ?:
            'A';

        $profilePhotoUrl =
            $currentUser && !empty($currentUser->profile_photo_path)
                ? asset('storage/' . $currentUser->profile_photo_path)
                : null;
    @endphp



    <div class="app-shell">

        <aside class="sidebar" id="adminSidebar" aria-label="Admin navigation">

            <a href="{{ route('admin.dashboard') }}" class="sidebar-brand">

                <img src="{{ asset('image/294539416_407599744767669_1937739510480713048_n.jpg') }}" alt="WRPlumb"
                    class="brand-logo">



                <span class="brand-copy">

                    <strong>WRPlumb</strong>

                    <small>Admin Panel</small>

                </span>

            </a>



            <nav class="sidebar-nav">

                <a href="{{ route('admin.dashboard') }}"
                    class="sidebar-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"
                    data-label="Dashboard" title="Dashboard">

                    <span class="sidebar-icon"><i class="fas fa-table-cells-large"></i></span>

                    <span class="sidebar-text">Dashboard</span>

                </a>



                <a href="{{ route('admin.clients.index') }}"
                    class="sidebar-link {{ request()->routeIs('admin.clients.*') ? 'active' : '' }}"
                    data-label="User Management" title="User Management">

                    <span class="sidebar-icon"><i class="fas fa-users-gear"></i></span>

                    <span class="sidebar-text">User Management</span>

                </a>



                <a href="{{ route('admin.quotations.index') }}"
                    class="sidebar-link {{ request()->routeIs('admin.quotations.*') && !request()->routeIs('admin.quotations.archived') ? 'active' : '' }}"
                    data-label="Service Requests" title="Service Requests">

                    <span class="sidebar-icon"><i class="fas fa-list-check"></i></span>

                    <span class="sidebar-text">Service Requests</span>

                </a>



                <a href="{{ route('admin.generated-quotations.index') }}"
                    class="sidebar-link {{ request()->routeIs('admin.generated-quotations.*') ? 'active' : '' }}"
                    data-label="Generated Quotations" title="Generated Quotations">

                    <span class="sidebar-icon"><i class="fas fa-file-invoice-dollar"></i></span>

                    <span class="sidebar-text">Generated Quotations</span>

                </a>



                <a href="{{ route('admin.quotations.archived') }}"
                    class="sidebar-link {{ request()->routeIs('admin.quotations.archived') || request()->routeIs('admin.archives.*') ? 'active' : '' }}"
                    data-label="Archived Records" title="Archived Records">

                    <span class="sidebar-icon"><i class="fas fa-box-archive"></i></span>

                    <span class="sidebar-text">Archived Records</span>

                </a>



                <a href="{{ route('admin.job-orders.index') }}"
                    class="sidebar-link {{ request()->routeIs('admin.job-orders.*') ? 'active' : '' }}"
                    data-label="Job Orders" title="Job Orders">

                    <span class="sidebar-icon"><i class="fas fa-clipboard-list"></i></span>

                    <span class="sidebar-text">Job Orders</span>

                </a>



                <a href="{{ route('admin.warranty-claims.index') }}"
                    class="sidebar-link {{ request()->routeIs('admin.warranty-claims.*') ? 'active' : '' }}"
                    data-label="Warranty Claims" title="Warranty Claims">

                    <span class="sidebar-icon"><i class="fas fa-shield-halved"></i></span>

                    <span class="sidebar-text">Warranty Claims</span>

                </a>



                <a href="{{ route('admin.backjobs.index') }}"
                    class="sidebar-link {{ request()->routeIs('admin.backjobs.*') ? 'active' : '' }}"
                    data-label="Backjobs" title="Backjobs">

                    <span class="sidebar-icon"><i class="fas fa-rotate-left"></i></span>

                    <span class="sidebar-text">Backjobs</span>

                </a>



                <a href="{{ route('admin.inspectors.availability') }}"
                    class="sidebar-link {{ request()->routeIs('admin.inspectors.availability') ? 'active' : '' }}"
                    data-label="Inspector Availability" title="Inspector Availability">

                    <span class="sidebar-icon"><i class="fas fa-calendar-check"></i></span>

                    <span class="sidebar-text">Inspector Availability</span>

                </a>



                <a href="{{ route('admin.reports.index') }}"
                    class="sidebar-link {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}"
                    data-label="Reports" title="Reports">

                    <span class="sidebar-icon"><i class="fas fa-chart-column"></i></span>

                    <span class="sidebar-text">Reports</span>

                </a>



                <a href="{{ route('admin.audit-logs.index') }}"
                    class="sidebar-link {{ request()->routeIs('admin.audit-logs.*') ? 'active' : '' }}"
                    data-label="Audit Logs" title="Audit Logs">

                    <span class="sidebar-icon"><i class="fas fa-shield-halved"></i></span>

                    <span class="sidebar-text">Audit Logs</span>

                </a>



                <a href="{{ route('admin.support.index') }}"
                    class="sidebar-link {{ request()->routeIs('admin.support.*') ? 'active' : '' }}"
                    data-label="Support Requests" title="Support Requests">

                    <span class="sidebar-icon"><i class="fas fa-comments"></i></span>

                    <span class="sidebar-text">Support Requests</span>

                </a>

            </nav>

        </aside>



        <button type="button" class="sidebar-backdrop" id="adminSidebarBackdrop" aria-label="Close navigation"
            tabindex="-1"></button>



        <main class="main">

            <header class="topbar">

                <div class="topbar-left">

                    <button type="button" class="topbar-menu-btn" id="adminSidebarToggle"
                        aria-label="Toggle navigation" aria-controls="adminSidebar" aria-expanded="false"
                        title="Toggle sidebar">

                        <i class="fas fa-bars"></i>

                    </button>



                    <div class="topbar-search-wrap">

                        <i class="fas fa-magnifying-glass"></i>

                        <input type="search" id="adminGlobalSearch" class="topbar-search"
                            placeholder="Search this page..." autocomplete="off" aria-label="Search this page">

                        <kbd>Ctrl+K</kbd>

                    </div>

                </div>



                <div class="topbar-actions">

                    <a href="{{ route('admin.alerts.index') }}" class="topbar-icon-btn" title="Notifications"
                        aria-label="Notifications">

                        <i class="fas fa-bell"></i>



                        @if ($adminUnreadAlerts > 0)
                            <span class="topbar-alert-count">

                                {{ $adminUnreadAlerts > 99 ? '99+' : $adminUnreadAlerts }}

                            </span>
                        @endif

                    </a>



                    <div class="dropdown admin-profile-dropdown">

                        <button class="topbar-profile admin-profile-trigger dropdown-toggle" type="button"
                            data-bs-toggle="dropdown" aria-expanded="false">

                            <span class="admin-avatar-sm">

                                @if ($profilePhotoUrl)
                                    <img src="{{ $profilePhotoUrl }}" alt="{{ $displayName }}">
                                @else
                                    {{ $initials }}
                                @endif

                            </span>



                            <span class="admin-profile-copy">

                                <strong>{{ $displayFirstName }}</strong>

                                <small>Admin</small>

                            </span>

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



                            <button type="button" class="dropdown-item admin-profile-item" data-bs-toggle="modal"
                                data-bs-target="#adminViewProfileModal">

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

            </header>



            <div class="content">

                @if (session('success') || session('info') || $errors->any())

                    <div class="wr-toast-stack">

                        @if (session('success'))
                            <div class="wr-toast wr-toast-success">

                                <span class="wr-toast-icon"><i class="fas fa-circle-check"></i></span>

                                <div class="wr-toast-copy">

                                    <strong>Success</strong>

                                    <span>{{ session('success') }}</span>

                                </div>

                                <button type="button" class="wr-toast-close" data-toast-close
                                    aria-label="Close notification">

                                    <i class="fas fa-xmark"></i>

                                </button>

                            </div>
                        @endif



                        @if (session('info'))
                            <div class="wr-toast wr-toast-info">

                                <span class="wr-toast-icon"><i class="fas fa-circle-info"></i></span>

                                <div class="wr-toast-copy">

                                    <strong>Notice</strong>

                                    <span>{{ session('info') }}</span>

                                </div>

                                <button type="button" class="wr-toast-close" data-toast-close
                                    aria-label="Close notification">

                                    <i class="fas fa-xmark"></i>

                                </button>

                            </div>
                        @endif



                        @if ($errors->any())
                            <div class="wr-toast wr-toast-danger">

                                <span class="wr-toast-icon"><i class="fas fa-circle-exclamation"></i></span>

                                <div class="wr-toast-copy">

                                    <strong>Action Needed</strong>

                                    <span>{{ $errors->first() }}</span>

                                </div>

                                <button type="button" class="wr-toast-close" data-toast-close
                                    aria-label="Close notification">

                                    <i class="fas fa-xmark"></i>

                                </button>

                            </div>
                        @endif

                    </div>

                @endif



                @yield('content')

            </div>

        </main>

    </div>



    <div class="modal fade admin-profile-modal" id="adminViewProfileModal" tabindex="-1"
        aria-labelledby="adminViewProfileModalLabel" aria-hidden="true">

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

    <script src="{{ asset('js/admin/layout.js') }}?v=admin-modern-01"></script>

    @stack('scripts')

</body>

</html>
