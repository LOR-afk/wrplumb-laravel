@extends('admin.layouts.app')

@section('title', 'Manage Clients - WRPlumb')
@section('topbar_title', 'Manage Clients')
@section('topbar_subtitle', 'View, search, and control client account status.')

@section('content')
<div class="page-header">
    <h1>Client Accounts</h1>
    <p>Manage registered client records and activate or deactivate access.</p>
</div>

<div class="panel">
    <div class="panel-header">
        <h5><i class="fas fa-users me-2 text-primary"></i>Registered Clients</h5>
    </div>
    <div class="panel-body">
        <form method="GET" class="mb-4">
            <div class="row g-2">
                <div class="col-md-8">
                    <input type="text" name="search" class="form-control" placeholder="Search by name, email, or username" value="{{ request('search') }}">
                </div>
                <div class="col-md-4">
                    <button class="btn btn-primary btn-clean w-100">Search</button>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Status</th>
                        <th width="170">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($clients as $client)
                        <tr>
                            <td>{{ $client->name }}</td>
                            <td>{{ $client->username }}</td>
                            <td>{{ $client->email }}</td>
                            <td>{{ $client->phone }}</td>
                            <td>
                                @if ($client->is_active)
                                    <span class="badge-soft badge-active">Active</span>
                                @else
                                    <span class="badge-soft badge-inactive">Inactive</span>
                                @endif
                            </td>
                            <td>
                                <form method="POST" action="{{ route('admin.clients.toggle-status', $client) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button class="btn btn-sm {{ $client->is_active ? 'btn-warning' : 'btn-success' }} btn-clean w-100">
                                        {{ $client->is_active ? 'Deactivate' : 'Activate' }}
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">No client records found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $clients->links() }}
        </div>
    </div>
</div>
@endsection