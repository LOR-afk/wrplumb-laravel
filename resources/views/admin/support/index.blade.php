@extends('admin.layouts.app')

@section('title', 'Support Requests - WRPlumb')
@section('topbar_title', 'Support Requests')
@section('topbar_subtitle', 'Review concerns escalated to Admin and respond promptly.')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin/support.css') }}">
@endpush

@section('content')
<div class="page-header">
    <h1>Support Queue</h1>
    <p>Monitor conversations routed to Admin and manage escalated client concerns.</p>
</div>

<div class="panel mb-4 support-filter-panel">
    <div class="panel-header">
        <h5><i class="fas fa-filter me-2 text-primary"></i>Filter Support Queue</h5>
    </div>
    <div class="panel-body">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Search Client</label>
                <input
                    type="text"
                    name="search"
                    class="form-control"
                    placeholder="Name, username, or email..."
                    value="{{ request('search') }}"
                >
            </div>

            <div class="col-md-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All</option>
                    <option value="open" @selected(request('status') === 'open')>Open</option>
                    <option value="routed" @selected(request('status') === 'routed')>Routed</option>
                    <option value="resolved" @selected(request('status') === 'resolved')>Resolved</option>
                </select>
            </div>

            <div class="col-md-5 d-flex align-items-end">
                <button class="btn btn-primary me-2">
                    <i class="fas fa-sliders me-2"></i>Apply Filter
                </button>
                <a href="{{ route('admin.support.index') }}" class="btn btn-outline-secondary">
                    Reset
                </a>
            </div>
        </form>
    </div>
</div>

<div class="panel">
    <div class="panel-header">
        <h5><i class="fas fa-comments me-2 text-primary"></i>Admin Support Queue</h5>
    </div>

    <div class="panel-body">
        <div class="table-responsive">
            <table class="table align-middle support-queue-table">
                <thead>
                    <tr>
                        <th>Client</th>
                        <th>Status</th>
                        <th>Queue</th>
                        <th>Messages</th>
                        <th>Created</th>
                        <th>Routed At</th>
                        <th width="120">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($conversations as $conversation)
                        <tr>
                            <td>
                                <div class="client-name">{{ $conversation->client->name }}</div>
                                <div class="client-email">{{ $conversation->client->email }}</div>
                            </td>

                            <td>
                                @if ($conversation->status === 'open')
                                    <span class="badge-soft green">Open</span>
                                @elseif ($conversation->status === 'routed')
                                    <span class="badge-soft orange">Routed</span>
                                @elseif ($conversation->status === 'resolved')
                                    <span class="badge-soft gray">Resolved</span>
                                @else
                                    <span class="badge-soft red">{{ ucfirst($conversation->status) }}</span>
                                @endif
                            </td>

                            <td>
                                @if ($conversation->current_queue === 'admin')
                                    <span class="badge-soft blue">Admin</span>
                                @elseif ($conversation->current_queue === 'resolved')
                                    <span class="badge-soft gray">Resolved</span>
                                @else
                                    <span class="badge-soft orange">{{ ucfirst($conversation->current_queue) }}</span>
                                @endif
                            </td>

                            <td>
                                <span class="support-message-count">{{ $conversation->messages_count }}</span>
                            </td>
                            <td>{{ $conversation->created_at->format('Y-m-d h:i A') }}</td>
                            <td>{{ optional($conversation->routed_at)->format('Y-m-d h:i A') ?? '—' }}</td>
                            <td>
                                <a href="{{ route('admin.support.show', $conversation) }}" class="btn btn-sm btn-primary support-open-btn">
                                    Open
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">
                                No support requests found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $conversations->links() }}
        </div>
    </div>
</div>
@endsection
