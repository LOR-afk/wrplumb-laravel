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
    <link rel="stylesheet" href="{{ asset('css/inspector/layout.css') }}?v=inspector-modern-02">

    @stack('styles')
</head>

<body>
@php
    $inspectorUser = auth()->user();

    $inspectorUnreadAlerts = $inspectorUser
        ? $inspectorUser->alerts()->where('is_read', false)->count()
        : 0;

    $inspectorDisplayName = $inspectorUser?->name
        ?: trim(
            ($inspectorUser?->first_name ?? '') . ' ' .
            ($inspectorUser?->middle_initial ?? '') . ' ' .
            ($inspectorUser?->last_name ?? '')
        );

    $inspectorDisplayName = trim(
        preg_replace('/\s+/', ' ', $inspectorDisplayName ?: 'Inspector')
    );

    $inspectorInitials = collect(explode(' ', $inspectorDisplayName))
        ->filter()
        ->take(2)
        ->map(fn ($part) => strtoupper(substr($part, 0, 1)))
        ->implode('') ?: 'I';

    $inspectorProfilePhotoUrl = ($inspectorUser && !empty($inspectorUser->profile_photo_path))
        ? asset('storage/' . $inspectorUser->profile_photo_path)
        : null;
@endphp

<div class="app-shell">
    <aside class="sidebar" id="inspectorSidebar" aria-label="Inspector navigation">
        <a href="{{ route('inspector.dashboard') }}" class="sidebar-brand" aria-label="WRPlumb Inspector Dashboard">
            <img
                src="{{ asset('image/294539416_407599744767669_1937739510480713048_n.jpg') }}"
                alt="WRPlumb"
                class="brand-logo"
            >

            <span class="brand-copy">
                <strong>WRPlumb</strong>
                <small>Inspector</small>
            </span>
        </a>

        <nav class="sidebar-nav">
            <a
                href="{{ route('inspector.dashboard') }}"
                class="sidebar-link {{ request()->routeIs('inspector.dashboard') ? 'active' : '' }}"
                data-label="Dashboard"
                title="Dashboard"
            >
                <span class="sidebar-icon"><i class="fas fa-table-cells-large"></i></span>
                <span class="sidebar-text">Dashboard</span>
            </a>

            <a
                href="{{ route('inspector.quotations.index') }}"
                class="sidebar-link {{ request()->routeIs('inspector.quotations.*') ? 'active' : '' }}"
                data-label="Assigned Requests"
                title="Assigned Requests"
            >
                <span class="sidebar-icon"><i class="fas fa-file-signature"></i></span>
                <span class="sidebar-text">Assigned Requests</span>
            </a>

            <a
                href="{{ route('inspector.availability.index') }}"
                class="sidebar-link {{ request()->routeIs('inspector.availability.*') ? 'active' : '' }}"
                data-label="Availability"
                title="Availability"
            >
                <span class="sidebar-icon"><i class="fas fa-calendar-check"></i></span>
                <span class="sidebar-text">Availability</span>
            </a>
        </nav>
    </aside>

    <button
        type="button"
        class="sidebar-backdrop"
        id="sidebarBackdrop"
        aria-label="Close navigation"
        tabindex="-1"
    ></button>

    <main class="main">
        <header class="topbar">
            <div class="topbar-left">
                <button
                    type="button"
                    class="topbar-menu-btn"
                    id="sidebarToggleBtn"
                    aria-label="Toggle navigation"
                    aria-controls="inspectorSidebar"
                    aria-expanded="false"
                    title="Toggle sidebar"
                >
                    <i class="fas fa-bars"></i>
                </button>

                <div class="topbar-search-wrap">
                    <i class="fas fa-magnifying-glass"></i>
                    <input
                        type="search"
                        id="inspectorGlobalSearch"
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
                    href="{{ route('inspector.alerts.index') }}"
                    class="topbar-icon-btn"
                    title="Alerts"
                    aria-label="Alerts"
                >
                    <i class="fas fa-bell"></i>

                    @if ($inspectorUnreadAlerts > 0)
                        <span class="topbar-alert-dot">{{ $inspectorUnreadAlerts }}</span>
                    @endif
                </a>

                <div class="profile-menu-wrap" id="inspectorProfileMenu">
                    <button
                        type="button"
                        class="topbar-profile profile-menu-trigger"
                        id="profileMenuTrigger"
                        aria-haspopup="true"
                        aria-expanded="false"
                    >
                        <span class="profile-avatar">
                            @if ($inspectorProfilePhotoUrl)
                                <img src="{{ $inspectorProfilePhotoUrl }}" alt="{{ $inspectorDisplayName }}">
                            @else
                                {{ $inspectorInitials }}
                            @endif
                        </span>

                        <span class="profile-copy">
                            <strong>{{ $inspectorUser?->first_name ?? 'Inspector' }}</strong>
                            <small>Inspector</small>
                        </span>

                        <i class="fas fa-chevron-down profile-chevron"></i>
                    </button>

                    <div
                        class="profile-dropdown"
                        id="profileDropdown"
                        role="menu"
                        aria-labelledby="profileMenuTrigger"
                    >
                        <div class="profile-dropdown-head">
                            <span class="profile-dropdown-avatar">
                                @if ($inspectorProfilePhotoUrl)
                                    <img src="{{ $inspectorProfilePhotoUrl }}" alt="{{ $inspectorDisplayName }}">
                                @else
                                    {{ $inspectorInitials }}
                                @endif
                            </span>

                            <div>
                                <strong>{{ $inspectorDisplayName }}</strong>
                                <span>{{ $inspectorUser?->email ?? 'No email available' }}</span>
                                <small><i class="fas fa-id-badge"></i> Inspector</small>
                            </div>
                        </div>

                        <div class="profile-dropdown-divider"></div>

                        <button
                            type="button"
                            class="profile-dropdown-item"
                            data-bs-toggle="modal"
                            data-bs-target="#inspectorViewProfileModal"
                            role="menuitem"
                        >
                            <span><i class="fas fa-user"></i></span>
                            <div>
                                <strong>View Profile</strong>
                                <small>Review your account information</small>
                            </div>
                        </button>

                        <button
                            type="button"
                            class="profile-dropdown-item"
                            data-bs-toggle="modal"
                            data-bs-target="#inspectorAccountSettingsModal"
                            role="menuitem"
                        >
                            <span><i class="fas fa-sliders"></i></span>
                            <div>
                                <strong>Account Settings</strong>
                                <small>Update profile, photo, and password</small>
                            </div>
                        </button>

                        <div class="profile-dropdown-divider"></div>

                        <form method="POST" action="{{ route('inspector.logout') }}">
                            @csrf

                            <button
                                type="submit"
                                class="profile-dropdown-item profile-logout"
                                role="menuitem"
                            >
                                <span><i class="fas fa-right-from-bracket"></i></span>
                                <div>
                                    <strong>Logout</strong>
                                    <small>Sign out of the Inspector Panel</small>
                                </div>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        <div class="content">
            @hasSection('topbar_title')
                @if (trim($__env->yieldContent('topbar_title')) !== '')
                    <div class="page-heading">
                        <h1>@yield('topbar_title')</h1>

                        <div class="page-heading-meta">
                            <span>WRPlumb</span>
                            <i class="fas fa-chevron-right"></i>
                            <strong>@yield('topbar_title')</strong>
                        </div>

                        @hasSection('topbar_subtitle')
                            @if (trim($__env->yieldContent('topbar_subtitle')) !== '')
                                <p>@yield('topbar_subtitle')</p>
                            @endif
                        @endif
                    </div>
                @endif
            @endif

            @if (session('success'))
                <div
                    class="inspector-toast inspector-toast-success"
                    id="inspectorSuccessToast"
                    role="status"
                    aria-live="polite"
                >
                    <span class="inspector-toast-icon">
                        <i class="fas fa-circle-check"></i>
                    </span>

                    <div class="inspector-toast-copy">
                        <strong>Success</strong>
                        <span>{{ session('success') }}</span>
                    </div>

                    <button
                        type="button"
                        class="inspector-toast-close"
                        data-toast-close
                        aria-label="Close notification"
                    >
                        <i class="fas fa-xmark"></i>
                    </button>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger mb-3">
                    Please check the form and try again.
                </div>
            @endif

            @yield('content')
        </div>
    </main>
