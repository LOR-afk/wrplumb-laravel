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
    <link rel="stylesheet" href="{{ asset('css/hr/layout.css') }}?v=hr-modern-01">
    <link rel="stylesheet" href="{{ asset('css/hr/alerts.css') }}?v=hr-alerts-01">
    <link rel="stylesheet" href="{{ asset('css/hr/profile.css') }}?v=hr-profile-01">

    @stack('styles')
</head>
<body>
@php
    /** @var \App\Models\User|null $hrUser */
    $hrUser = auth()->user();

    $hrUnreadAlerts = $hrUser
        ? $hrUser->alerts()->where('is_read', false)->count()
        : 0;

    $hrDisplayName = $hrUser?->name ?: trim(
        ($hrUser?->first_name ?? '') . ' ' .
        ($hrUser?->middle_initial ?? '') . ' ' .
        ($hrUser?->last_name ?? '')
    );

    $hrDisplayName = trim(
        preg_replace('/\s+/', ' ', $hrDisplayName ?: 'HR User')
    );

    $hrInitials = collect(explode(' ', $hrDisplayName))
        ->filter()
        ->take(2)
        ->map(fn ($part) => strtoupper(substr($part, 0, 1)))
        ->implode('') ?: 'HR';

    $hrProfilePhotoUrl = ($hrUser && !empty($hrUser->profile_photo_path))
        ? asset('storage/' . $hrUser->profile_photo_path)
        : null;

    $hrProfileErrors = $errors->getBag('hrProfile');
@endphp

