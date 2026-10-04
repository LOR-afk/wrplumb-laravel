<!DOCTYPE html>
<html lang="en">
<head>
    @php
        $isSupportWidget = request()->boolean('support_widget');
    @endphp

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'WRPlumb Client Panel')</title>

    <link rel="icon" type="image/jpeg" href="{{ asset('image/294539416_407599744767669_1937739510480713048_n.jpg') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <link rel="stylesheet" href="{{ asset('css/common.css') }}">
    <link rel="stylesheet" href="{{ asset('css/client/layout.css') }}?v=client-modern-01">
    <link rel="stylesheet" href="{{ asset('css/client/support-widget.css') }}">
    @stack('styles')
</head>

<body class="{{ $isSupportWidget ? 'support-widget-mode' : '' }}">
@php
    /** @var \App\Models\User|null $currentUser */
    $currentUser = auth()->user();

    $clientDisplayName = $currentUser?->name
        ?: trim(
            ($currentUser?->first_name ?? '') . ' ' .
            ($currentUser?->middle_initial ?? '') . ' ' .
            ($currentUser?->last_name ?? '')
        );

    $clientDisplayName = trim(
        preg_replace('/\s+/', ' ', $clientDisplayName ?: 'Client User')
    );

    $clientFirstName = $currentUser?->first_name
        ?: (explode(' ', $clientDisplayName)[0] ?? 'Client');

    $clientInitials = collect(explode(' ', $clientDisplayName))
        ->filter()
        ->take(2)
        ->map(fn ($part) => strtoupper(substr($part, 0, 1)))
        ->implode('') ?: 'C';

    $clientProfilePhotoUrl = ($currentUser && !empty($currentUser->profile_photo_path))
        ? asset('storage/' . $currentUser->profile_photo_path)
        : null;

    $clientUnreadAlerts = $currentUser
        ? $currentUser->alerts()->where('is_read', false)->count()
        : 0;

    $clientRecentAlerts = $currentUser
        ? $currentUser->alerts()->latest()->limit(5)->get()
        : collect();
@endphp

