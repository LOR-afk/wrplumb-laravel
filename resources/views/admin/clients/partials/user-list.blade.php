@php
    $roleLabels = [
        'admin' => 'Admin',
        'hr' => 'HR',
        'client' => 'Client',
        'worker' => 'Inspector/Personnel',
        'inspector' => 'Inspector',
    ];
@endphp

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
                    <th>Role</th>
                    <th>Status</th>
                    <th>Email</th>
                    <th>Manage</th>
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
                            <span class="user-role">{{ $roleLabels[$user->role] ?? ucfirst($user->role) }}</span>
                        </td>

                        <td>
                            @if ($user->is_active)
                                <span class="status-badge status-active">Active</span>
                            @else
                                <span class="status-badge status-inactive">Inactive</span>
                            @endif
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
                                            {{ $user->is_active ? 'Deactivate account' : 'Reactivate account' }}
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
                                                    'roleOptions' => [
                                                        'client' => 'Client',
                                                        'hr' => 'HR',
                                                        'worker' => 'Inspector/Personnel',
                                                        'inspector' => 'Inspector',
                                                        'admin' => 'Admin',
                                                    ],
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
                                                <div class="password-label-row">
                                                    <label class="form-label mb-0">New Temporary Password</label>

                                                    <button
                                                        type="button"
                                                        class="btn btn-sm btn-outline-primary generate-password-btn"
                                                        data-password-target="resetPassword{{ $user->id }}"
                                                    >
                                                        <i class="fas fa-wand-magic-sparkles me-1"></i>
                                                        Generate
                                                    </button>
                                                </div>

                                                <div class="password-field-wrap">
                                                    <input
                                                        type="password"
                                                        id="resetPassword{{ $user->id }}"
                                                        name="password"
                                                        class="form-control password-input"
                                                        minlength="16"
                                                        autocomplete="new-password"
                                                        placeholder="Generate or enter a strong password"
                                                        required
                                                    >

                                                    <button
                                                        type="button"
                                                        class="password-toggle"
                                                        data-password-target="resetPassword{{ $user->id }}"
                                                        aria-label="Show password"
                                                        title="Show password"
                                                    >
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                </div>

                                                <div class="password-policy">
                                                    <span><i class="fas fa-shield-halved me-1"></i>At least 16 characters</span>
                                                    <span>Uppercase & lowercase</span>
                                                    <span>Number</span>
                                                    <span>Symbol</span>
                                                </div>
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
                        <td colspan="6">
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

    <div class="p-3 user-pagination">
        {{ $users->links() }}
    </div>
</div>