<div class="app-shell">
    <aside class="sidebar" id="hrSidebar" aria-label="HR navigation">
        <a href="{{ route('hr.dashboard') }}" class="sidebar-brand">
            <img
                src="{{ asset('image/294539416_407599744767669_1937739510480713048_n.jpg') }}"
                alt="WRPlumb"
                class="brand-logo"
            >
            <span class="brand-copy">
                <strong>WRPlumb</strong>
                <small>HR Panel</small>
            </span>
        </a>

        <nav class="sidebar-nav">
            <a href="{{ route('hr.dashboard') }}"
               class="sidebar-link {{ request()->routeIs('hr.dashboard') ? 'active' : '' }}"
               data-label="Dashboard" title="Dashboard">
                <span class="sidebar-icon"><i class="fas fa-table-cells-large"></i></span>
                <span class="sidebar-text">Dashboard</span>
            </a>

            <a href="{{ route('hr.inspection-reports.index') }}"
               class="sidebar-link {{ request()->routeIs('hr.inspection-reports.*') ? 'active' : '' }}"
               data-label="Inspection Reports" title="Inspection Reports">
                <span class="sidebar-icon"><i class="fas fa-clipboard-check"></i></span>
                <span class="sidebar-text">Inspection Reports</span>
            </a>

            <a href="{{ route('hr.quotations.index') }}"
               class="sidebar-link {{ request()->routeIs('hr.quotations.*') && !request()->routeIs('hr.quotations.archived') ? 'active' : '' }}"
               data-label="Quotations" title="Quotations">
                <span class="sidebar-icon"><i class="fas fa-file-invoice-dollar"></i></span>
                <span class="sidebar-text">Quotations</span>
            </a>

            <a href="{{ route('hr.contracts.index') }}"
               class="sidebar-link {{ request()->routeIs('hr.contracts.*') ? 'active' : '' }}"
               data-label="Contracts" title="Contracts">
                <span class="sidebar-icon"><i class="fas fa-file-contract"></i></span>
                <span class="sidebar-text">Contracts</span>
            </a>

            <a href="{{ route('hr.invoices.index') }}"
               class="sidebar-link {{ request()->routeIs('hr.invoices.*') ? 'active' : '' }}"
               data-label="Invoices" title="Invoices">
                <span class="sidebar-icon"><i class="fas fa-file-invoice"></i></span>
                <span class="sidebar-text">Invoices</span>
            </a>

            <a href="{{ route('hr.payments.index') }}"
               class="sidebar-link {{ request()->routeIs('hr.payments.*') ? 'active' : '' }}"
               data-label="Payments" title="Payments">
                <span class="sidebar-icon"><i class="fas fa-credit-card"></i></span>
                <span class="sidebar-text">Payments</span>
            </a>

            <a href="{{ route('hr.receipts.index') }}"
               class="sidebar-link {{ request()->routeIs('hr.receipts.*') ? 'active' : '' }}"
               data-label="Receipts" title="Receipts">
                <span class="sidebar-icon"><i class="fas fa-receipt"></i></span>
                <span class="sidebar-text">Receipts</span>
            </a>

            <a href="{{ route('hr.quotations.archived') }}"
               class="sidebar-link {{ request()->routeIs('hr.quotations.archived') ? 'active' : '' }}"
               data-label="Archived" title="Archived">
                <span class="sidebar-icon"><i class="fas fa-box-archive"></i></span>
                <span class="sidebar-text">Archived</span>
            </a>

            <a href="{{ route('hr.reports.index') }}"
               class="sidebar-link {{ request()->routeIs('hr.reports.*') ? 'active' : '' }}"
               data-label="Reports" title="Reports">
                <span class="sidebar-icon"><i class="fas fa-chart-column"></i></span>
                <span class="sidebar-text">Reports</span>
            </a>

            <a href="{{ route('hr.support.index') }}"
               class="sidebar-link {{ request()->routeIs('hr.support.*') ? 'active' : '' }}"
               data-label="Support Queue" title="Support Queue">
                <span class="sidebar-icon"><i class="fas fa-comments"></i></span>
                <span class="sidebar-text">Support Queue</span>
            </a>
        </nav>

    </aside>

    <button
        type="button"
        class="sidebar-backdrop"
        id="hrSidebarBackdrop"
        aria-label="Close navigation"
        tabindex="-1"
    ></button>

    <main class="main">
        <header class="topbar">
            <div class="topbar-left">
                <button
                    type="button"
                    class="topbar-menu-btn"
                    id="hrSidebarToggle"
                    aria-label="Toggle navigation"
                    aria-controls="hrSidebar"
                    aria-expanded="false"
                >
                    <i class="fas fa-bars"></i>
                </button>

                <div class="topbar-search-wrap">
                    <i class="fas fa-magnifying-glass"></i>
                    <input
                        type="search"
                        id="hrGlobalSearch"
                        class="topbar-search"
                        placeholder="Search this page..."
                        autocomplete="off"
                        aria-label="Search this page"
                    >
                    <kbd>Ctrl+K</kbd>
                </div>
            </div>

            <div class="topbar-actions">
                <a
                    href="{{ route('hr.alerts.index') }}"
                    class="topbar-icon-btn"
                    title="Notifications"
                    aria-label="Notifications"
                >
                    <i class="fas fa-bell"></i>

                    @if ($hrUnreadAlerts > 0)
                        <span class="topbar-alert-count">
                            {{ $hrUnreadAlerts > 99 ? '99+' : $hrUnreadAlerts }}
                        </span>
                    @endif
                </a>

                <div class="dropdown hr-profile-dropdown">
                    <button
                        type="button"
                        class="hr-profile-trigger dropdown-toggle"
                        data-bs-toggle="dropdown"
                        aria-expanded="false"
                    >
                        <span class="hr-profile-avatar-sm">
                            @if ($hrProfilePhotoUrl)
                                <img src="{{ $hrProfilePhotoUrl }}" alt="{{ $hrDisplayName }}">
                            @else
                                {{ $hrInitials }}
                            @endif
                        </span>

                        <span class="hr-profile-trigger-copy">
                            <strong>{{ $hrUser?->first_name ?? 'HR' }}</strong>
                            <small>HR</small>
                        </span>
                    </button>

                    <div class="dropdown-menu dropdown-menu-end hr-profile-menu">
                        <div class="hr-profile-menu-head">
                            <span class="hr-profile-avatar-lg">
                                @if ($hrProfilePhotoUrl)
                                    <img src="{{ $hrProfilePhotoUrl }}" alt="{{ $hrDisplayName }}">
                                @else
                                    {{ $hrInitials }}
                                @endif
                            </span>

                            <div class="hr-profile-menu-copy">
                                <strong>{{ $hrDisplayName }}</strong>
                                <span>{{ $hrUser?->email ?? 'No email listed' }}</span>
                                <em class="hr-profile-role-badge">HR</em>
                            </div>
                        </div>

                        <button
                            type="button"
                            class="hr-profile-item"
                            data-bs-toggle="modal"
                            data-bs-target="#hrViewProfileModal"
                        >
                            <span class="hr-profile-item-icon">
                                <i class="fas fa-user"></i>
                            </span>
                            <span class="hr-profile-item-copy">
                                <strong>View Profile</strong>
                                <small>Review your account information</small>
                            </span>
                        </button>

                        <button
                            type="button"
                            class="hr-profile-item"
                            data-bs-toggle="modal"
                            data-bs-target="#hrAccountSettingsModal"
                        >
                            <span class="hr-profile-item-icon">
                                <i class="fas fa-sliders"></i>
                            </span>
                            <span class="hr-profile-item-copy">
                                <strong>Account Settings</strong>
                                <small>Update profile, photo, and password</small>
                            </span>
                        </button>

                        <div class="dropdown-divider"></div>

                        <form method="POST" action="{{ route('hr.logout') }}">
                            @csrf
                            <button type="submit" class="hr-profile-item danger">
                                <span class="hr-profile-item-icon">
                                    <i class="fas fa-right-from-bracket"></i>
                                </span>
                                <span class="hr-profile-item-copy">
                                    <strong>Logout</strong>
                                    <small>Sign out of the HR Panel</small>
                                </span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        <div class="content">
            @if (session('success'))
                <div class="hr-toast hr-toast-success" role="status" aria-live="polite">
                    <span class="hr-toast-icon"><i class="fas fa-circle-check"></i></span>
                    <div class="hr-toast-copy">
                        <strong>Success</strong>
                        <span>{{ session('success') }}</span>
                    </div>
                    <button type="button" class="hr-toast-close" data-toast-close aria-label="Close notification">
                        <i class="fas fa-xmark"></i>
                    </button>
                </div>
            @endif

            @if ($errors->any() && !$hrProfileErrors->any())
                <div class="hr-toast hr-toast-error" role="alert" aria-live="assertive">
                    <span class="hr-toast-icon"><i class="fas fa-circle-exclamation"></i></span>
                    <div class="hr-toast-copy">
                        <strong>Action Needed</strong>
                        <span>{{ $errors->first() }}</span>
                    </div>
                    <button type="button" class="hr-toast-close" data-toast-close aria-label="Close notification">
                        <i class="fas fa-xmark"></i>
                    </button>
                </div>
            @endif

            @yield('content')
        </div>
    </main>
