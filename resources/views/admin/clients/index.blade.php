@extends('admin.layouts.app')

@section('title', 'User Management - WRPlumb')
@section('topbar_title', 'User Management')
@section('topbar_subtitle', 'Manage client, HR, inspector/personnel, and administrator accounts.')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin/user-management.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/user-form.css') }}">
@endpush

@section('content')
@php
    $roleOptions = [
        'client' => 'Client',
        'hr' => 'HR',
        'worker' => 'Inspector/Personnel',
        'inspector' => 'Inspector',
        'admin' => 'Admin',
    ];
@endphp

<div class="user-page">
    <div class="user-stats-grid">
        <div class="user-stat-card">
            <div class="user-stat-icon"><i class="fas fa-users"></i></div>
            <div>
                <div class="user-stat-label">Total Users</div>
                <div class="user-stat-value">{{ $stats['total'] }}</div>
            </div>
        </div>

        <div class="user-stat-card">
            <div class="user-stat-icon"><i class="fas fa-user-check"></i></div>
            <div>
                <div class="user-stat-label">Active</div>
                <div class="user-stat-value">{{ $stats['active'] }}</div>
            </div>
        </div>

        <div class="user-stat-card">
            <div class="user-stat-icon"><i class="fas fa-user-slash"></i></div>
            <div>
                <div class="user-stat-label">Inactive</div>
                <div class="user-stat-value">{{ $stats['inactive'] }}</div>
            </div>
        </div>

        <div class="user-stat-card">
            <div class="user-stat-icon"><i class="fas fa-envelope-circle-check"></i></div>
            <div>
                <div class="user-stat-label">Unverified Email</div>
                <div class="user-stat-value">{{ $stats['unverified'] }}</div>
            </div>
        </div>
    </div>

    <div class="user-card">
        <div class="user-card-header">
            <div>
                <h5 class="user-card-title"><i class="fas fa-filter text-primary me-2"></i>Filter Users</h5>
                <p class="user-card-subtitle">Search and narrow down accounts by role, status, or email verification.</p>
            </div>

            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createUserModal">
                <i class="fas fa-user-plus me-1"></i>Create User
            </button>
        </div>

        <div class="user-card-body">
            <form method="GET" action="{{ route('admin.clients.index') }}" class="user-filter-form" id="userFilterForm">
                <div>
                    <label class="form-label" for="userSearch">Search</label>
                    <input
                        type="text"
                        id="userSearch"
                        name="search"
                        class="form-control"
                        placeholder="Name, email, username, phone..."
                        value="{{ request('search') }}"
                        autocomplete="off"
                    >
                </div>

                <div>
                    <label class="form-label" for="roleFilter">Role</label>
                    <select name="role" id="roleFilter" class="form-select">
                        <option value="">All roles</option>
                        @foreach ($roleOptions as $value => $label)
                            <option value="{{ $value }}" @selected(request('role') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="form-label" for="statusFilter">Status</label>
                    <select name="status" id="statusFilter" class="form-select">
                        <option value="">All statuses</option>
                        <option value="active" @selected(request('status') === 'active')>Active</option>
                        <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
                    </select>
                </div>

                <div>
                    <label class="form-label" for="verifiedFilter">Email</label>
                    <select name="verified" id="verifiedFilter" class="form-select">
                        <option value="">All emails</option>
                        <option value="verified" @selected(request('verified') === 'verified')>Verified</option>
                        <option value="unverified" @selected(request('verified') === 'unverified')>Unverified</option>
                    </select>
                </div>

                <button type="button" class="btn btn-outline-secondary" id="resetFilters">Reset</button>
            </form>
        </div>
    </div>

    <div class="user-card" id="userListContainer">
        @include('admin.clients.partials.user-list', ['users' => $users])
    </div>
</div>

<div class="modal fade" id="createUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.clients.store') }}">
                @csrf

                <div class="modal-header">
                    <div>
                        <h5 class="modal-title">Create User Account</h5>
                        <p class="text-muted mb-0">Create an account for client, HR, inspector/personnel, or admin.</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    @include('admin.clients.partials.user-form', [
                        'userRecord' => null,
                        'roleOptions' => $roleOptions,
                        'includePassword' => true,
                    ])

                    <div class="row g-2 mt-2">
                        <div class="col-md-6">
                            <div class="form-check">
                                <input type="hidden" name="is_active" value="0">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="createActive" checked>
                                <label class="form-check-label" for="createActive">Set account as active</label>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-check">
                                <input type="hidden" name="email_verified" value="0">
                                <input class="form-check-input" type="checkbox" name="email_verified" value="1" id="createVerified" checked>
                                <label class="form-check-label" for="createVerified">Mark email as verified</label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-primary">Create User</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('userFilterForm');
    const searchInput = document.getElementById('userSearch');
    const roleFilter = document.getElementById('roleFilter');
    const statusFilter = document.getElementById('statusFilter');
    const verifiedFilter = document.getElementById('verifiedFilter');
    const resetButton = document.getElementById('resetFilters');
    const userList = document.getElementById('userListContainer');

    let searchTimer = null;
    let activeRequest = null;

    function buildUrl(pageUrl = null) {
        const url = new URL(pageUrl || form.action, window.location.origin);

        if (!pageUrl) {
            const formData = new FormData(form);
            url.search = '';

            for (const [key, value] of formData.entries()) {
                const cleanValue = String(value).trim();

                if (cleanValue !== '') {
                    url.searchParams.set(key, cleanValue);
                }
            }
        }

        return url;
    }

    async function loadUsers(pageUrl = null) {
        const url = buildUrl(pageUrl);

        if (activeRequest) {
            activeRequest.abort();
        }

        activeRequest = new AbortController();
        userList.classList.add('is-loading');

        try {
            const response = await fetch(url.toString(), {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                signal: activeRequest.signal
            });

            if (!response.ok) {
                throw new Error('Unable to load users.');
            }

            const data = await response.json();
            userList.innerHTML = data.html;
            window.history.replaceState({}, '', url.toString());
        } catch (error) {
            if (error.name !== 'AbortError') {
                console.error(error);
            }
        } finally {
            userList.classList.remove('is-loading');
        }
    }

    function secureRandomIndex(max) {
        const random = new Uint32Array(1);
        const maxValid = Math.floor(0x100000000 / max) * max;

        do {
            crypto.getRandomValues(random);
        } while (random[0] >= maxValid);

        return random[0] % max;
    }

    function randomCharacter(characters) {
        return characters[secureRandomIndex(characters.length)];
    }

    function shuffleSecure(characters) {
        const result = [...characters];

        for (let i = result.length - 1; i > 0; i--) {
            const j = secureRandomIndex(i + 1);
            [result[i], result[j]] = [result[j], result[i]];
        }

        return result.join('');
    }

    function generateStrongPassword(length = 16) {
        const uppercase = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
        const lowercase = 'abcdefghijkmnopqrstuvwxyz';
        const numbers = '23456789';
        const symbols = '!@#$%^&*()-_=+?';
        const all = uppercase + lowercase + numbers + symbols;

        const password = [
            randomCharacter(uppercase),
            randomCharacter(lowercase),
            randomCharacter(numbers),
            randomCharacter(symbols)
        ];

        while (password.length < length) {
            password.push(randomCharacter(all));
        }

        return shuffleSecure(password);
    }

    document.addEventListener('click', function (event) {
        const generateButton = event.target.closest('.generate-password-btn');

        if (generateButton) {
            const targetId = generateButton.dataset.passwordTarget;
            const input = document.getElementById(targetId);

            if (input) {
                input.value = generateStrongPassword(16);
                input.type = 'text';
                input.dispatchEvent(new Event('input', { bubbles: true }));

                const toggle = document.querySelector('.password-toggle[data-password-target="' + targetId + '"]');

                if (toggle) {
                    const icon = toggle.querySelector('i');
                    if (icon) {
                        icon.classList.remove('fa-eye');
                        icon.classList.add('fa-eye-slash');
                    }
                    toggle.setAttribute('aria-label', 'Hide password');
                    toggle.setAttribute('title', 'Hide password');
                }

                input.focus();
                input.select();
            }

            return;
        }

        const toggleButton = event.target.closest('.password-toggle');

        if (toggleButton) {
            const targetId = toggleButton.dataset.passwordTarget;
            const input = document.getElementById(targetId);

            if (!input) {
                return;
            }

            const showPassword = input.type === 'password';
            input.type = showPassword ? 'text' : 'password';

            const icon = toggleButton.querySelector('i');

            if (icon) {
                icon.classList.toggle('fa-eye', !showPassword);
                icon.classList.toggle('fa-eye-slash', showPassword);
            }

            toggleButton.setAttribute('aria-label', showPassword ? 'Hide password' : 'Show password');
            toggleButton.setAttribute('title', showPassword ? 'Hide password' : 'Show password');

            return;
        }

        const paginationLink = event.target.closest('#userListContainer .pagination a');

        if (paginationLink) {
            event.preventDefault();
            loadUsers(paginationLink.href);
        }
    });

    if (!form || !searchInput || !userList) {
        return;
    }

    searchInput.addEventListener('input', function () {
        clearTimeout(searchTimer);

        searchTimer = setTimeout(function () {
            loadUsers();
        }, 350);
    });

    [roleFilter, statusFilter, verifiedFilter].forEach(function (filter) {
        filter.addEventListener('change', function () {
            loadUsers();
        });
    });

    resetButton.addEventListener('click', function () {
        clearTimeout(searchTimer);

        searchInput.value = '';
        roleFilter.value = '';
        statusFilter.value = '';
        verifiedFilter.value = '';

        loadUsers();
        searchInput.focus();
    });
});
</script>
@endpush