</div>

<!-- Inspector View Profile -->

<div

    class="modal fade inspector-profile-modal"

    id="inspectorViewProfileModal"

    tabindex="-1"

    aria-labelledby="inspectorViewProfileModalLabel"

    aria-hidden="true"

>

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content inspector-profile-modal-content">

            <div class="modal-header">

                <div>

                    <h5 class="modal-title" id="inspectorViewProfileModalLabel">My Profile</h5>

                    <p class="inspector-profile-modal-subtitle">Review your Inspector account information.</p>

                </div>

                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>

            </div>

            <div class="modal-body">

                <div class="inspector-profile-preview">

                    <span class="inspector-profile-avatar-xl">

                        @if ($inspectorProfilePhotoUrl)

                            <img src="{{ $inspectorProfilePhotoUrl }}" alt="{{ $inspectorDisplayName }}">

                        @else

                            {{ $inspectorInitials }}

                        @endif

                    </span>

                    <div>

                        <h4>{{ $inspectorDisplayName }}</h4>

                        <p>{{ $inspectorUser?->email ?? 'No email listed' }}</p>

                        <span class="inspector-profile-role-badge">Inspector</span>

                    </div>

                </div>

                <div class="inspector-profile-info-grid">

                    <div class="inspector-profile-info-item">

                        <span>Full Name</span>

                        <strong>{{ $inspectorDisplayName }}</strong>

                    </div>

                    <div class="inspector-profile-info-item">

                        <span>Username</span>

                        <strong>{{ $inspectorUser?->username ?? '—' }}</strong>

                    </div>

                    <div class="inspector-profile-info-item">

                        <span>Email Address</span>

                        <strong>{{ $inspectorUser?->email ?? '—' }}</strong>

                    </div>

                    <div class="inspector-profile-info-item">

                        <span>Phone</span>

                        <strong>{{ $inspectorUser?->phone ?? '—' }}</strong>

                    </div>

                    <div class="inspector-profile-info-item inspector-profile-info-wide">

                        <span>Address</span>

                        <strong>{{ $inspectorUser?->address ?? '—' }}</strong>

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

                    data-bs-target="#inspectorAccountSettingsModal"

                >

                    <i class="fas fa-sliders me-1"></i>

                    Account Settings

                </button>

            </div>

        </div>

    </div>