</div>

<div
    class="modal fade hr-profile-modal"
    id="hrViewProfileModal"
    tabindex="-1"
    aria-labelledby="hrViewProfileModalLabel"
    aria-hidden="true"
>
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title" id="hrViewProfileModalLabel">My Profile</h5>
                    <p class="hr-profile-modal-subtitle">Review your HR account information.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <div class="hr-profile-preview">
                    <span class="hr-profile-avatar-xl">
                        @if ($hrProfilePhotoUrl)
                            <img src="{{ $hrProfilePhotoUrl }}" alt="{{ $hrDisplayName }}">
                        @else
                            {{ $hrInitials }}
                        @endif
                    </span>

                    <div>
                        <h4>{{ $hrDisplayName }}</h4>
                        <p>{{ $hrUser?->email ?? 'No email listed' }}</p>
                        <span class="hr-profile-role-badge">HR</span>
                    </div>
                </div>

                <div class="hr-profile-info-grid">
                    <div class="hr-profile-info-item">
                        <span>Full Name</span>
                        <strong>{{ $hrDisplayName }}</strong>
                    </div>

                    <div class="hr-profile-info-item">
                        <span>Username</span>
                        <strong>{{ $hrUser?->username ?? '—' }}</strong>
                    </div>

                    <div class="hr-profile-info-item">
                        <span>Email Address</span>
                        <strong>{{ $hrUser?->email ?? '—' }}</strong>
                    </div>

                    <div class="hr-profile-info-item">
                        <span>Phone</span>
                        <strong>{{ $hrUser?->phone ?? '—' }}</strong>
                    </div>

                    <div class="hr-profile-info-item full">
                        <span>Address</span>
                        <strong>{{ $hrUser?->address ?? '—' }}</strong>
                    </div>

                    <div class="hr-profile-info-item full">
                        <span>Member Since</span>
                        <strong>{{ optional($hrUser?->created_at)->format('M d, Y') ?? '—' }}</strong>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    Close
                </button>

                <button
                    type="button"
                    class="btn btn-primary"
                    data-bs-dismiss="modal"
                    data-bs-toggle="modal"
                    data-bs-target="#hrAccountSettingsModal"
                >
                    Account Settings
                </button>
            </div>
        </div>
    </div>
</div>

<div
    class="modal fade hr-profile-modal"
    id="hrAccountSettingsModal"
    tabindex="-1"
    aria-labelledby="hrAccountSettingsModalLabel"
    aria-hidden="true"
    data-open-on-error="{{ $hrProfileErrors->any() ? '1' : '0' }}"
