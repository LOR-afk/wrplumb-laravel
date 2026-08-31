@extends('client.layouts.app')

@section('title', 'Job Order Details')
@section('topbar_title', 'Job Order Details')
@section('topbar_subtitle', 'Review the current execution status and service information.')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/client/job-order-show.css') }}?v=20260818a">
@endpush

@section('content')
@php
    $status = $jobOrder->status ?? 'scheduled';

    $warrantyClaims = $jobOrder->warrantyClaims()
        ->with('backJob')
        ->latest()
        ->get();

    $latestWarrantyClaim = $warrantyClaims->first();

    $activeWarrantyClaim = $warrantyClaims->first(function ($claim) {
        return in_array($claim->status, ['pending', 'approved']);
    });

    $warrantyExpiresAt = $jobOrder->completed_at
        ? $jobOrder->completed_at->copy()->addDays(30)
        : null;

    $isCompleted = $status === 'completed' && !empty($jobOrder->completed_at);
    $isWithinWarranty = $warrantyExpiresAt
        ? now()->lessThanOrEqualTo($warrantyExpiresAt)
        : false;

    $canSubmitWarrantyClaim = $isCompleted && $isWithinWarranty && !$activeWarrantyClaim;

    $warrantyStatusText = match (true) {
        !$isCompleted => 'Available after job completion',
        $isWithinWarranty => 'Within 30-day warranty period',
        default => 'Warranty period ended',
    };

    $warrantyStatusClass = match (true) {
        !$isCompleted => 'secondary',
        $isWithinWarranty => 'success',
        default => 'danger',
    };
@endphp

@php
    $statusKey = strtolower((string) $status);

    $statusLabel = match ($statusKey) {
        'scheduled' => 'Scheduled',
        'in_progress' => 'In Progress',
        'completed' => 'Completed',
        'cancelled', 'canceled' => 'Cancelled',
        default => ucfirst(str_replace('_', ' ', $statusKey)),
    };

    $statusClass = match ($statusKey) {
        'scheduled' => 'blue',
        'in_progress' => 'orange',
        'completed' => 'green',
        'cancelled', 'canceled' => 'gray',
        default => 'gray',
    };

    $workerName = $jobOrder->worker->name
        ?? $jobOrder->worker->first_name
        ?? 'Not assigned';

    $scheduleText = $jobOrder->scheduled_date
        ? optional($jobOrder->scheduled_date)->format('M d, Y')
        : 'Not yet scheduled';

    $scheduleTimeText = $jobOrder->scheduled_time
        ? \Carbon\Carbon::parse($jobOrder->scheduled_time)->format('h:i A')
        : null;
@endphp

