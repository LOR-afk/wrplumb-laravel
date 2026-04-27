@extends('client.layouts.app')

@section('title', 'My Alerts - WRPlumb')
@section('topbar_title', 'My Alerts')
@section('topbar_subtitle', 'Recent updates about your requests.')

@section('content')
<div class="page-header">
    <h1>My Alerts</h1>
    <p>Track updates about request assignment, progress, and completion.</p>
</div>

<div class="panel">
    <div class="panel-body">
        @forelse ($alerts as $alert)
            <div class="border rounded-4 p-3 mb-3">
                <div class="fw-bold">{{ $alert->title }}</div>
                <div class="text-muted small mb-2">{{ $alert->created_at->format('M d, Y h:i A') }}</div>
                <div class="mb-2">{{ $alert->message }}</div>

                @if ($alert->link)
                    <a href="{{ $alert->link }}" class="btn btn-sm btn-primary">Open</a>
                @endif
            </div>
        @empty
            <div class="text-center text-muted py-5">No alerts yet.</div>
        @endforelse

        {{ $alerts->links() }}
    </div>
</div>
@endsection