@extends('hr.layouts.app')

@section('title', 'HR Reports')

@section('content')
<div class="page-header-card mb-4">
    <h2 class="mb-1">HR Reports</h2>
    <p class="text-muted mb-0">Generate income reports and export payment records to Excel.</p>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body">
                <div class="small text-muted fw-bold">Total Income</div>
                <h4 class="fw-bold mb-0">PHP {{ number_format((float) $summary['total_income'], 2) }}</h4>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body">
                <div class="small text-muted fw-bold">Confirmed Payments</div>
                <h4 class="fw-bold mb-0">{{ $summary['confirmed_count'] }}</h4>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body">
                <div class="small text-muted fw-bold">Rejected Payments</div>
                <h4 class="fw-bold mb-0">{{ $summary['rejected_count'] }}</h4>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body">
                <div class="small text-muted fw-bold">Receipts Issued</div>
                <h4 class="fw-bold mb-0">{{ $summary['receipt_count'] }}</h4>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body">
        <h5 class="fw-bold mb-3">
            <i class="fas fa-file-excel text-success me-2"></i>Export Income Report
        </h5>

        <form method="GET" action="{{ route('hr.reports.income.export') }}" class="row g-3">
            <div class="col-md-2">
                <label class="form-label">Year</label>
                <input type="number" name="year" class="form-control" value="{{ $year }}" min="2020" max="2100">
            </div>

            <div class="col-md-2">
                <label class="form-label">Month</label>
                <select name="month" class="form-select">
                    <option value="">All Months</option>
                    @foreach (range(1, 12) as $m)
                        <option value="{{ $m }}" @selected((string) $month === (string) $m)>
                            {{ DateTime::createFromFormat('!m', $m)->format('F') }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="confirmed">Confirmed</option>
                    <option value="pending">Pending</option>
                    <option value="pending_verification">Pending Verification</option>
                    <option value="rejected">Rejected</option>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label">Payment Method</label>
                <select name="method" class="form-select">
                    <option value="">All Methods</option>
                    <option value="Cash">Cash</option>
                    <option value="GCash">GCash</option>
                    <option value="Bank Transfer">Bank Transfer</option>
                </select>
            </div>

            <div class="col-md-3 d-flex align-items-end">
                <button class="btn btn-success w-100">
                    <i class="fas fa-download me-2"></i>Export Excel
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
