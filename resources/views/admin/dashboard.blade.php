@extends('admin.layouts.app')

@section('title', 'Dashboard Overview - WRPlumb')
@section('topbar_title', 'Dashboard Overview')
@section('topbar_subtitle', 'Monitor clients, quotations, support activity, and inspector availability.')

@section('content')
<style>
    .dashboard-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 18px;
        margin-bottom: 24px;
    }

    .dashboard-grid-2 {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 18px;
        margin-bottom: 24px;
    }

    .hero-card {
        background: linear-gradient(135deg, #ffffff, #f7fbff);
        border: 1px solid var(--wr-border);
        border-radius: 22px;
        box-shadow: var(--wr-shadow);
        padding: 26px 28px;
        margin-bottom: 24px;
        position: relative;
        overflow: hidden;
    }

    .hero-card::after {
        content: "";
        position: absolute;
        top: -40px;
        right: -40px;
        width: 180px;
        height: 180px;
        border-radius: 50%;
        background: rgba(29, 155, 240, 0.08);
    }

    .hero-title {
        font-size: 2rem;
        font-weight: 800;
        margin-bottom: 8px;
        color: #0f172a;
        position: relative;
        z-index: 1;
    }

    .hero-text {
        color: var(--wr-muted);
        margin-bottom: 18px;
        position: relative;
        z-index: 1;
    }


    .stat-helper {
        margin-top: 10px;
        color: var(--wr-muted);
        font-size: 0.92rem;
    }

    .accent-line {
        height: 4px;
        border-radius: 999px;
        margin-top: 16px;
        background: #eaf2fb;
        overflow: hidden;
    }

    .accent-line span {
        display: block;
        height: 100%;
        border-radius: 999px;
    }

    .action-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 16px;
    }

    .action-card {
        display: block;
        text-decoration: none;
        color: inherit;
        background: #fff;
        border: 1px solid var(--wr-border);
        border-radius: 18px;
        padding: 18px;
        transition: all 0.2s ease;
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.04);
    }

    .action-card:hover {
        transform: translateY(-2px);
        border-color: #cfe4fb;
        box-shadow: 0 12px 28px rgba(15, 23, 42, 0.07);
    }

    .action-icon {
        width: 46px;
        height: 46px;
        border-radius: 14px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 14px;
        font-size: 18px;
    }

    .action-title {
        font-size: 1.02rem;
        font-weight: 800;
        margin-bottom: 6px;
    }

    .action-text {
        color: var(--wr-muted);
        font-size: 0.92rem;
        margin: 0;
    }

    .summary-list {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .summary-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 12px 14px;
        border: 1px solid #edf2f7;
        border-radius: 14px;
        background: #fbfdff;
    }

    .summary-label {
        color: #334155;
        font-weight: 700;
    }

    .summary-value {
        font-weight: 800;
        color: #0f172a;
    }

    .mini-panel {
        background: #fff;
        border: 1px solid var(--wr-border);
        border-radius: 18px;
        padding: 18px;
    }

    .mini-panel-title {
        font-size: 1rem;
        font-weight: 800;
        margin-bottom: 12px;
    }

    .activity-item {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        padding: 12px 0;
        border-bottom: 1px solid #edf2f7;
    }

    .activity-item:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }

    .activity-dot {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        margin-top: 6px;
        flex-shrink: 0;
    }

    .activity-text {
        color: #334155;
        margin: 0;
        font-size: 0.94rem;
    }

    .activity-sub {
        color: var(--wr-muted);
        font-size: 0.85rem;
        margin-top: 2px;
    }

    @media (max-width: 1199.98px) {
        .dashboard-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .dashboard-grid-2 {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 767.98px) {
        .dashboard-grid,
        .action-grid {
            grid-template-columns: 1fr;
        }

        .hero-title {
            font-size: 1.5rem;
        }
    }
</style>

<div class="hero-card">
    <div class="hero-title">Welcome, Admin</div>
    <p class="hero-text">
        Here is a quick view of your system activity, service requests, and inspector availability.
    </p>
</div>

<div class="dashboard-grid">
    <div class="stat-card">
        <div class="stat-top">
            <div>
                <div class="stat-label">Clients</div>
                <div class="stat-value">{{ $clientCount }}</div>
            </div>
            <div class="stat-icon blue">
                <i class="fas fa-users"></i>
            </div>
        </div>
        <div class="stat-helper">Registered client accounts in the system.</div>
        <div class="accent-line"><span style="width: 78%; background:#1d9bf0;"></span></div>
    </div>

    <div class="stat-card">
        <div class="stat-top">
            <div>
                <div class="stat-label">Workers / Inspectors</div>
                <div class="stat-value">{{ $workerCount }}</div>
            </div>
            <div class="stat-icon green">
                <i class="fas fa-user-check"></i>
            </div>
        </div>
        <div class="stat-helper">Available field personnel for assignment.</div>
        <div class="accent-line"><span style="width: 62%; background:#16a34a;"></span></div>
    </div>

    <div class="stat-card">
        <div class="stat-top">
            <div>
                <div class="stat-label">Pending Quotations</div>
                <div class="stat-value">{{ $pendingQuotationsCount }}</div>
            </div>
            <div class="stat-icon orange">
                <i class="fas fa-hourglass-half"></i>
            </div>
        </div>
        <div class="stat-helper">Requests awaiting review or action.</div>
        <div class="accent-line"><span style="width: 48%; background:#f59e0b;"></span></div>
    </div>

    <div class="stat-card">
        <div class="stat-top">
            <div>
                <div class="stat-label">Assigned Quotations</div>
                <div class="stat-value">{{ $assignedQuotationsCount }}</div>
            </div>
            <div class="stat-icon pink">
                <i class="fas fa-clipboard-check"></i>
            </div>
        </div>
        <div class="stat-helper">Requests already assigned to inspectors.</div>
        <div class="accent-line"><span style="width: 56%; background:#db2777;"></span></div>
    </div>
</div>

<div class="dashboard-grid-2">
    <div class="panel">
        <div class="panel-header">
            <h5><i class="fas fa-bolt me-2 text-primary"></i>Quick Actions</h5>
        </div>
        <div class="panel-body">
            <div class="action-grid">
                <a href="{{ route('admin.clients.index') }}" class="action-card">
                    <div class="action-icon" style="background:#eaf4ff; color:#1d4ed8;">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="action-title">Manage Clients</div>
                    <p class="action-text">View, activate, and manage registered client accounts.</p>
                </a>

                <a href="{{ route('admin.quotations.index') }}" class="action-card">
                    <div class="action-icon" style="background:#fff7ed; color:#f97316;">
                        <i class="fas fa-file-signature"></i>
                    </div>
                    <div class="action-title">View Quotations</div>
                    <p class="action-text">Review quotation requests and assign available inspectors.</p>
                </a>

                <a href="{{ route('admin.support.index') }}" class="action-card">
                    <div class="action-icon" style="background:#ecfdf3; color:#16a34a;">
                        <i class="fas fa-comments"></i>
                    </div>
                    <div class="action-title">Support Requests</div>
                    <p class="action-text">Check escalated concerns and respond to client support issues.</p>
                </a>

                <a href="{{ route('admin.inspectors.availability') }}" class="action-card">
                    <div class="action-icon" style="background:#fdf2f8; color:#db2777;">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div class="action-title">Inspector Availability</div>
                    <p class="action-text">Monitor duty schedules and check who is available for dispatch.</p>
                </a>
            </div>
        </div>
    </div>

    <div class="panel">
        <div class="panel-header">
            <h5><i class="fas fa-chart-pie me-2 text-primary"></i>System Summary</h5>
        </div>
        <div class="panel-body">
            <div class="summary-list">
                <div class="summary-row">
                    <span class="summary-label">Total Clients</span>
                    <span class="summary-value">{{ $clientCount }}</span>
                </div>

                <div class="summary-row">
                    <span class="summary-label">Total Inspectors</span>
                    <span class="summary-value">{{ $workerCount }}</span>
                </div>

                <div class="summary-row">
                    <span class="summary-label">Pending Requests</span>
                    <span class="badge-soft orange">{{ $pendingQuotationsCount }}</span>
                </div>

                <div class="summary-row">
                    <span class="summary-label">Assigned Requests</span>
                    <span class="badge-soft blue">{{ $assignedQuotationsCount }}</span>
                </div>

                <div class="summary-row">
                    <span class="summary-label">Open Support Concerns</span>
                    <span class="badge-soft green">{{ $openSupportCount }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="panel">
    <div class="panel-header">
        <h5><i class="fas fa-clock-rotate-left me-2 text-primary"></i>Operational Notes</h5>
    </div>
    <div class="panel-body">
        <div class="mini-panel">
            <div class="mini-panel-title">Today’s Focus</div>

            <div class="activity-item">
                <div class="activity-dot" style="background:#1d9bf0;"></div>
                <div>
                    <p class="activity-text">Review new quotation requests and assign inspectors based on availability.</p>
                    <div class="activity-sub">Assignment workflow</div>
                </div>
            </div>

            <div class="activity-item">
                <div class="activity-dot" style="background:#16a34a;"></div>
                <div>
                    <p class="activity-text">Check support requests escalated from HR and provide admin-level response if needed.</p>
                    <div class="activity-sub">Support operations</div>
                </div>
            </div>

            <div class="activity-item">
                <div class="activity-dot" style="background:#f59e0b;"></div>
                <div>
                    <p class="activity-text">Monitor inspector duty schedules before dispatching field personnel.</p>
                    <div class="activity-sub">Inspector coordination</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection