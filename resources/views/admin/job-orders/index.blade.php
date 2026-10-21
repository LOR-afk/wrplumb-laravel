@extends('admin.layouts.app')

@section('title', 'Job Orders - WRPlumb')
@section('topbar_title', 'Job Orders')
@section('topbar_subtitle', 'Track scheduled and ongoing service execution.')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin/job-orders.css') }}">
@endpush

@section('content')
<div class="job-orders-page">
    <section class="job-stats-grid" id="jobStatsContainer">
        @include('admin.job-orders.partials.stats', [
            'jobOrders' => $jobOrders,
            'summary' => $summary,
        ])
    </section>

    <section class="job-filter-bar">
        <form
            method="GET"
            action="{{ route('admin.job-orders.index') }}"
            class="job-filter-form"
            id="jobFilterForm"
        >
            <input
                type="hidden"
                name="sort"
                id="jobSortField"
                value="{{ request('sort', 'created_at') }}"
            >

            <input
                type="hidden"
                name="direction"
                id="jobDirectionField"
                value="{{ request('direction', 'desc') }}"
            >

            <div class="job-search-control">
                <input
                    type="search"
                    name="search"
                    class="form-control"
                    placeholder="Search job order, client, worker..."
                    value="{{ request('search') }}"
                >
            </div>

            <select name="status" class="form-select job-filter-select" aria-label="Filter by status">
                <option value="">All statuses</option>
                <option value="scheduled" @selected(request('status') === 'scheduled')>Scheduled</option>
                <option value="in_progress" @selected(request('status') === 'in_progress')>In Progress</option>
                <option value="completed" @selected(request('status') === 'completed')>Completed</option>
                <option value="cancelled" @selected(request('status') === 'cancelled')>Cancelled</option>
            </select>

            <select name="job_type" class="form-select job-filter-select" aria-label="Filter by job order type">
                <option value="">All JO Types</option>
                <option value="inspection" @selected(request('job_type') === 'inspection')>Inspection JO (IJO)</option>
                <option value="service" @selected(request('job_type') === 'service')>Service JO (SJO)</option>
                <option value="legacy" @selected(request('job_type') === 'legacy')>Legacy JO (JO)</option>
            </select>

            <select name="year" class="form-select job-filter-select" aria-label="Filter by year">
                <option value="">All Years</option>
                @foreach (($years ?? collect()) as $yearOption)
                    <option value="{{ $yearOption }}" @selected((string) request('year') === (string) $yearOption)>
                        {{ $yearOption }}
                    </option>
                @endforeach
            </select>

            <button class="btn btn-primary job-filter-submit" type="submit">
                <i class="fas fa-filter me-1"></i> Apply
            </button>

            <button class="btn btn-outline-secondary job-filter-reset" type="button" id="jobResetFilters">
                Reset
            </button>
        </form>
    </section>

    <section class="job-layout">
        <div class="job-main-panel" id="jobListContainer">
            @include('admin.job-orders.partials.job-list', [
                'jobOrders' => $jobOrders,
            ])
        </div>
    </section>
</div>

<script>
(function () {
    function initJobOrdersAjax() {
        const form = document.getElementById('jobFilterForm');
        const list = document.getElementById('jobListContainer');
        const stats = document.getElementById('jobStatsContainer');
        const reset = document.getElementById('jobResetFilters');
        const search = form ? form.querySelector('input[name="search"]') : null;
        const sortField = document.getElementById('jobSortField');
        const directionField = document.getElementById('jobDirectionField');

        if (!form || !list || !stats || !sortField || !directionField) {
            return;
        }

        let searchTimer = null;
        let requestController = null;

        function buildUrl() {
            const url = new URL(form.action, window.location.origin);
            const formData = new FormData(form);

            for (const [key, rawValue] of formData.entries()) {
                const value = String(rawValue).trim();

                if (value !== '') {
                    url.searchParams.set(key, value);
                }
            }

            return url;
        }

        async function refreshJobs(url = buildUrl()) {
            if (requestController) {
                requestController.abort();
            }

            requestController = new AbortController();

            list.classList.add('is-loading');
            stats.classList.add('is-loading');

            try {
                const response = await fetch(url.toString(), {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    signal: requestController.signal
                });

                if (!response.ok) {
                    throw new Error('AJAX request failed: ' + response.status);
                }

                const data = await response.json();

                if (!data.list_html || !data.stats_html) {
                    throw new Error('Invalid AJAX response.');
                }

                list.innerHTML = data.list_html;
                stats.innerHTML = data.stats_html;

                sortField.value = url.searchParams.get('sort') || 'created_at';
                directionField.value = url.searchParams.get('direction') || 'desc';

                window.history.replaceState({}, '', url.toString());
            } catch (error) {
                if (error.name !== 'AbortError') {
                    console.error('Job Orders AJAX:', error);
                }
            } finally {
                list.classList.remove('is-loading');
                stats.classList.remove('is-loading');
            }
        }

        if (search) {
            search.addEventListener('input', function () {
                clearTimeout(searchTimer);

                searchTimer = setTimeout(function () {
                    refreshJobs();
                }, 80);
            });

            search.addEventListener('search', function () {
                refreshJobs();
            });
        }

        form.querySelectorAll(
            'select[name="status"], select[name="job_type"], select[name="year"]'
        ).forEach(function (select) {
            select.addEventListener('change', function () {
                refreshJobs();
            });
        });

        form.addEventListener('submit', function (event) {
            event.preventDefault();
            refreshJobs();
        });

        if (reset) {
            reset.addEventListener('click', function () {
                form.reset();
                sortField.value = 'created_at';
                directionField.value = 'desc';
                refreshJobs();
            });
        }

        document.addEventListener('click', function (event) {
            const sortButton = event.target.closest(
                '#jobListContainer button[data-sort-column]'
            );

            if (sortButton) {
                event.preventDefault();
                event.stopPropagation();

                const clickedColumn = sortButton.dataset.sortColumn;
                const currentColumn = sortField.value || 'created_at';
                const currentDirection = directionField.value || 'desc';

                sortField.value = clickedColumn;

                if (clickedColumn === currentColumn) {
                    directionField.value =
                        currentDirection === 'asc' ? 'desc' : 'asc';
                } else {
                    directionField.value = 'asc';
                }

                refreshJobs();
                return;
            }

            const paginationLink = event.target.closest(
                '#jobListContainer .pagination a'
            );

            if (paginationLink) {
                event.preventDefault();
                event.stopPropagation();

                refreshJobs(
                    new URL(paginationLink.href, window.location.origin)
                );
            }
        }, true);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initJobOrdersAjax);
    } else {
        initJobOrdersAjax();
    }
})();
</script>
@endsection
