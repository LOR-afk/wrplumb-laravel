@extends('inspector.layouts.app')

@section('title', 'Inspector Availability - WRPlumb')
@section('topbar_title', 'Availability Schedule')
@section('topbar_subtitle', 'Set your available dates and duty schedule.')

@section('content')
<div class="page-header">
    <h1>Set Your Duty Schedule</h1>
    <p>Set your duty status so Admin can view when you are available for assignment.</p>
</div>

<div class="panel mb-4">
    <div class="panel-header">
        <h5><i class="fas fa-calendar-check me-2 text-primary"></i>Add Availability</h5>
    </div>
    <div class="panel-body">
        <form method="POST" action="{{ route('inspector.availability.store') }}">
            @csrf

            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Date</label>
                    <input type="date" name="availability_date" class="form-control" required>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Start Time</label>
                    <input type="time" name="start_time" class="form-control">
                </div>

                <div class="col-md-2">
                    <label class="form-label">End Time</label>
                    <input type="time" name="end_time" class="form-control">
                </div>

                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select" required>
                        <option value="available">Available</option>
                        <option value="on_duty">On Duty</option>
                        <option value="off_duty">Off Duty</option>
                        <option value="on_leave">On Leave</option>
                    </select>
                </div>

                <div class="col-md-12">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" class="form-control" rows="3" placeholder="Optional notes..."></textarea>
                </div>

                <div class="col-md-12">
                    <button class="btn btn-primary">
                        <i class="fas fa-floppy-disk me-2"></i>Save Availability
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="panel">
    <div class="panel-header">
        <h5><i class="fas fa-table-list me-2 text-primary"></i>My Availability Records</h5>
    </div>
    <div class="panel-body">
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Start</th>
                        <th>End</th>
                        <th>Status</th>
                        <th>Notes</th>
                        <th width="120">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($availabilities as $availability)
                        <tr>
                            <td>{{ $availability->availability_date->format('Y-m-d') }}</td>
                            <td>{{ $availability->start_time ?? '—' }}</td>
                            <td>{{ $availability->end_time ?? '—' }}</td>
                            <td>
                                @if ($availability->status === 'available')
                                    <span class="badge-soft green">Available</span>
                                @elseif ($availability->status === 'on_duty')
                                    <span class="badge-soft blue">On Duty</span>
                                @elseif ($availability->status === 'off_duty')
                                    <span class="badge-soft orange">Off Duty</span>
                                @else
                                    <span class="badge-soft gray">On Leave</span>
                                @endif
                            </td>
                            <td>{{ $availability->notes ?? '—' }}</td>
                            <td>
                                <form method="POST" action="{{ route('inspector.availability.destroy', $availability) }}" onsubmit="return confirm('Delete this availability?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div class="d-flex flex-column align-items-center justify-content-center text-muted">
                                    <div class="mb-3" style="width:64px;height:64px;border-radius:18px;background:#eef6ff;color:#1d9bf0;display:flex;align-items:center;justify-content:center;font-size:24px;">
                                        <i class="fas fa-calendar-xmark"></i>
                                    </div>
                                    <div class="fw-bold text-dark mb-1">No availability records yet</div>
                                    <div class="text-muted">Your saved availability schedule will appear here.</div>
                                </div>
                            </td>
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