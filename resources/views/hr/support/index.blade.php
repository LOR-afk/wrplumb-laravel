@extends('hr.layouts.app')

@section('title', 'HR Support Queue - WRPlumb')
@section('topbar_title', 'Support Queue')
@section('topbar_subtitle', 'Review and handle concerns routed to HR.')

@section('content')
<div class="page-header">
    <h1>HR Support Queue</h1>
    <p>Review client concerns routed by the support workflow and provide the appropriate response.</p>
</div>

<div class="panel">
    <div class="panel-header">
        <h5><i class="fas fa-comments me-2 text-primary"></i>Queued Conversations</h5>
    </div>
    <div class="panel-body">
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Client</th>
                        <th>Status</th>
                        <th>Routed To</th>
                        <th>Created</th>
                        <th width="120">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($conversations as $conversation)
                        <tr>
                            <td>
                                <div class="fw-bold text-dark">{{ $conversation->client->name }}</div>
                                <div class="text-muted small">{{ $conversation->client->email }}</div>
                            </td>
                            <td>
                                @if ($conversation->status === 'routed')
                                    <span class="badge-soft orange">Routed</span>
                                @elseif ($conversation->status === 'open')
                                    <span class="badge-soft green">Open</span>
                                @elseif ($conversation->status === 'resolved')
                                    <span class="badge-soft gray">Resolved</span>
                                @else
                                    <span class="badge-soft red">{{ ucfirst($conversation->status) }}</span>
                                @endif
                            </td>
                            <td>{{ $conversation->routed_to ?? 'hr' }}</td>
                            <td>{{ $conversation->created_at->format('Y-m-d h:i A') }}</td>
                            <td>
                                <a href="{{ route('hr.support.show', $conversation) }}" class="btn btn-sm btn-primary">
                                    Open
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <div class="d-flex flex-column align-items-center justify-content-center text-muted">
                                    <div class="mb-3" style="width:64px;height:64px;border-radius:18px;background:#eef6ff;color:#1d9bf0;display:flex;align-items:center;justify-content:center;font-size:24px;">
                                        <i class="fas fa-headset"></i>
                                    </div>
                                    <div class="fw-bold text-dark mb-1">No HR-routed conversations yet</div>
                                    <div class="text-muted">New concerns sent to HR will appear here.</div>
                                </div>
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