</div>

<!-- Inspector Account Settings -->

<div

    class="modal fade inspector-profile-modal inspector-account-settings-modal"

    id="inspectorAccountSettingsModal"

    tabindex="-1"

    aria-labelledby="inspectorAccountSettingsModalLabel"

    aria-hidden="true"

>

    <div class="modal-dialog modal-dialog-centered modal-xl">

        <div class="modal-content inspector-profile-modal-content">

            <form

                method="POST"

                action="{{ route('inspector.profile.update') }}"

                enctype="multipart/form-data"

                id="inspectorAccountSettingsForm"

            >

                @csrf

                @method('PUT')

                <input type="hidden" name="_profile_form" value="inspector_account">

                <input

                    type="hidden"

                    name="remove_profile_photo"

                    id="inspectorRemoveProfilePhoto"

                    value="{{ old('remove_profile_photo', 0) }}"

                >

                <div class="modal-header">

                    <div>

                        <h5 class="modal-title" id="inspectorAccountSettingsModalLabel">Account Settings</h5>

                        <p class="inspector-profile-modal-subtitle">

                            Update your profile information, photo, and password.

                        </p>

                    </div>

                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>

                </div>

                <div class="modal-body">

                    <section class="inspector-settings-photo-panel">

                        <div class="inspector-settings-photo-preview" id="inspectorSettingsPhotoPreview" data-initials="{{ $inspectorInitials }}">

                            @if ($inspectorProfilePhotoUrl)

                                <img

                                    src="{{ $inspectorProfilePhotoUrl }}"

                                    alt="{{ $inspectorDisplayName }}"

                                    id="inspectorSettingsPhotoImage"

                                >

                            @else

                                <span id="inspectorSettingsPhotoInitials">{{ $inspectorInitials }}</span>

                            @endif

                        </div>

                        <div class="inspector-settings-photo-copy">

                            <h5>{{ $inspectorDisplayName }}</h5>

                            <p>{{ $inspectorUser?->email ?? 'No email listed' }}</p>

                            <div class="inspector-settings-photo-actions">

                                <label

                                    for="inspectorProfilePhoto"

                                    class="btn btn-outline-primary inspector-settings-upload-btn"

                                >

                                    <i class="fas fa-camera me-1"></i>

                                    Change Profile Picture

                                </label>

                                <input

                                    type="file"

                                    name="profile_photo"

                                    id="inspectorProfilePhoto"

                                    class="d-none"

                                    accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"

                                >

                                <button

                                    type="button"

                                    class="btn inspector-settings-remove-btn"

                                    id="inspectorRemovePhotoBtn"

                                >

                                    <i class="fas fa-trash me-1"></i>

                                    Remove Photo

                                </button>

                            </div>

                            <div class="inspector-settings-photo-status" id="inspectorPhotoStatus">

                                @if ($inspectorProfilePhotoUrl)

                                    Current profile photo is active.

                                @else

                                    No profile photo uploaded. Initials are being used.

                                @endif

                            </div>

                            <div class="inspector-settings-help">

                                Accepted formats: JPG, PNG, or WEBP. Maximum size: 2 MB.

                            </div>

                        </div>

                    </section>

                    <div class="inspector-settings-grid">

                        <div>

                            <label class="form-label" for="inspectorFirstName">First Name</label>

                            <input

                                type="text"

                                class="form-control @error('first_name') is-invalid @enderror"

                                id="inspectorFirstName"

                                name="first_name"

                                value="{{ old('first_name', $inspectorUser?->first_name) }}"

                            >

                            @error('first_name')

                                <div class="invalid-feedback">{{ $message }}</div>

                            @enderror

                        </div>

                        <div>

                            <label class="form-label" for="inspectorMiddleInitial">Middle Initial</label>

                            <input

                                type="text"

                                class="form-control @error('middle_initial') is-invalid @enderror"

                                id="inspectorMiddleInitial"

                                name="middle_initial"

                                maxlength="10"

                                value="{{ old('middle_initial', $inspectorUser?->middle_initial) }}"

                            >

                            @error('middle_initial')

                                <div class="invalid-feedback">{{ $message }}</div>

                            @enderror

                        </div>

                        <div>

                            <label class="form-label" for="inspectorLastName">Last Name</label>

                            <input

                                type="text"

                                class="form-control @error('last_name') is-invalid @enderror"

                                id="inspectorLastName"

                                name="last_name"

                                value="{{ old('last_name', $inspectorUser?->last_name) }}"

                            >

                            @error('last_name')

                                <div class="invalid-feedback">{{ $message }}</div>

                            @enderror

                        </div>

                        <div>

                            <label class="form-label" for="inspectorDisplayName">Display Name</label>

                            <input

                                type="text"

                                class="form-control @error('name') is-invalid @enderror"

                                id="inspectorDisplayName"

                                name="name"

                                value="{{ old('name', $inspectorUser?->name ?: $inspectorDisplayName) }}"

                            >

                            @error('name')

                                <div class="invalid-feedback">{{ $message }}</div>

                            @enderror

                        </div>

                        <div>

                            <label class="form-label" for="inspectorEmail">Email Address</label>

                            <input

                                type="email"

                                class="form-control @error('email') is-invalid @enderror"

                                id="inspectorEmail"

                                name="email"

                                value="{{ old('email', $inspectorUser?->email) }}"

                                required

                            >

                            @error('email')

                                <div class="invalid-feedback">{{ $message }}</div>

                            @enderror

                        </div>

                        <div>

                            <label class="form-label" for="inspectorPhone">Phone</label>

                            <input

                                type="text"

                                class="form-control @error('phone') is-invalid @enderror"

                                id="inspectorPhone"

                                name="phone"

                                value="{{ old('phone', $inspectorUser?->phone) }}"

                            >

                            @error('phone')

                                <div class="invalid-feedback">{{ $message }}</div>

                            @enderror

                        </div>

                    </div>

                    <section class="inspector-settings-password-panel">

                        <div class="inspector-settings-password-head">

                            <h5>

                                <i class="fas fa-lock"></i>

                                Change Password

                            </h5>

                            <p>Leave these fields blank if you do not want to change your password.</p>

                        </div>

                        <div class="inspector-settings-grid">

                            <div>

                                <label class="form-label" for="inspectorCurrentPassword">Current Password</label>

                                <input

                                    type="password"

                                    class="form-control @error('current_password') is-invalid @enderror"

                                    id="inspectorCurrentPassword"

                                    name="current_password"

                                    autocomplete="current-password"

                                >

                                @error('current_password')

                                    <div class="invalid-feedback">{{ $message }}</div>

                                @enderror

                            </div>

                            <div>

                                <label class="form-label" for="inspectorNewPassword">New Password</label>

                                <input

                                    type="password"

                                    class="form-control @error('password') is-invalid @enderror"

                                    id="inspectorNewPassword"

                                    name="password"

                                    autocomplete="new-password"

                                >

                                @error('password')

                                    <div class="invalid-feedback">{{ $message }}</div>

                                @enderror

                            </div>

                            <div>

                                <label class="form-label" for="inspectorPasswordConfirmation">Confirm New Password</label>

                                <input

                                    type="password"

                                    class="form-control"

                                    id="inspectorPasswordConfirmation"

                                    name="password_confirmation"

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
<script src="{{ asset('js/inspector/layout.js') }}?v=inspector-modern-02"></script>

@if ($errors->any() && old('_profile_form') === 'inspector_account')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const settingsModal = document.getElementById('inspectorAccountSettingsModal');

    if (settingsModal && window.bootstrap) {
        bootstrap.Modal.getOrCreateInstance(settingsModal).show();
    }
});
</script>
@endif

@stack('scripts')
</body>
</html>