<div class="client-job-show-page">
    <section class="client-job-hero">
        <div class="client-job-hero-copy">
            <span>Job Order</span>

            <div class="client-job-title-row">
                <h2>{{ $jobOrder->service_type ?? 'Service Job' }}</h2>
                <em class="client-job-status {{ $statusClass }}">
                    {{ $statusLabel }}
                </em>
            </div>

            <p>
                <i class="fas fa-clipboard-check"></i>
                {{ $jobOrder->job_order_no }}
            </p>
        </div>

        <div class="client-job-hero-actions">
            <a href="{{ route('client.job-orders.index') }}" class="client-job-btn secondary">
                <i class="fas fa-arrow-left"></i>
                My Job Orders
            </a>
        </div>
    </section>

    <section class="client-job-summary">
        <article>
            <span>Status</span>
            <strong>{{ $statusLabel }}</strong>
        </article>

        <article>
            <span>Assigned Worker</span>
            <strong>{{ $workerName }}</strong>
        </article>

        <article>
            <span>Schedule</span>
            <strong>
                {{ $scheduleText }}
                @if ($scheduleTimeText)
                    · {{ $scheduleTimeText }}
                @endif
            </strong>
        </article>

        <article>
            <span>Service Flow</span>
            <strong>{{ ucfirst(str_replace('_', ' ', $jobOrder->service_flow ?? '—')) }}</strong>
        </article>

        <article>
            <span>Project Type</span>
            <strong>{{ $jobOrder->project_type ?? '—' }}</strong>
        </article>
    </section>

    <div class="client-job-main-grid">
        <section class="client-job-card">
            <div class="client-job-card-head">
                <span class="client-job-card-icon blue">
                    <i class="fas fa-screwdriver-wrench"></i>
                </span>

                <div>
                    <h3>Service Execution</h3>
                    <p>Core details for this job order.</p>
                </div>
            </div>

            <div class="client-job-card-body">
                <div class="client-job-detail-grid">
                    <div>
                        <span>Job Order No.</span>
                        <strong>{{ $jobOrder->job_order_no }}</strong>
                    </div>

                    <div>
                        <span>Service Type</span>
                        <strong>{{ $jobOrder->service_type ?? '—' }}</strong>
                    </div>

                    <div>
                        <span>Assigned Worker</span>
                        <strong>{{ $workerName }}</strong>
                    </div>

                    <div>
                        <span>Service Flow</span>
                        <strong>{{ ucfirst(str_replace('_', ' ', $jobOrder->service_flow ?? '—')) }}</strong>
                    </div>

                    <div>
                        <span>Project Type</span>
                        <strong>{{ $jobOrder->project_type ?? '—' }}</strong>
                    </div>

                    <div>
                        <span>Completion Date</span>
                        <strong>
                            {{ optional($jobOrder->completed_at)->format('M d, Y · h:i A') ?? 'Not completed yet' }}
                        </strong>
                    </div>
                </div>

                <div class="client-job-notes-grid">
                    <article>
                        <span>Scope of Work</span>
                        <p>{{ $jobOrder->scope_of_work ?? 'No scope of work provided.' }}</p>
                    </article>

                    <article>
                        <span>Work Remarks</span>
                        <p>{{ $jobOrder->work_remarks ?? 'No work remarks yet.' }}</p>
                    </article>

                    <article>
                        <span>Completion Notes</span>
                        <p>{{ $jobOrder->completion_notes ?? 'No completion notes yet.' }}</p>
                    </article>
                </div>
            </div>
        </section>

        <section class="client-job-card client-job-progress-card">
            <div class="client-job-card-head">
                <span class="client-job-card-icon violet">
                    <i class="fas fa-chart-line"></i>
                </span>

                <div>
                    <h3>Current Progress</h3>
                    <p>Where this service currently stands.</p>
                </div>
            </div>

            <div class="client-job-card-body">
                @php
                    $progressPercent = match ($statusKey) {
                        'scheduled' => 25,
                        'in_progress' => 60,
                        'completed' => 100,
                        'cancelled', 'canceled' => 0,
                        default => 10,
                    };
                @endphp

                <div class="client-job-progress-summary">
                    <div>
                        <span>Overall Progress</span>
                        <strong>{{ $progressPercent }}%</strong>
                    </div>

                    <em>{{ $statusLabel }}</em>
                </div>

                <div class="client-job-progress-track">
                    <i style="width: {{ $progressPercent }}%;"></i>
                </div>

<div class="client-job-progress-steps">
    <div class="{{ $progressPercent > 10 ? 'done' : ($progressPercent === 10 ? 'current' : 'pending') }}">
        <span><i class="fas fa-check"></i></span>
        <small>Created</small>
    </div>

    <div class="{{ $progressPercent > 25 ? 'done' : ($progressPercent === 25 ? 'current' : 'pending') }}">
        <span><i class="fas fa-calendar-check"></i></span>
        <small>Scheduled</small>
    </div>

    <div class="{{ $progressPercent > 60 ? 'done' : ($progressPercent === 60 ? 'current' : 'pending') }}">
        <span><i class="fas fa-play"></i></span>
        <small>In Progress</small>
    </div>

    <div class="{{ $progressPercent >= 100 ? 'done' : 'pending' }}">
        <span><i class="fas fa-flag-checkered"></i></span>
        <small>Completed</small>
    </div>
