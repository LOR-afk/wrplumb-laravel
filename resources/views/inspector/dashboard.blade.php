@extends('inspector.layouts.app')

@section('title', 'Inspector Dashboard - WRPlumb')
@section('topbar_title', 'Inspector Dashboard')
@section('topbar_subtitle', 'Monitor assigned service requests and field activity.')

@section('content')
<div class="page-header">
    <h1>Welcome, {{ auth()->user()->first_name ?? 'Inspector' }}</h1>
    <p>Review your assigned requests, update progress, and manage your availability schedule.</p>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-top">
                <div>
                    <div class="stat-label">Assigned Requests</div>
                    <div class="stat-value">{{ $assignedCount }}</div>
                </div>
                <div class="stat-icon blue">
                    <i class="fas fa-clipboard-list"></i>
                </div>
            </div>
            <div class="stat-helper">Requests assigned to you and waiting for action.</div>
            <div class="accent-line"><span style="width:72%; background:#1d9bf0;"></span></div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-top">
                <div>
                    <div class="stat-label">In Progress</div>
                    <div class="stat-value">{{ $inProgressCount }}</div>
                </div>
                <div class="stat-icon orange">
                    <i class="fas fa-screwdriver-wrench"></i>
                </div>
            </div>
            <div class="stat-helper">Active field work and ongoing service tasks.</div>
            <div class="accent-line"><span style="width:58%; background:#f59e0b;"></span></div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-top">
                <div>
                    <div class="stat-label">Completed</div>
                    <div class="stat-value">{{ $completedCount }}</div>
                </div>
                <div class="stat-icon green">
                    <i class="fas fa-circle-check"></i>
                </div>
            </div>
            <div class="stat-helper">Finished service requests with completed updates.</div>
            <div class="accent-line"><span style="width:64%; background:#16a34a;"></span></div>
        </div>
    </div>
</div>

<div class="panel">
    <div class="panel-header">
        <h5><i class="fas fa-bolt me-2 text-primary"></i>Quick Actions</h5>
    </div>
    <div class="panel-body d-flex gap-2 flex-wrap">
        <a href="{{ route('inspector.quotations.index') }}" class="btn btn-primary">
            <i class="fas fa-file-signature me-2"></i>View Assigned Requests
        </a>

        <a href="{{ route('inspector.availability.index') }}" class="btn btn-outline-primary">
            <i class="fas fa-calendar-check me-2"></i>Manage Availability
        </a>
    </div>
</div>
@endsection