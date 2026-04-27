@php
    $submittedDone = !is_null($quotation->created_at);

    $assignedDone = !is_null($quotation->assigned_at)
        || in_array($quotation->status, ['assigned', 'in_progress', 'completed']);

    $progressDone = !is_null($quotation->inspected_at)
        || in_array($quotation->status, ['in_progress', 'completed']);

    $completedDone = !is_null($quotation->completed_at)
        || $quotation->status === 'completed';

    $steps = [
        [
            'label' => 'Submitted',
            'done' => $submittedDone,
            'time' => $quotation->created_at,
            'icon' => 'fa-file-circle-plus',
        ],
        [
            'label' => 'Assigned',
            'done' => $assignedDone,
            'time' => $quotation->assigned_at,
            'icon' => 'fa-user-check',
        ],
        [
            'label' => 'In Progress',
            'done' => $progressDone,
            'time' => $quotation->inspected_at,
            'icon' => 'fa-screwdriver-wrench',
        ],
        [
            'label' => 'Completed',
            'done' => $completedDone,
            'time' => $quotation->completed_at,
            'icon' => 'fa-circle-check',
        ],
    ];
@endphp

<style>
    .request-timeline {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 16px;
        margin-bottom: 18px;
    }

    .timeline-step {
        position: relative;
        background: #fff;
        border: 1px solid #e3ebf3;
        border-radius: 18px;
        padding: 16px 14px;
        box-shadow: 0 8px 18px rgba(15, 23, 42, 0.04);
        min-height: 110px;
    }

    .timeline-step.done {
        border-color: #bfdbfe;
        background: linear-gradient(180deg, #ffffff, #f8fbff);
    }

    .timeline-step.pending {
        background: #fcfdff;
    }

    .timeline-step::after {
        content: '';
        position: absolute;
        top: 38px;
        right: -16px;
        width: 16px;
        height: 2px;
        background: #dbe7f3;
    }

    .timeline-step:last-child::after {
        display: none;
    }

    .timeline-icon {
        width: 44px;
        height: 44px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 12px;
        font-size: 18px;
        background: #eef2f7;
        color: #64748b;
    }

    .timeline-step.done .timeline-icon {
        background: #eaf4ff;
        color: #1d4ed8;
    }

    .timeline-label {
        font-size: 0.95rem;
        font-weight: 800;
        color: #0f172a;
        margin-bottom: 6px;
    }

    .timeline-status {
        font-size: 0.82rem;
        font-weight: 700;
        margin-bottom: 6px;
    }

    .timeline-step.done .timeline-status {
        color: #15803d;
    }

    .timeline-step.pending .timeline-status {
        color: #64748b;
    }

    .timeline-time {
        font-size: 0.82rem;
        color: #6b7280;
        line-height: 1.4;
    }

    @media (max-width: 991.98px) {
        .request-timeline {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .timeline-step::after {
            display: none;
        }
    }

    @media (max-width: 575.98px) {
        .request-timeline {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="request-timeline">
    @foreach ($steps as $step)
        <div class="timeline-step {{ $step['done'] ? 'done' : 'pending' }}">
            <div class="timeline-icon">
                <i class="fas {{ $step['icon'] }}"></i>
            </div>

            <div class="timeline-label">{{ $step['label'] }}</div>
            <div class="timeline-status">
                {{ $step['done'] ? 'Completed Step' : 'Waiting' }}
            </div>

            <div class="timeline-time">
                @if ($step['time'])
                    {{ $step['time']->format('M d, Y h:i A') }}
                @else
                    Not yet available
                @endif
            </div>
        </div>
    @endforeach
</div>