>
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <form
                method="POST"
                action="{{ route('hr.profile.update') }}"
                enctype="multipart/form-data"
            >
                @csrf
                @method('PUT')

                <input
                    type="hidden"
                    name="remove_profile_photo"
                    id="hrRemoveProfilePhoto"
                    value="{{ old('remove_profile_photo', 0) }}"
                >

                <div class="modal-header">
                    <div>
                        <h5 class="modal-title" id="hrAccountSettingsModalLabel">Account Settings</h5>
                        <p class="hr-profile-modal-subtitle">
                            Update your profile information, photo, and password.
                        </p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    @if ($hrProfileErrors->any())
                        <div class="alert alert-danger">
                            {{ $hrProfileErrors->first() }}
                        </div>
                    @endif

                    <section class="hr-settings-photo-panel">
                        <div
                            class="hr-settings-photo-preview"
                            id="hrProfilePhotoPreview"
                            data-initials="{{ $hrInitials }}"
                        >
                            @if ($hrProfilePhotoUrl)
                                <img src="{{ $hrProfilePhotoUrl }}" alt="{{ $hrDisplayName }}">
                            @else
                                <span>{{ $hrInitials }}</span>
                            @endif
                        </div>

                        <div class="hr-settings-photo-copy">
                            <h5>{{ $hrDisplayName }}</h5>
                            <p>{{ $hrUser?->email ?? 'No email listed' }}</p>

                            <div class="hr-settings-photo-actions">
                                <label class="btn btn-outline-primary mb-0">
                                    <i class="fas fa-camera me-1"></i>
                                    Change Profile Picture
                                    <input
                                        type="file"
                                        name="profile_photo"
                                        id="hrProfilePhotoInput"
                                        accept="image/png,image/jpeg,image/webp"
                                        hidden
                                    >
                                </label>

                                @if ($hrProfilePhotoUrl)
                                    <button
                                        type="button"
                                        class="btn btn-outline-danger"
                                        id="hrRemoveProfilePhotoButton"
                                    >
                                        <i class="fas fa-trash me-1"></i>
                                        Remove Photo
                                    </button>
                                @endif
                            </div>

                            <div class="hr-settings-photo-status">
                                @if ($hrProfilePhotoUrl)
                                    Current profile photo is active.
                                @else
                                    No uploaded photo yet. Initials are currently used.
                                @endif
                            </div>

                            <div class="hr-settings-help">
                                Accepted formats: JPG, PNG, or WEBP. Maximum size: 2 MB.
                            </div>
                        </div>
                    </section>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">First Name</label>
                            <input
                                type="text"
                                name="first_name"
                                class="form-control"
                                value="{{ old('first_name', $hrUser?->first_name) }}"
                            >
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Middle Initial</label>
                            <input
                                type="text"
                                name="middle_initial"
                                class="form-control"
                                maxlength="10"
                                value="{{ old('middle_initial', $hrUser?->middle_initial) }}"
                            >
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Last Name</label>
                            <input
                                type="text"
                                name="last_name"
                                class="form-control"
                                value="{{ old('last_name', $hrUser?->last_name) }}"
                            >
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Display Name</label>
                            <input
                                type="text"
                                name="name"
                                class="form-control"
                                value="{{ old('name', $hrUser?->name) }}"
                            >
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Email Address</label>
                            <input
                                type="email"
                                name="email"
                                class="form-control"
                                value="{{ old('email', $hrUser?->email) }}"
                                required
                            >
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Phone</label>
                            <input
                                type="text"
                                name="phone"
                                class="form-control"
                                value="{{ old('phone', $hrUser?->phone) }}"
                            >
                        </div>
                    </div>

                    <section class="hr-settings-section">
                        <h5>Change Password</h5>
                        <p>Leave these fields blank if you do not want to change your password.</p>

                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Current Password</label>
                                <input
                                    type="password"
                                    name="current_password"
                                    class="form-control"
                                    autocomplete="current-password"
                                >
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">New Password</label>
                                <input
                                    type="password"
                                    name="password"
                                    class="form-control"
                                    autocomplete="new-password"
                                >
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Confirm New Password</label>
                                <input
                                    type="password"
                                    name="password_confirmation"
                                    class="form-control"
                                    autocomplete="new-password"
                                >
                            </div>
                        </div>
                    </section>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-floppy-disk me-1"></i>
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('js/hr/layout.js') }}?v=hr-modern-01"></script>
<script src="{{ asset('js/hr/profile.js') }}?v=hr-profile-01"></script>

@stack('scripts')
</body>
</html>
