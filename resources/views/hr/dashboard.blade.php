@extends('hr.layouts.app')

@section('title', 'HR Dashboard - WRPlumb')
@section('topbar_title', 'HR Dashboard')
@section('topbar_subtitle', 'Monitor conversations routed to HR and support operations.')

@section('content')
<div class="page-header">
    <h1>Welcome, {{ auth()->user()->first_name ?? 'HR' }}</h1>
    <p>Review support workload, reply to client concerns, and escalate issues when needed.</p>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-top">
                <div>
                    <div class="stat-label">Total in HR Queue</div>
                    <div class="stat-value">{{ $openCount }}</div>
                </div>
                <div class="stat-icon blue">
                    <i class="fas fa-inbox"></i>
                </div>
            </div>
            <div class="stat-helper">Support conversations currently handled by HR.</div>
            <div class="accent-line"><span style="width:70%; background:#1d9bf0;"></span></div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-top">
                <div>
                    <div class="stat-label">Routed to HR</div>
                    <div class="stat-value">{{ $routedCount }}</div>
                </div>
                <div class="stat-icon orange">
                    <i class="fas fa-share"></i>
                </div>
            </div>
            <div class="stat-helper">Concerns forwarded by the automated support workflow.</div>
            <div class="accent-line"><span style="width:55%; background:#f59e0b;"></span></div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-top">
                <div>
                    <div class="stat-label">Resolved Conversations</div>
                    <div class="stat-value">{{ $resolvedCount }}</div>
                </div>
                <div class="stat-icon green">
                    <i class="fas fa-circle-check"></i>
                </div>
            </div>
            <div class="stat-helper">Support concerns already completed and closed.</div>
            <div class="accent-line"><span style="width:62%; background:#16a34a;"></span></div>
        </div>
    </div>
</div>

<div class="panel">
    <div class="panel-header">
        <h5><i class="fas fa-bolt me-2 text-primary"></i>Quick Action</h5>
    </div>
    <div class="panel-body">
        <a href="{{ route('hr.support.index') }}" class="btn btn-primary">
            <i class="fas fa-comments me-2"></i>Open Support Queue
        </a>
    </div>
</div>
@endsection