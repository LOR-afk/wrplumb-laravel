@php
    $jobCollection = method_exists($jobOrders, 'getCollection')
        ? $jobOrders->getCollection()
        : collect($jobOrders);
@endphp

<div class="job-stat-card">
    <span class="job-stat-icon blue"><i class="fas fa-clipboard-list"></i></span>
    <div>
        <span>Total Jobs</span>
        <strong>{{ $summary['total'] ?? $jobCollection->count() }}</strong>
    </div>
</div>

<div class="job-stat-card">
    <span class="job-stat-icon amber"><i class="fas fa-calendar-check"></i></span>
    <div>
        <span>Scheduled</span>
        <strong>{{ $summary['scheduled'] ?? 0 }}</strong>
    </div>
</div>

<div class="job-stat-card">
    <span class="job-stat-icon blue"><i class="fas fa-play"></i></span>
    <div>
        <span>In Progress</span>
        <strong>{{ $summary['in_progress'] ?? 0 }}</strong>
    </div>
</div>

<div class="job-stat-card">
    <span class="job-stat-icon green"><i class="fas fa-circle-check"></i></span>
    <div>
        <span>Completed</span>
        <strong>{{ $summary['completed'] ?? 0 }}</strong>
    </div>
</div>

<div class="job-stat-card">
    <span class="job-stat-icon gray"><i class="fas fa-ban"></i></span>
    <div>
        <span>Cancelled</span>
        <strong>{{ $summary['cancelled'] ?? 0 }}</strong>
    </div>
</div>