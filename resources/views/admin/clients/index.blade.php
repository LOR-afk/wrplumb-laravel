@extends('admin.layouts.app')

@section('title', 'User Management - WRPlumb')
@section('topbar_title', 'User Management')
@section('topbar_subtitle', 'Manage client, HR, inspector/personnel, and administrator accounts.')

@push('styles')
<style>
    .user-page {
        display: flex;
        flex-direction: column;
        gap: 14px;
    }

    .user-stats-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 12px;
    }

    .user-stat-card {
        display: flex;
        align-items: center;
        gap: 12px;
        min-height: 68px;
        padding: 13px 15px;
        background: #ffffff;
        border: 1px solid #dbe7f3;
        border-radius: 18px;
        box-shadow: 0 10px 26px rgba(15, 23, 42, 0.045);
    }

    .user-stat-icon {
        width: 40px;
        height: 40px;
        display: grid;
        place-items: center;
        border-radius: 15px;
        background: #e8f5ff;
        color: #0f4c81;
        flex: 0 0 auto;
    }

    .user-stat-label {
        color: #64748b;
        font-size: 0.68rem;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .user-stat-value {
        color: #0f172a;
        font-size: 1.2rem;
        font-weight: 950;
        line-height: 1.1;
    }

    .user-card {
        background: #ffffff;
        border: 1px solid #dbe7f3;
        border-radius: 20px;
        box-shadow: 0 12px 30px rgba(15, 23, 42, 0.055);
        overflow: hidden;
    }

    .user-card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        padding: 14px 18px;
        border-bottom: 1px solid #dbe7f3;
        background: linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
    }

    .user-card-title {
        margin: 0;
        color: #0f172a;
        font-weight: 950;
        font-size: 1rem;
    }

    .user-card-subtitle {
        margin: 3px 0 0;
        color: #64748b;
        font-size: 0.78rem;
    }

    .user-card-body {
        padding: 15px 18px;
    }

    .user-filter-form {
        display: grid;
        grid-template-columns: minmax(260px, 1.6fr) minmax(130px, 0.8fr) minmax(130px, 0.8fr) minmax(150px, 0.8fr) auto auto;
        gap: 10px;
        align-items: end;
    }

    .user-filter-form .form-label {
        font-size: 0.72rem;
        font-weight: 850;
        color: #334155;
        margin-bottom: 5px;
    }

    .user-table {
        margin-bottom: 0;
    }

    .user-table th {
        color: #64748b;
        font-size: 0.68rem;
        font-weight: 950;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        white-space: nowrap;
        padding: 10px 12px;
    }

    .user-table td {
        padding: 12px;
        vertical-align: middle;
    }

    .user-main-name {
        color: #0f172a;
        font-weight: 900;
        line-height: 1.2;
    }

    .user-sub {
        color: #64748b;
        font-size: 0.74rem;
        line-height: 1.25;
        margin-top: 2px;
    }

    .user-contact {
        max-width: 260px;
    }

    .user-email {
        color: #0f172a;
        font-weight: 700;
        font-size: 0.82rem;
        word-break: break-word;
    }

    .user-badge-row {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        align-items: center;
    }

    .role-badge,
    .status-badge,
    .verify-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 5px 9px;
        border-radius: 999px;
        font-size: 0.66rem;
        font-weight: 950;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .role-badge {
        background: #eef6ff;
        color: #0f4c81;
    }

    .status-active,
    .verify-ok {
        background: #ecfdf3;
        color: #15803d;
    }

    .status-inactive,
    .verify-no {
        background: #fee2e2;
        color: #b91c1c;
    }

    .user-actions {
        min-width: 122px;
        text-align: right;
    }

    .user-actions .dropdown-menu {
        min-width: 210px;
        border: 1px solid #dbe7f3;
        border-radius: 16px;
        box-shadow: 0 18px 38px rgba(15, 23, 42, 0.12);
        padding: 8px;
    }

    .user-actions .dropdown-item,
    .user-actions .dropdown-menu button.dropdown-item {
        border-radius: 10px;
        font-size: 0.82rem;
        font-weight: 700;
        padding: 8px 10px;
    }

    .user-actions form {
        margin: 0;
    }

    .modal-helper {
        color: #64748b;
        font-size: 0.78rem;
        line-height: 1.45;
    }

    @media (max-width: 1200px) {
        .user-stats-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .user-filter-form {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .user-table {
            min-width: 900px;
        }
    }

    @media (max-width: 768px) {
        .user-stats-grid,
        .user-filter-form {
            grid-template-columns: 1fr;
        }

        .user-card-header {
            align-items: flex-start;
            flex-direction: column;
        }

        .user-card-header .btn {
            width: 100%;
        }

        .user-table {
            min-width: 860px;
        }
    }
</style>
@endpush

@section('content')
@php
    $roleLabels = [
        'admin' => 'Admin',
        'hr' => 'HR',
        'client' => 'Client',
        'worker' => 'Inspector/Personnel',
        'inspector' => 'Inspector',
    ];

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
            <form method="GET" class="user-filter-form">
                <div>
                    <label class="form-label">Search</label>
                    <input type="text" name="search" class="form-control" placeholder="Name, email, username, phone..." value="{{ request('search') }}">
                </div>

                <div>
                    <label class="form-label">Role</label>
                    <select name="role" class="form-select">
                        <option value="">All roles</option>
                        @foreach ($roleOptions as $value => $label)
                            <option value="{{ $value }}" @selected(request('role') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All statuses</option>
                        <option value="active" @selected(request('status') === 'active')>Active</option>
                        <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
                    </select>
                </div>

                <div>
                    <label class="form-label">Email</label>
                    <select name="verified" class="form-select">
                        <option value="">All emails</option>
                        <option value="verified" @selected(request('verified') === 'verified')>Verified</option>
                        <option value="unverified" @selected(request('verified') === 'unverified')>Unverified</option>
                    </select>
                </div>

                <button class="btn btn-primary" title="Apply filters"><i class="fas fa-magnifying-glass"></i></button>
                <a href="{{ route('admin.clients.index') }}" class="btn btn-outline-secondary">Reset</a>
            </form>
        </div>
    </div>

    <div class="user-card">
        <div class="user-card-header">
            <div>
                <h5 class="user-card-title"><i class="fas fa-users-gear text-primary me-2"></i>System Users</h5>
                <p class="user-card-subtitle">Showing {{ $users->count() }} of {{ $users->total() }} account(s).</p>
            </div>
        </div>

        <div class="user-card-body p-0">
            <div class="table-responsive">
                <table class="table user-table align-middle">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Contact</th>
                            <th>Role / Status</th>
                            <th>Email</th>
                            <th class="text-end">Manage</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $user)
                            <tr>
                                <td>
                                    <div class="user-main-name">{{ $user->name }}</div>
                                    <div class="user-sub">Username: {{ $user->username ?? 'No username' }}</div>
                                    <div class="user-sub">ID #{{ $user->id }}</div>
                                </td>
                                <td>
                                    <div class="user-contact">
                                        <div class="user-email">{{ $user->email }}</div>
                                        <div class="user-sub">{{ $user->phone ?? 'No phone number' }}</div>
                                    </div>
                                </td>
                                <td>
                                    <div class="user-badge-row">
                                        <span class="role-badge">{{ $roleLabels[$user->role] ?? ucfirst($user->role) }}</span>
                                        @if ($user->is_active)
                                            <span class="status-badge status-active">Active</span>
                                        @else
                                            <span class="status-badge status-inactive">Inactive</span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    @if ($user->email_verified_at)
                                        <span class="verify-badge verify-ok">Verified</span>
                                    @else
                                        <span class="verify-badge verify-no">Unverified</span>
                                    @endif
                                </td>
                                <td class="user-actions">
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                            Manage
                                        </button>

                                        <div class="dropdown-menu dropdown-menu-end">
                                            <button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#editUserModal{{ $user->id }}">
                                                <i class="fas fa-pen-to-square me-2 text-primary"></i>Edit details
                                            </button>

                                            @if (!$user->email_verified_at)
                                                <form method="POST" action="{{ route('admin.clients.verify-email', $user) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button class="dropdown-item">
                                                        <i class="fas fa-envelope-circle-check me-2 text-success"></i>Verify email
                                                    </button>
                                                </form>
                                            @endif

                                            <button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#resetPasswordModal{{ $user->id }}">
                                                <i class="fas fa-key me-2 text-secondary"></i>Reset password
                                            </button>

                                            <hr class="dropdown-divider">

                                            <form method="POST" action="{{ route('admin.clients.toggle-status', $user) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button class="dropdown-item {{ $user->is_active ? 'text-danger' : 'text-success' }}" @disabled(auth()->id() === $user->id && $user->is_active)>
                                                    <i class="fas {{ $user->is_active ? 'fa-user-slash' : 'fa-user-check' }} me-2"></i>
                                                    {{ $user->is_active ? 'Deactivate account' : 'Activate account' }}
                                                </button>
                                            </form>
                                        </div>
                                    </div>

                                    <div class="modal fade" id="editUserModal{{ $user->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
                                            <div class="modal-content">
                                                <form method="POST" action="{{ route('admin.clients.update', $user) }}">
                                                    @csrf
                                                    @method('PATCH')

                                                    <div class="modal-header">
                                                        <div>
                                                            <h5 class="modal-title">Edit User Account</h5>
                                                            <p class="text-muted mb-0">{{ $user->name }} • {{ $roleLabels[$user->role] ?? ucfirst($user->role) }}</p>
                                                        </div>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>

                                                    <div class="modal-body text-start">
                                                        @include('admin.clients.partials.user-form', [
                                                            'userRecord' => $user,
                                                            'roleOptions' => $roleOptions,
                                                            'includePassword' => false,
                                                        ])
                                                    </div>

                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                                        <button class="btn btn-primary">Save Changes</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="modal fade" id="resetPasswordModal{{ $user->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content">
                                                <form method="POST" action="{{ route('admin.clients.reset-password', $user) }}">
                                                    @csrf
                                                    @method('PATCH')

                                                    <div class="modal-header">
                                                        <div>
                                                            <h5 class="modal-title">Reset Temporary Password</h5>
                                                            <p class="text-muted mb-0">{{ $user->name }}</p>
                                                        </div>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>

                                                    <div class="modal-body text-start">
                                                        <label class="form-label">New Temporary Password</label>
                                                        <input type="text" name="password" class="form-control" placeholder="Example: TempPass.123" required>
                                                        <p class="modal-helper mt-2">Password must have at least 8 characters with uppercase, lowercase, number, and special character.</p>
                                                    </div>

                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                                        <button class="btn btn-primary">Reset Password</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">
                                    <div class="text-center text-muted py-5">
                                        <i class="fas fa-users-slash fa-2x mb-2 text-primary"></i>
                                        <div class="fw-bold text-dark">No users found</div>
                                        <div>Try adjusting your search or filters.</div>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-3">{{ $users->links() }}</div>
        </div>
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
