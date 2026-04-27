@extends('admin.layouts.app')

@section('title', 'Inspector Availability - WRPlumb')
@section('topbar_title', 'Inspector Availability')
@section('topbar_subtitle', 'Check which inspectors are available for assignment.')

@section('content')
<div class="page-header">
    <h1>Inspector Availability</h1>
    <p>View the duty schedule and availability of all inspectors.</p>
</div>

<div class="panel mb-4">
    <div class="panel-header">Filter</div>
    <div class="panel-body">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Date</label>
                <input type="date" name="availability_date" class="form-control" value="{{ request('availability_date') }}">
            </div>

            <div class="col-md-4">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All</option>
                    <option value="available" @selected(request('status') === 'available')>Available</option>
                    <option value="on_duty" @selected(request('status') === 'on_duty')>On Duty</option>
                    <option value="off_duty" @selected(request('status') === 'off_duty')>Off Duty</option>
                    <option value="on_leave" @selected(request('status') === 'on_leave')>On Leave</option>
                </select>
            </div>

            <div class="col-md-4 d-flex align-items-end">
                <button class="btn btn-primary me-2">Apply Filter</button>
                <a href="{{ route('admin.inspectors.availability') }}" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="panel">
    <div class="panel-header">Inspector Schedule</div>
    <div class="panel-body">
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Inspector</th>
                        <th>Date</th>
                        <th>Start</th>
                        <th>End</th>
                        <th>Status</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($availabilities as $availability)
                        <tr>
                            <td>{{ $availability->inspector->name }}</td>
                            <td>{{ $availability->availability_date->format('Y-m-d') }}</td>
                            <td>{{ $availability->start_time ?? '—' }}</td>
                            <td>{{ $availability->end_time ?? '—' }}</td>
                            <td>{{ ucfirst(str_replace('_', ' ', $availability->status)) }}</td>
                            <td>{{ $availability->notes ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">No availability records found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $availabilities->links() }}
        </div>
    </div>
</div>
@endsection