<div class="app-shell">
    @unless($isSupportWidget)
        <aside class="sidebar" id="clientSidebar" aria-label="Client navigation">
            <a
                href="{{ route('client.dashboard') }}"
                class="sidebar-brand"
                aria-label="WRPlumb Client Dashboard"
            >
                <img
                    src="{{ asset('image/294539416_407599744767669_1937739510480713048_n.jpg') }}"
                    alt="WRPlumb"
                    class="brand-logo"
                >

                <span class="brand-copy">
                    <strong>WRPlumb</strong>
                    <small>Client Portal</small>
                </span>
            </a>

            <nav class="sidebar-nav">
                <a
                    href="{{ route('client.dashboard') }}"
                    class="sidebar-link {{ request()->routeIs('client.dashboard') ? 'active' : '' }}"
                    data-label="Dashboard"
                    title="Dashboard"
                >
                    <span class="sidebar-icon"><i class="fas fa-table-cells-large"></i></span>
                    <span class="sidebar-text">Dashboard</span>
                </a>

                <a
                    href="{{ route('client.requests.create') }}"
                    class="sidebar-link {{ request()->routeIs('client.requests.create') ? 'active' : '' }}"
                    data-label="Book a Service"
                    title="Book a Service"
                >
                    <span class="sidebar-icon"><i class="fas fa-screwdriver-wrench"></i></span>
                    <span class="sidebar-text">Book a Service</span>
                </a>

                <a
                    href="{{ route('client.requests.index') }}"
                    class="sidebar-link {{ request()->routeIs('client.requests.index', 'client.requests.show') ? 'active' : '' }}"
                    data-label="My Requests"
                    title="My Requests"
                >
                    <span class="sidebar-icon"><i class="fas fa-clipboard-list"></i></span>
                    <span class="sidebar-text">My Requests</span>
                </a>

                <a
                    href="{{ route('client.quotations.index') }}"
                    class="sidebar-link {{ request()->routeIs('client.quotations.*') ? 'active' : '' }}"
                    data-label="Quotations"
                    title="Quotations"
                >
                    <span class="sidebar-icon"><i class="fas fa-file-lines"></i></span>
                    <span class="sidebar-text">Quotations</span>
                </a>

                <a
                    href="{{ route('client.contracts.index') }}"
                    class="sidebar-link {{ request()->routeIs('client.contracts.*') ? 'active' : '' }}"
                    data-label="Contracts"
                    title="Contracts"
                >
                    <span class="sidebar-icon"><i class="fas fa-file-signature"></i></span>
                    <span class="sidebar-text">Contracts</span>
                </a>

                <a
                    href="{{ route('client.job-orders.index') }}"
                    class="sidebar-link {{ request()->routeIs('client.job-orders.*') ? 'active' : '' }}"
                    data-label="Job Orders"
                    title="Job Orders"
                >
                    <span class="sidebar-icon"><i class="fas fa-clipboard-check"></i></span>
                    <span class="sidebar-text">Job Orders</span>
                </a>

                <a
                    href="{{ route('client.invoices.index') }}"
                    class="sidebar-link {{ request()->routeIs('client.invoices.*') ? 'active' : '' }}"
                    data-label="Invoices"
                    title="Invoices"
                >
                    <span class="sidebar-icon"><i class="fas fa-file-invoice"></i></span>
                    <span class="sidebar-text">Invoices</span>
                </a>

                <a
                    href="{{ route('client.payments.index') }}"
                    class="sidebar-link {{ request()->routeIs('client.payments.*') ? 'active' : '' }}"
                    data-label="Payments"
                    title="Payments"
                >
                    <span class="sidebar-icon"><i class="fas fa-credit-card"></i></span>
                    <span class="sidebar-text">Payments</span>
                </a>

                <a
                    href="{{ route('client.receipts.index') }}"
                    class="sidebar-link {{ request()->routeIs('client.receipts.*') ? 'active' : '' }}"
                    data-label="Receipts"
                    title="Receipts"
                >
                    <span class="sidebar-icon"><i class="fas fa-receipt"></i></span>
                    <span class="sidebar-text">Receipts</span>
                </a>
            </nav>

        </aside>

        <button
            type="button"
            class="client-sidebar-overlay"
            id="clientSidebarOverlay"
            aria-label="Close navigation"
            tabindex="-1"
        ></button>
    @endunless

    <main class="main">
        @unless($isSupportWidget)
            <header class="topbar">
                <div class="topbar-left">
                    <button
                        type="button"
                        class="topbar-menu-btn"
                        id="clientSidebarToggle"
                        aria-label="Toggle navigation"
                        aria-controls="clientSidebar"
                        aria-expanded="false"
                        title="Toggle sidebar"
                    >
                        <i class="fas fa-bars"></i>
                    </button>

                    <div class="topbar-search-wrap">
                        <i class="fas fa-magnifying-glass"></i>
                        <input
                            type="search"
                            id="clientGlobalSearch"
                            class="topbar-search"
                            placeholder="Search this page..."
                            autocomplete="off"
                            aria-label="Search this page"
                        >
                        <kbd>Ctrl+K</kbd>
                    </div>
                </div>

                <div class="topbar-actions">
                    <div class="client-alert-dropdown">
                        <button
                            type="button"
                            class="topbar-icon-btn client-alert-toggle"
                            id="clientAlertToggle"
                            aria-expanded="false"
                            aria-label="Open notifications"
                            title="Notifications"
                        >
                            <i class="fas fa-bell"></i>

                            @if ($clientUnreadAlerts > 0)
                                <span class="client-alert-count">
                                    {{ $clientUnreadAlerts > 99 ? '99+' : $clientUnreadAlerts }}
                                </span>
                            @endif
                        </button>

                        <div
                            class="client-alert-menu"
                            id="clientAlertMenu"
                            aria-hidden="true"
                        >
                            <div class="client-alert-header">
                                <div>
                                    <strong>Notifications</strong>
                                    <small>
                                        {{ $clientUnreadAlerts }}
                                        {{ $clientUnreadAlerts === 1 ? 'unread notification' : 'unread notifications' }}
                                    </small>
                                </div>

                                @if ($clientUnreadAlerts > 0)
                                    <form
                                        method="POST"
                                        action="{{ route('client.alerts.mark-all-read') }}"
                                    >
                                        @csrf
                                        @method('PATCH')

                                        <button type="submit" class="client-alert-mark-all">
                                            Mark all as read
                                        </button>
                                    </form>
                                @endif
                            </div>

                            <div class="client-alert-list">
                                @forelse ($clientRecentAlerts as $alert)
                                    @php
                                        $alertIcon = match ($alert->type) {
                                            'payment_due' => 'fa-file-invoice-dollar',
                                            'payment' => 'fa-circle-check',
                                            'quotation' => 'fa-file-lines',
                                            'contract' => 'fa-file-signature',
                                            'job_order' => 'fa-clipboard-check',
                                            'request' => 'fa-screwdriver-wrench',
                                            'support' => 'fa-headset',
                                            default => 'fa-bell',
                                        };
                                    @endphp

                                    <form
                                        method="POST"
                                        action="{{ route('client.alerts.mark-read', $alert) }}"
                                        class="client-alert-form"
                                    >
                                        @csrf
                                        @method('PATCH')

                                        <button
                                            type="submit"
                                            class="client-alert-item {{ $alert->is_read ? '' : 'unread' }}"
                                        >
                                            <span class="client-alert-icon alert-type-{{ $alert->type }}">
                                                <i class="fas {{ $alertIcon }}"></i>
                                            </span>

                                            <span class="client-alert-content">
                                                <strong>{{ $alert->title }}</strong>
                                                <span>{{ $alert->message }}</span>
                                                <small>{{ $alert->created_at->diffForHumans() }}</small>
                                            </span>

                                            @unless ($alert->is_read)
                                                <span class="client-alert-dot"></span>
                                            @endunless
                                        </button>
                                    </form>
                                @empty
                                    <div class="client-alert-empty">
                                        <span><i class="fas fa-bell-slash"></i></span>
                                        <strong>No notifications yet</strong>
                                        <p>Payment reminders and account updates will appear here.</p>
                                    </div>
                                @endforelse
                            </div>

                            <a
                                href="{{ route('client.alerts.index') }}"
                                class="client-alert-view-all"
                            >
                                View all notifications
                                <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>

                    <div class="client-profile-dropdown">
                        <button
                            type="button"
                            class="topbar-profile client-profile-toggle"
                            id="clientProfileToggle"
                            aria-expanded="false"
                            aria-label="Open profile menu"
                        >
                            <span class="client-profile-avatar-sm">
                                @if ($clientProfilePhotoUrl)
                                    <img src="{{ $clientProfilePhotoUrl }}" alt="{{ $clientDisplayName }}">
                                @else
                                    {{ $clientInitials }}
                                @endif
                            </span>

                            <span class="client-profile-copy">
                                <strong>{{ $clientFirstName }}</strong>
                                <small>Client</small>
                            </span>

                            <i class="fas fa-chevron-down client-profile-chevron"></i>
                        </button>

                        <div
                            class="client-profile-menu"
                            id="clientProfileMenu"
                            aria-hidden="true"
                        >
                            <div class="client-profile-menu-head">
                                <span class="client-profile-avatar-lg">
                                    @if ($clientProfilePhotoUrl)
                                        <img src="{{ $clientProfilePhotoUrl }}" alt="{{ $clientDisplayName }}">
                                    @else
                                        {{ $clientInitials }}
                                    @endif
                                </span>

                                <div class="client-profile-menu-copy">
                                    <strong>{{ $clientDisplayName }}</strong>
                                    <span>{{ $currentUser?->email ?? 'No email listed' }}</span>
                                    <em>Client</em>
                                </div>
                            </div>

                            <div class="client-profile-menu-actions">
                                <button
                                    type="button"
                                    class="client-profile-item"
                                    data-bs-toggle="modal"
                                    data-bs-target="#clientViewProfileModal"
                                >
                                    <span class="client-profile-item-icon">
                                        <i class="fas fa-user"></i>
                                    </span>
                                    <strong>View Profile</strong>
                                </button>

                                <button
                                    type="button"
                                    class="client-profile-item"
                                    data-bs-toggle="modal"
                                    data-bs-target="#clientAccountSettingsModal"
                                >
                                    <span class="client-profile-item-icon">
                                        <i class="fas fa-sliders"></i>
                                    </span>
                                    <strong>Account Settings</strong>
                                </button>
                            </div>

                            <div class="client-profile-menu-footer">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf

                                    <button type="submit" class="client-profile-item danger">
                                        <span class="client-profile-item-icon">
                                            <i class="fas fa-right-from-bracket"></i>
                                        </span>
                                        <strong>Logout</strong>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </header>
        @endunless

        <div class="content">
            @unless($isSupportWidget)
                <div class="client-page-heading">
                    <h1>@yield('topbar_title', 'Client Dashboard')</h1>

                    <div class="client-page-breadcrumb">
                        <span>WRPlumb</span>
                        <i class="fas fa-chevron-right"></i>
                        <strong>@yield('topbar_title', 'Dashboard')</strong>
                    </div>

                    @hasSection('topbar_subtitle')
                        @if (trim($__env->yieldContent('topbar_subtitle')) !== '')
                            <p>@yield('topbar_subtitle')</p>
                        @endif
                    @endif
                </div>
            @endunless

            @if (session('success') || $errors->any())
                <div class="client-toast-stack" id="clientToastStack">
                    @if (session('success'))
                        <div class="client-toast success">
                            <span class="client-toast-icon">
                                <i class="fas fa-circle-check"></i>
                            </span>
                            <div class="client-toast-copy">
                                <strong>Success</strong>
                                <span>{{ session('success') }}</span>
                            </div>
                            <button type="button" class="client-toast-close" aria-label="Close notification">
                                <i class="fas fa-xmark"></i>
                            </button>
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="client-toast danger">
                            <span class="client-toast-icon">
                                <i class="fas fa-circle-exclamation"></i>
                            </span>
                            <div class="client-toast-copy">
                                <strong>Action Needed</strong>
                                <span>{{ $errors->first() }}</span>
                            </div>
                            <button type="button" class="client-toast-close" aria-label="Close notification">
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