</div>
            </div>
        </section>
    </div>

    <section class="client-job-card warranty-card-clean">
        <div class="client-job-card-head warranty-head">
            <span class="client-job-card-icon green">
                <i class="fas fa-shield-halved"></i>
            </span>

            <div class="warranty-head-copy">
                <h3>Warranty Claim</h3>
                <p>Report a recurring issue related to this completed service.</p>
            </div>

            <em class="warranty-status-badge {{ $warrantyStatusClass }}">
                {{ $warrantyStatusText }}
            </em>
        </div>

        <div class="client-job-card-body">
            <div class="warranty-summary-grid">
                <article>
                    <span>Completed Date</span>
                    <strong>{{ optional($jobOrder->completed_at)->format('M d, Y · h:i A') ?? 'Not completed yet' }}</strong>
                </article>

                <article>
                    <span>Warranty Until</span>
                    <strong>{{ $warrantyExpiresAt ? $warrantyExpiresAt->format('M d, Y · h:i A') : 'Not available' }}</strong>
                </article>

                <article>
                    <span>Total Claims</span>
                    <strong>{{ $warrantyClaims->count() }}</strong>
                </article>
            </div>

            @if ($latestWarrantyClaim)
                <div class="warranty-latest-claim">
                    <div>
                        <span>Latest Claim</span>
                        <strong>{{ $latestWarrantyClaim->claim_no }}</strong>
                        <p>{{ \Illuminate\Support\Str::limit($latestWarrantyClaim->issue_description, 140) }}</p>

                        @if ($latestWarrantyClaim->backJob)
                            <small>
                                Backjob:
                                {{ $latestWarrantyClaim->backJob->backjob_no }}
                                ·
                                {{ ucwords(str_replace('_', ' ', $latestWarrantyClaim->backJob->status)) }}
                            </small>
                        @endif

                        @if ($latestWarrantyClaim->rejection_reason)
                            <small>
                                Rejection reason: {{ $latestWarrantyClaim->rejection_reason }}
                            </small>
                        @endif
                    </div>

                    <em class="claim-status-pill {{ $latestWarrantyClaim->status }}">
                        {{ ucfirst($latestWarrantyClaim->status) }}
                    </em>
                </div>
            @endif

            @if ($canSubmitWarrantyClaim)
                <div class="warranty-message success">
                    <i class="fas fa-circle-check"></i>
                    This job is still within the warranty period. You may submit a claim if the same issue returned.
                </div>

                <form method="POST" action="{{ route('client.warranty-claims.store', $jobOrder) }}" class="warranty-form-clean">
                    @csrf

                    <div class="warranty-field">
                        <label for="issue_description">Issue Description</label>
                        <textarea
                            id="issue_description"
                            name="issue_description"
                            class="form-control"
                            placeholder="Describe the recurring issue. Example: The repaired leak returned in the same area."
                            required
                        >{{ old('issue_description') }}</textarea>
                        <small>Please describe what happened after the service was completed.</small>
                    </div>

                    <div class="warranty-date-grid">
                        <div class="warranty-field">
                            <label for="preferred_date">Preferred Date</label>
                            <input
                                type="date"
                                id="preferred_date"
                                name="preferred_date"
                                class="form-control"
                                value="{{ old('preferred_date') }}"
                            >
                        </div>

                        <div class="warranty-field">
                            <label for="preferred_time">Preferred Time</label>
                            <input
                                type="time"
                                id="preferred_time"
                                name="preferred_time"
                                class="form-control"
                                value="{{ old('preferred_time') }}"
                            >
                        </div>
                    </div>

                    <button type="submit" class="warranty-submit-btn">
                        <i class="fas fa-paper-plane"></i>
                        Submit Warranty Claim
                    </button>
                </form>
            @elseif ($activeWarrantyClaim)
                <div class="warranty-message warning">
                    <i class="fas fa-clock"></i>
                    You already have an active warranty claim for this job order. Please wait for admin review or backjob updates.
                </div>
            @elseif (!$isCompleted)
                <div class="warranty-message neutral">
                    <i class="fas fa-lock"></i>
                    Warranty claim submission becomes available after the job order is marked as completed.
                </div>
            @else
                <div class="warranty-message danger">
                    <i class="fas fa-triangle-exclamation"></i>
                    This job order is outside the 30-day warranty period. Please contact support for further assistance.
                </div>
            @endif
        </div>
    </section>
</div>
@endsection