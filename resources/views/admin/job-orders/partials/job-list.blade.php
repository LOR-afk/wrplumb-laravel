@php
    $jobCollection = method_exists($jobOrders, 'getCollection')
        ? $jobOrders->getCollection()
        : collect($jobOrders);

    $clientName = function ($jobOrder) {
        $request = $jobOrder?->quotationRequest;

        return $request?->full_name
            ?: trim(($request?->first_name ?? '') . ' ' . ($request?->last_name ?? ''))
            ?: 'Unknown Client';
    };

    $workerName = fn ($jobOrder) => $jobOrder?->worker?->name
        ?? trim(($jobOrder?->worker?->first_name ?? '') . ' ' . ($jobOrder?->worker?->last_name ?? ''))
        ?: 'Not assigned';

    $statusMeta = function ($status) {
        return match ($status) {
            'scheduled' => ['Scheduled', 'scheduled', 25],
            'in_progress' => ['In Progress', 'in_progress', 60],
            'completed' => ['Completed', 'completed', 100],
            'cancelled' => ['Cancelled', 'cancelled', 0],
            default => [ucfirst(str_replace('_', ' ', $status ?? 'pending')), 'default', 10],
        };
    };

    $currentSort = request('sort', 'created_at');
    $currentDirection = request('direction', 'desc');

    $sortUrl = function (string $column) use ($currentSort, $currentDirection) {
        $nextDirection = ($currentSort === $column && $currentDirection === 'asc')
            ? 'desc'
            : 'asc';

        return route('admin.job-orders.index', array_merge(
            request()->except('page'),
            [
                'sort' => $column,
                'direction' => $nextDirection,
            ]
        ));
    };

    $sortArrow = function (string $column) use ($currentSort, $currentDirection) {
        if ($currentSort !== $column) {
            return '↕';
        }

        return $currentDirection === 'asc' ? '↑' : '↓';
    };
@endphp

<div class="job-panel-head">
    <div>
        <h5><i class="fas fa-clipboard-list me-2 text-primary"></i>All Job Orders</h5>
        <p>
            Showing {{ $jobCollection->count() }} of
            {{ method_exists($jobOrders, 'total') ? $jobOrders->total() : $jobCollection->count() }}
            job order(s).
        </p>
    </div>
</div>

@if ($jobCollection->count())
    <div class="job-table-wrap">
        <table class="table align-middle job-orders-table">
            <thead>
                <tr>
                    <th>
                        <button type="button" class="job-sort-link job-sort-button" data-sort-column="job_order_no">
                            Job Order No.
                            <span class="job-sort-arrow">{{ $sortArrow('job_order_no') }}</span>
                        </button>
                    </th>

                    <th>
                        <button type="button" class="job-sort-link job-sort-button" data-sort-column="client">
                            Client
                            <span class="job-sort-arrow">{{ $sortArrow('client') }}</span>
                        </button>
                    </th>

                    <th>
                        <button type="button" class="job-sort-link job-sort-button" data-sort-column="service_type">
                            Service
                            <span class="job-sort-arrow">{{ $sortArrow('service_type') }}</span>
                        </button>
                    </th>

                    <th>
                        <button type="button" class="job-sort-link job-sort-button" data-sort-column="worker">
                            Worker
                            <span class="job-sort-arrow">{{ $sortArrow('worker') }}</span>
                        </button>
                    </th>

                    <th>
                        <button type="button" class="job-sort-link job-sort-button" data-sort-column="scheduled_date">
                            Schedule
                            <span class="job-sort-arrow">{{ $sortArrow('scheduled_date') }}</span>
                        </button>
                    </th>

                    <th>
                        <button type="button" class="job-sort-link job-sort-button" data-sort-column="status">
                            Status
                            <span class="job-sort-arrow">{{ $sortArrow('status') }}</span>
                        </button>
                    </th>

                    <th>
                        <button type="button" class="job-sort-link job-sort-button" data-sort-column="progress">
                            Progress
                            <span class="job-sort-arrow">{{ $sortArrow('progress') }}</span>
                        </button>
                    </th>

                    <th class="text-end">Action</th>
                </tr>
            </thead>

            <tbody>
                @foreach ($jobCollection as $jobOrder)
                    @php
                        [$statusLabel, $statusClass, $progress] = $statusMeta($jobOrder->status);
                    @endphp

                    <tr class="job-row">
                        <td>
                            <div class="job-order-code">
                                <strong>{{ $jobOrder->job_order_no }}</strong>
                            </div>
                        </td>

                        <td>
                            <div class="job-table-primary">{{ $clientName($jobOrder) }}</div>
                            <div class="job-table-muted">{{ $jobOrder->quotationRequest?->email ?? '—' }}</div>
                        </td>

                        <td>{{ $jobOrder->service_type ?? '—' }}</td>

                        <td>{{ $workerName($jobOrder) }}</td>

                        <td>
                            @if ($jobOrder->scheduled_date)
                                <div class="job-table-primary">
                                    {{ optional($jobOrder->scheduled_date)->format('Y-m-d') }}
                                </div>
                                <div class="job-table-muted">
                                    {{ $jobOrder->scheduled_time ?: 'Time not set' }}
                                </div>
                            @else
                                —
                            @endif
                        </td>

                        <td>
                            <span class="job-status {{ $statusClass }}">{{ $statusLabel }}</span>
                        </td>

                        <td>
                            <div class="job-progress-inline">
                                <span>{{ $progress }}%</span>
                                <div class="job-progress-track">
                                    <div
                                        class="job-progress-fill {{ $statusClass }}"
                                        style="width: {{ $progress }}%"
                                    ></div>
                                </div>
                            </div>
                        </td>

                        <td class="job-action-cell">
                            <a
                                href="{{ route('admin.job-orders.show', $jobOrder) }}"
                                class="btn btn-sm btn-outline-primary job-view-btn"
                            >
                                <i class="fas fa-eye me-1"></i> View
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="job-pagination-row">
        <span>
            Showing {{ $jobOrders->firstItem() ?? 1 }}
            to {{ $jobOrders->lastItem() ?? $jobCollection->count() }}
            of {{ $jobOrders->total() ?? $jobCollection->count() }} results
        </span>

        <div class="job-pagination-links">
            {{ $jobOrders->links() }}
        </div>
    </div>
@else
    <div class="job-empty-state">
        <div><i class="fas fa-clipboard-list"></i></div>
        <strong>No job orders found</strong>
        <p>Generated job orders from approved service requests will appear here.</p>
    </div>
@endif