@unless($isSupportWidget)
    <div class="modal fade client-profile-modal" id="clientViewProfileModal" tabindex="-1" aria-labelledby="clientViewProfileModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content client-profile-modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title" id="clientViewProfileModalLabel">My Profile</h5>
                        <p class="client-profile-modal-subtitle">Review your account information.</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div class="client-profile-preview">
                        <span class="client-profile-avatar-xl">
                            @if ($clientProfilePhotoUrl)
                                <img src="{{ $clientProfilePhotoUrl }}" alt="{{ $clientDisplayName }}">
                            @else
                                {{ $clientInitials }}
                            @endif
                        </span>

                        <div>
                            <h4>{{ $clientDisplayName }}</h4>
                            <p>{{ $currentUser?->email ?? 'No email listed' }}</p>
                            <span class="client-profile-role-badge">Client</span>
                        </div>
                    </div>

                    <div class="client-profile-info-grid">
                        <div class="client-profile-info-item">
                            <span>Full Name</span>
                            <strong>{{ $clientDisplayName }}</strong>
                        </div>

                        <div class="client-profile-info-item">
                            <span>Email Address</span>
                            <strong>{{ $currentUser?->email ?? '—' }}</strong>
                        </div>

                        <div class="client-profile-info-item">
                            <span>Phone</span>
                            <strong>{{ $currentUser?->phone ?? '—' }}</strong>
                        </div>

                        <div class="client-profile-info-item">
                            <span>Address</span>
                            <strong>{{ $currentUser?->address ?? '—' }}</strong>
                        </div>

                        <div class="client-profile-info-item">
                            <span>Account Status</span>
                            <strong>{{ $currentUser?->is_active ? 'Active' : 'Inactive' }}</strong>
                        </div>

                        <div class="client-profile-info-item">
                            <span>Member Since</span>
                            <strong>{{ optional($currentUser?->created_at)->format('M d, Y') ?? '—' }}</strong>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                    <button
                        type="button"
                        class="btn btn-primary"
                        data-bs-dismiss="modal"
                        data-bs-toggle="modal"
                        data-bs-target="#clientAccountSettingsModal"
                    >
                        <i class="fas fa-sliders me-1"></i>
                        Account Settings
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade client-profile-modal client-account-settings-modal" id="clientAccountSettingsModal" tabindex="-1" aria-labelledby="clientAccountSettingsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content client-profile-modal-content">
                <form method="POST" action="{{ route('client.profile.update') }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <input type="hidden" name="remove_profile_photo" id="clientRemoveProfilePhoto" value="0">

                    <div class="modal-header">
                        <div>
                            <h5 class="modal-title" id="clientAccountSettingsModalLabel">Account Settings</h5>
                            <p class="client-profile-modal-subtitle">Update your profile information, photo, and password.</p>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <div class="client-settings-photo-panel">
                            <div
                                class="client-settings-photo-preview"
                                id="clientProfilePhotoPreview"
                                data-initials="{{ $clientInitials }}"
                            >
                                @if ($clientProfilePhotoUrl)
                                    <img src="{{ $clientProfilePhotoUrl }}" alt="{{ $clientDisplayName }}">
                                @else
                                    <span>{{ $clientInitials }}</span>
                                @endif
                            </div>

                            <div class="client-settings-photo-copy">
                                <h5>{{ $clientDisplayName }}</h5>
                                <p>{{ $currentUser?->email }}</p>

                                <div class="client-settings-photo-actions">
                                    <label class="btn btn-outline-primary client-settings-upload-btn mb-0">
                                        <i class="fas fa-camera me-1"></i>
                                        Change Profile Picture
                                        <input
                                            type="file"
                                            name="profile_photo"
                                            id="clientProfilePhotoInput"
                                            accept="image/png,image/jpeg,image/webp"
                                            hidden
                                        >
                                    </label>

                                    @if ($clientProfilePhotoUrl)
                                        <button
                                            type="button"
                                            class="btn btn-outline-danger client-settings-remove-btn"
                                            id="clientRemoveProfilePhotoButton"
                                        >
                                            <i class="fas fa-trash me-1"></i>
                                            Remove Photo
                                        </button>
                                    @endif
                                </div>

                                <div class="client-settings-photo-status" id="clientProfilePhotoStatus">
                                    @if ($clientProfilePhotoUrl)
                                        Current profile photo is active.
                                    @else
                                        No uploaded photo yet. Initials are currently used.
                                    @endif
                                </div>

                                <div class="client-settings-help">
                                    Accepted formats: JPG, PNG, or WEBP. Maximum size: 2 MB.
                                </div>
                            </div>
                        </div>

                        <div class="client-settings-grid">
                            <div>
                                <label class="form-label">First Name</label>
                                <input type="text" name="first_name" class="form-control" value="{{ old('first_name', $currentUser?->first_name) }}">
                            </div>

                            <div>
                                <label class="form-label">Middle Initial</label>
                                <input type="text" name="middle_initial" class="form-control" value="{{ old('middle_initial', $currentUser?->middle_initial) }}">
                            </div>

                            <div>
                                <label class="form-label">Last Name</label>
                                <input type="text" name="last_name" class="form-control" value="{{ old('last_name', $currentUser?->last_name) }}">
                            </div>

                            <div>
                                <label class="form-label">Display Name</label>
                                <input type="text" name="name" class="form-control" value="{{ old('name', $currentUser?->name) }}" placeholder="Client User">
                            </div>

                            <div>
                                <label class="form-label">Email Address</label>
                                <input type="email" name="email" class="form-control" value="{{ old('email', $currentUser?->email) }}" required>
                            </div>

                            <div>
                                <label class="form-label">Phone</label>
                                <input type="text" name="phone" class="form-control" value="{{ old('phone', $currentUser?->phone) }}" placeholder="09XXXXXXXXX">
                            </div>
                        </div>

                        <div class="client-settings-password-panel">
                            <div class="client-settings-password-head">
                                <h5><i class="fas fa-lock"></i>Change Password</h5>
                                <p>Leave these fields blank if you do not want to change your password.</p>
                            </div>

                            <div class="client-settings-grid">
                                <div>
                                    <label class="form-label">Current Password</label>
                                    <input type="password" name="current_password" class="form-control" autocomplete="current-password">
                                </div>

                                <div>
                                    <label class="form-label">New Password</label>
                                    <input type="password" name="password" class="form-control" autocomplete="new-password">
                                </div>

                                <div>
                                    <label class="form-label">Confirm New Password</label>
                                    <input type="password" name="password_confirmation" class="form-control" autocomplete="new-password">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endunless

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

@unless($isSupportWidget)
    <div class="client-support-widget">
        <div class="client-support-panel" id="clientSupportPanel" aria-hidden="true">
            <iframe
                id="clientSupportFrame"
                src="{{ route('client.support.index', ['support_widget' => 1]) }}"
                title="Customer Support"
                loading="lazy"
            ></iframe>
        </div>

        <button
            type="button"
            class="client-support-toggle"
            id="clientSupportToggle"
            aria-label="Open customer support"
        >
            <span class="client-support-toggle-inner">
                <i class="fas fa-headset"></i>
            </span>
        </button>
    </div>
@endunless

<script src="{{ asset('js/client/layout.js') }}?v=client-modern-01"></script>
@stack('scripts')
</body>
</html>
