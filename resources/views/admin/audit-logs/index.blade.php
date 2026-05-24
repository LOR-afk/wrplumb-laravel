@extends('admin.layouts.app')

@section('title', 'Audit Logs - WRPlumb')
@section('topbar_title', 'Audit Logs')
@section('topbar_subtitle', 'Track important system actions, security events, and administrator activity.')

@push('styles')
<style>
    .audit-page {
        display: flex;
        flex-direction: column;
        gap: 14px;
    }

    .audit-stats-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 12px;
    }

    .audit-stat-card {
        display: flex;
        align-items: center;
        gap: 12px;
        min-height: 68px;
        padding: 14px 16px;
        background: #ffffff;
        border: 1px solid #dbe7f3;
        border-radius: 18px;
        box-shadow: 0 10px 26px rgba(15, 23, 42, 0.045);
    }

    .audit-stat-icon {
        width: 42px;
        height: 42px;
        display: grid;
        place-items: center;
        flex: 0 0 auto;
        border-radius: 15px;
        background: #e8f5ff;
        color: #0f4c81;
        font-size: 1rem;
    }

    .audit-stat-label {
        color: #64748b;
        font-size: 0.68rem;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .audit-stat-value {
        color: #0f172a;
        font-size: 1.25rem;
        font-weight: 950;
        line-height: 1.1;
    }

    .audit-card {
        background: #ffffff;
        border: 1px solid #dbe7f3;
        border-radius: 20px;
        box-shadow: 0 12px 30px rgba(15, 23, 42, 0.055);
        overflow: hidden;
    }

    .audit-card-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 12px;
        padding: 15px 18px;
        border-bottom: 1px solid #dbe7f3;
        background: linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
    }

    .audit-card-title {
        margin: 0;
        color: #0f172a;
        font-weight: 950;
        font-size: 1.02rem;
    }

    .audit-card-subtitle {
        margin: 3px 0 0;
        color: #64748b;
        font-size: 0.8rem;
    }

    .audit-card-body {
        padding: 16px 18px;
    }

    .audit-filter-form {
        display: grid;
        grid-template-columns: minmax(220px, 1.3fr) minmax(140px, 0.8fr) minmax(120px, 0.7fr) minmax(150px, 0.9fr) minmax(120px, 0.6fr) minmax(120px, 0.6fr) auto auto;
        gap: 10px;
        align-items: end;
    }

    .audit-filter-form .form-label {
        color: #334155;
        font-size: 0.74rem;
        font-weight: 850;
        margin-bottom: 5px;
    }

    .audit-filter-form .form-control,
    .audit-filter-form .form-select {
        min-height: 38px;
        border-radius: 12px !important;
    }

    .audit-table {
        margin-bottom: 0;
        vertical-align: middle;
    }

    .audit-table th {
        padding: 10px 12px;
        background: #f8fbff;
        color: #64748b;
        font-size: 0.7rem;
        font-weight: 950;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        white-space: nowrap;
    }

    .audit-table td {
        padding: 11px 12px;
        color: #334155;
        font-size: 0.82rem;
    }

    .audit-action {
        color: #0f172a;
        font-weight: 900;
    }

    .audit-description {
        margin-top: 3px;
        color: #64748b;
        font-size: 0.75rem;
        line-height: 1.35;
    }

    .audit-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 58px;
        padding: 5px 8px;
        border-radius: 999px;
        background: #eef6ff;
        color: #0f4c81;
        font-size: 0.68rem;
        font-weight: 950;
        text-transform: uppercase;
    }

    .audit-user {
        color: #0f172a;
        font-weight: 900;
    }

    .audit-user-sub {
        color: #64748b;
        font-size: 0.74rem;
    }

    .audit-json {
        max-height: 210px;
        overflow: auto;
        margin: 0;
        padding: 12px;
        border-radius: 14px;
        background: #0f172a;
        color: #dbeafe;
        font-size: 0.78rem;
    }

    .audit-empty {
        display: grid;
        place-items: center;
        min-height: 210px;
        padding: 36px 16px;
        text-align: center;
        color: #64748b;
    }

    .audit-empty-icon {
        width: 58px;
        height: 58px;
        display: grid;
        place-items: center;
        margin-bottom: 10px;
        border-radius: 20px;
        background: #e8f5ff;
        color: #0f4c81;
        font-size: 1.55rem;
    }

    .audit-empty-title {
        color: #0f172a;
        font-weight: 950;
        margin-bottom: 3px;
    }

    .audit-human-summary {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 10px;
        margin-bottom: 16px;
    }

    .audit-human-box {
        padding: 12px 14px;
        background: #f8fbff;
        border: 1px solid #dbe7f3;
        border-radius: 15px;
    }

    .audit-human-box span {
        display: block;
        color: #64748b;
        font-size: 0.68rem;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        margin-bottom: 4px;
    }

    .audit-human-box strong {
        color: #0f172a;
        font-size: 0.88rem;
        font-weight: 950;
    }

    .audit-change-list {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .audit-change-row {
        border: 1px solid #dbe7f3;
        border-radius: 16px;
        overflow: hidden;
        background: #ffffff;
    }

    .audit-change-field {
        padding: 10px 14px;
        background: #f8fbff;
        color: #0f172a;
        font-weight: 950;
        border-bottom: 1px solid #dbe7f3;
    }

    .audit-change-values {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0;
    }

    .audit-change-value {
        padding: 12px 14px;
        min-height: 64px;
    }

    .audit-change-value:first-child {
        border-right: 1px solid #dbe7f3;
    }

    .audit-change-value span {
        display: block;
        color: #64748b;
        font-size: 0.68rem;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        margin-bottom: 5px;
    }

    .audit-change-value strong {
        color: #0f172a;
        font-size: 0.9rem;
        font-weight: 850;
        word-break: break-word;
    }

    .audit-before {
        background: #fff7ed;
    }

    .audit-after {
        background: #ecfdf3;
    }

    .audit-no-change {
        padding: 14px;
        border-radius: 15px;
        background: #f8fbff;
        color: #64748b;
        border: 1px solid #dbe7f3;
        text-align: center;
    }

    .audit-technical-toggle {
        margin-top: 16px;
    }

    .audit-technical-toggle summary {
        cursor: pointer;
        color: #0f4c81;
        font-weight: 900;
        font-size: 0.82rem;
        padding: 10px 12px;
        border: 1px dashed #bfdbfe;
        border-radius: 14px;
        background: #f8fbff;
    }

    .audit-technical-body {
        margin-top: 10px;
    }

    @media (max-width: 1200px) {
        .audit-stats-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .audit-filter-form {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .audit-filter-actions {
            grid-column: span 2;
        }
    }

    @media (max-width: 768px) {
        .audit-stats-grid,
        .audit-filter-form,
        .audit-human-summary,
        .audit-change-values {
            grid-template-columns: 1fr;
        }

        .audit-change-value:first-child {
            border-right: 0;
            border-bottom: 1px solid #dbe7f3;
        }

        .audit-filter-actions {
            grid-column: auto;
        }

        .audit-table {
            min-width: 980px;
        }
    }
</style>
@endpush

@section('content')
@php
    $pageItems = $auditLogs->getCollection();
    $adminLogsOnPage = $pageItems->where('role', 'admin')->count();
    $modulesOnPage = $pageItems->pluck('module')->filter()->unique()->count();
    $todayOnPage = $pageItems->filter(fn ($log) => optional($log->created_at)->isToday())->count();

    $fieldLabels = [
        'service_flow' => 'Service Decision',
        'visit_purpose' => 'Visit Purpose',
        'flow_source' => 'Decision Source',
        'flow_override_reason' => 'Reason for Manual Change',
        'worker_id' => 'Assigned Personnel ID',
        'worker_name' => 'Assigned Personnel',
        'assigned_by' => 'Assigned By',
        'assigned_at' => 'Assigned Date/Time',
        'admin_notes' => 'Admin Notes',
        'status' => 'Request Status',
        'appointment_status' => 'Appointment Status',
        'appointment_date' => 'Appointment Date',
        'appointment_time' => 'Appointment Time',
        'approved_at' => 'Approved Date/Time',
        'rescheduled_at' => 'Rescheduled Date/Time',
        'cancelled_at' => 'Cancelled Date/Time',
        'cancel_reason' => 'Cancellation Reason',
        'client_action_request' => 'Client Request Type',
        'client_action_status' => 'Client Request Status',
        'client_requested_date' => 'Client Requested Date',
        'client_requested_time' => 'Client Requested Time',
        'client_request_reason' => 'Client Request Reason',
        'client_request_reviewed_at' => 'Review Date/Time',
        'client_request_review_notes' => 'Review Notes',
        'role' => 'Role',
        'email' => 'Email',
        'otp_challenge' => 'OTP Challenge',
        'verified_at' => 'Verified At',
        'logged_out_at' => 'Logged Out At',
    ];

    $formatAuditValue = function ($key, $value) {
        if ($value === null || $value === '') {
            return 'Not set';
        }

        if (is_bool($value)) {
            return $value ? 'Yes' : 'No';
        }

        if (is_array($value)) {
            return json_encode($value, JSON_UNESCAPED_SLASHES);
        }

        $value = (string) $value;

        $valueMaps = [
            'service_flow' => [
                'direct_service' => 'Direct Service',
                'inspection_required' => 'Inspection Required',
            ],
            'visit_purpose' => [
                'service' => 'Service',
                'inspection' => 'Inspection',
            ],
            'flow_source' => [
                'system' => 'System Decision',
                'manual' => 'Manual Admin Change',
            ],
            'appointment_status' => [
                'pending' => 'Pending',
                'approved' => 'Approved',
                'rescheduled' => 'Rescheduled',
                'cancelled' => 'Cancelled',
            ],
            'status' => [
                'pending' => 'Pending',
                'assigned' => 'Assigned',
                'in_progress' => 'In Progress',
                'completed' => 'Completed',
                'cancelled' => 'Cancelled',
            ],
            'client_action_request' => [
                'reschedule' => 'Reschedule Request',
                'cancel' => 'Cancellation Request',
            ],
            'client_action_status' => [
                'pending' => 'Pending',
                'approved' => 'Approved',
                'declined' => 'Declined',
            ],
            'otp_challenge' => [
                'sent' => 'OTP Sent',
            ],
        ];

        if (isset($valueMaps[$key][$value])) {
            return $valueMaps[$key][$value];
        }

        if (str_contains($key, '_at')) {
            try {
                return \Carbon\Carbon::parse($value)->format('Y-m-d h:i A');
            } catch (\Exception $e) {
                return $value;
            }
        }

        if (str_contains($key, '_time') && preg_match('/^\d{2}:\d{2}/', $value)) {
            try {
                return \Carbon\Carbon::parse($value)->format('h:i A');
            } catch (\Exception $e) {
                return $value;
            }
        }

        return ucwords(str_replace('_', ' ', $value));
    };
@endphp

<div class="audit-page">
    <div class="audit-stats-grid">
        <div class="audit-stat-card">
            <div class="audit-stat-icon"><i class="fas fa-shield-halved"></i></div>
            <div>
                <div class="audit-stat-label">Total Logs</div>
                <div class="audit-stat-value">{{ $auditLogs->total() }}</div>
            </div>
        </div>

        <div class="audit-stat-card">
            <div class="audit-stat-icon"><i class="fas fa-user-shield"></i></div>
            <div>
                <div class="audit-stat-label">Admin Logs on Page</div>
                <div class="audit-stat-value">{{ $adminLogsOnPage }}</div>
            </div>
        </div>

        <div class="audit-stat-card">
            <div class="audit-stat-icon"><i class="fas fa-layer-group"></i></div>
            <div>
                <div class="audit-stat-label">Modules on Page</div>
                <div class="audit-stat-value">{{ $modulesOnPage }}</div>
            </div>
        </div>

        <div class="audit-stat-card">
            <div class="audit-stat-icon"><i class="fas fa-calendar-day"></i></div>
            <div>
                <div class="audit-stat-label">Today on Page</div>
                <div class="audit-stat-value">{{ $todayOnPage }}</div>
            </div>
        </div>
    </div>

    <div class="audit-card">
        <div class="audit-card-header">
            <div>
                <h5 class="audit-card-title"><i class="fas fa-filter text-primary me-2"></i>Filter Audit Logs</h5>
                <p class="audit-card-subtitle">Search activity records by user, module, role, date, or action.</p>
            </div>
        </div>

        <div class="audit-card-body">
            <form method="GET" class="audit-filter-form">
                <div>
                    <label class="form-label">Search</label>
                    <input
                        type="text"
                        name="search"
                        class="form-control"
                        value="{{ request('search') }}"
                        placeholder="Action, user, email, description..."
                    >
                </div>

                <div>
                    <label class="form-label">Module</label>
                    <select name="module" class="form-select">
                        <option value="">All modules</option>
                        @foreach ($modules as $module)
                            <option value="{{ $module }}" @selected(request('module') === $module)>
                                {{ $module }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="form-label">Role</label>
                    <select name="role" class="form-select">
                        <option value="">All roles</option>
                        <option value="admin" @selected(request('role') === 'admin')>Admin</option>
                        <option value="hr" @selected(request('role') === 'hr')>HR</option>
                        <option value="inspector" @selected(request('role') === 'inspector')>Inspector</option>
                        <option value="client" @selected(request('role') === 'client')>Client</option>
                    </select>
                </div>

                <div>
                    <label class="form-label">User</label>
                    <select name="user_id" class="form-select">
                        <option value="">All users</option>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}" @selected((string) request('user_id') === (string) $user->id)>
                                {{ $user->name }} ({{ $user->role }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="form-label">From</label>
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>

                <div>
                    <label class="form-label">To</label>
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                </div>

                <button class="btn btn-primary" title="Apply filters">
                    <i class="fas fa-magnifying-glass"></i>
                </button>

                <a href="{{ route('admin.audit-logs.index') }}" class="btn btn-outline-secondary">
                    Reset
                </a>
            </form>
        </div>
    </div>

    <div class="audit-card">
        <div class="audit-card-header">
            <div>
                <h5 class="audit-card-title"><i class="fas fa-clock-rotate-left text-primary me-2"></i>System Activity</h5>
                <p class="audit-card-subtitle">Showing {{ $auditLogs->count() }} of {{ $auditLogs->total() }} log record(s).</p>
            </div>
        </div>

        <div class="audit-card-body p-0">
            @if ($auditLogs->count())
                <div class="table-responsive">
                    <table class="table audit-table align-middle">
                        <thead>
                            <tr>
                                <th>Date / Time</th>
                                <th>User</th>
                                <th>Role</th>
                                <th>Module</th>
                                <th>Action</th>
                                <th>Record</th>
                                <th>IP Address</th>
                                <th>Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($auditLogs as $log)
                                <tr>
                                    <td>
                                        <strong>{{ optional($log->created_at)->format('Y-m-d') }}</strong><br>
                                        <span class="text-muted">{{ optional($log->created_at)->format('h:i A') }}</span>
                                    </td>
                                    <td>
                                        <div class="audit-user">{{ $log->user_name ?? 'System' }}</div>
                                        <div class="audit-user-sub">{{ $log->user_email ?? 'No email' }}</div>
                                    </td>
                                    <td><span class="audit-badge">{{ $log->role ?? 'system' }}</span></td>
                                    <td>{{ $log->module ?? '—' }}</td>
                                    <td>
                                        <div class="audit-action">{{ $log->action }}</div>
                                        @if ($log->description)
                                            <div class="audit-description">{{ $log->description }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($log->record_type)
                                            <span class="text-muted small">{{ class_basename($log->record_type) }}</span><br>
                                            <strong>#{{ $log->record_id }}</strong>
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td>{{ $log->ip_address ?? '—' }}</td>
                                    <td>
                                        @if ($log->old_values || $log->new_values)
                                            <button
                                                type="button"
                                                class="btn btn-sm btn-outline-primary"
                                                data-bs-toggle="modal"
                                                data-bs-target="#auditLogModal{{ $log->id }}"
                                            >
                                                View
                                            </button>

                                            <div class="modal fade" id="auditLogModal{{ $log->id }}" tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <div>
                                                                <h5 class="modal-title">Audit Log Details</h5>
                                                                <p class="text-muted mb-0">{{ $log->action }} • {{ optional($log->created_at)->format('Y-m-d h:i A') }}</p>
                                                            </div>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>

                                                        <div class="modal-body">
                                                            @php
                                                                $oldValues = $log->old_values ?? [];
                                                                $newValues = $log->new_values ?? [];
                                                                $allKeys = collect(array_keys($oldValues))
                                                                    ->merge(array_keys($newValues))
                                                                    ->unique()
                                                                    ->values();

                                                                $changedKeys = $allKeys->filter(function ($key) use ($oldValues, $newValues) {
                                                                    $old = $oldValues[$key] ?? null;
                                                                    $new = $newValues[$key] ?? null;

                                                                    return json_encode($old) !== json_encode($new);
                                                                })->values();
                                                            @endphp

                                                            <div class="audit-human-summary">
                                                                <div class="audit-human-box">
                                                                    <span>Action</span>
                                                                    <strong>{{ $log->action }}</strong>
                                                                </div>

                                                                <div class="audit-human-box">
                                                                    <span>Module</span>
                                                                    <strong>{{ $log->module ?? 'System' }}</strong>
                                                                </div>

                                                                <div class="audit-human-box">
                                                                    <span>Performed By</span>
                                                                    <strong>{{ $log->user_name ?? 'System' }}</strong>
                                                                </div>
                                                            </div>

                                                            @if ($changedKeys->count())
                                                                <div class="audit-change-list">
                                                                    @foreach ($changedKeys as $key)
                                                                        @php
                                                                            $label = $fieldLabels[$key] ?? \Illuminate\Support\Str::headline($key);
                                                                            $beforeValue = $formatAuditValue($key, $oldValues[$key] ?? null);
                                                                            $afterValue = $formatAuditValue($key, $newValues[$key] ?? null);
                                                                        @endphp

                                                                        <div class="audit-change-row">
                                                                            <div class="audit-change-field">{{ $label }}</div>
                                                                            <div class="audit-change-values">
                                                                                <div class="audit-change-value audit-before">
                                                                                    <span>Before</span>
                                                                                    <strong>{{ $beforeValue }}</strong>
                                                                                </div>

                                                                                <div class="audit-change-value audit-after">
                                                                                    <span>After</span>
                                                                                    <strong>{{ $afterValue }}</strong>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    @endforeach
                                                                </div>
                                                            @else
                                                                <div class="audit-no-change">
                                                                    No field-level changes were recorded for this action.
                                                                </div>
                                                            @endif

                                                            <details class="audit-technical-toggle">
                                                                <summary>
                                                                    <i class="fas fa-code me-1"></i>
                                                                    Show technical data
                                                                </summary>

                                                                <div class="audit-technical-body">
                                                                    @if ($log->old_values)
                                                                        <div class="mb-3">
                                                                            <div class="fw-bold mb-2">Old Technical Data</div>
                                                                            <pre class="audit-json">{{ json_encode($log->old_values, JSON_PRETTY_PRINT) }}</pre>
                                                                        </div>
                                                                    @endif

                                                                    @if ($log->new_values)
                                                                        <div>
                                                                            <div class="fw-bold mb-2">New Technical Data</div>
                                                                            <pre class="audit-json">{{ json_encode($log->new_values, JSON_PRETTY_PRINT) }}</pre>
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                            </details>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            —
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="p-3">
                    {{ $auditLogs->links() }}
                </div>
            @else
                <div class="audit-empty">
                    <div>
                        <div class="audit-empty-icon"><i class="fas fa-shield-halved"></i></div>
                        <div class="audit-empty-title">No audit logs found</div>
                        <div>System activity records will appear here once actions are logged.</div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
