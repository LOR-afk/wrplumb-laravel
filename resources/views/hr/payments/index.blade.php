@extends('hr.layouts.app')

@section('title', 'Payments')

@section('content')
<div class="page-header-card mb-4">
    <h2 class="mb-1">Payments</h2>
    <p class="text-muted mb-0">View and manage recorded payments.</p>
</div>

@if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Payment No.</th>
                        <th>Invoice No.</th>
                        <th>Client</th>
                        <th>Schedule</th>
                        <th>Amount</th>
                        <th>Method</th>
                        <th>Status</th>
                        <th>Payment Date</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($payments as $payment)
                        <tr>
                            <td class="fw-semibold">{{ $payment->payment_no }}</td>
                            <td>{{ $payment->invoice->invoice_no ?? '—' }}</td>
                            <td>{{ $payment->invoice->quotation->request->full_name ?? '—' }}</td>
                            <td>{{ $payment->paymentSchedule->label ?? '—' }}</td>
                            <td>PHP {{ number_format((float) $payment->amount, 2) }}</td>
                            <td>{{ $payment->payment_method ?? '—' }}</td>
                            <td>
                                <span class="badge bg-secondary text-uppercase">
                                    {{ $payment->status }}
                                </span>
                            </td>
                            <td>{{ optional($payment->payment_date)->format('M d, Y') }}</td>
                            <td class="text-end">
                                <a href="{{ route('hr.payments.show', $payment) }}" class="btn btn-sm btn-outline-primary">
                                    View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">No payments found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(method_exists($payments, 'links'))
            <div class="p-3">
                {{ $payments->links() }}
            </div>
        @endif
    </div>
</div>
@endsection