@extends('client.layouts.app')

@section('title', 'Contract Details')
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/client/contracts.css') }}">
@endpush
@section('content')


<div class="page-heading contract-page-heading no-print">
    <div>
        <h1>Contract Details</h1>
        <p>Review and print your service contract.</p>
    </div>

    <div class="contract-header-meta">
        <div class="contract-number-box">
            <span class="label">Contract No.</span>
            <strong>{{ $contract->contract_no }}</strong>
        </div>

        <div class="contract-status-box">
            <span class="label">Status</span>
            <span class="status-badge status-{{ strtolower($contract->status) }}">
                {{ strtoupper($contract->status) }}
            </span>
        </div>
    </div>
</div>

@if (session('success'))
    <div class="alert alert-success no-print">{{ session('success') }}</div>
@endif

@if ($errors->any())
    <div class="alert alert-danger no-print">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="print-actions no-print">
    <a href="{{ route('client.contracts.index') }}" class="btn btn-outline-secondary">Back to My Contracts</a>
    <a href="{{ route('client.quotations.show', $contract->quotation) }}" class="btn btn-outline-primary">View Quotation</a>

    @if (!in_array($contract->status, ['accepted', 'finalized', 'cancelled']))
        <form method="POST" action="{{ route('client.contracts.accept', $contract) }}">
            @csrf
            <button type="submit" class="btn btn-success">Accept Contract</button>
        </form>
    @endif

    <button type="button" class="btn btn-dark" onclick="window.print()">Print Contract</button>
</div>

<div class="contract-wrapper">
    <div class="contract-header">
        <div class="contract-brand">
            <h2>WR Plumbing and Construction Services</h2>
            <p>Service Contract Agreement</p>
        </div>

        <div class="text-end">
            <div class="meta-label">Contract No.</div>
            <div class="meta-value">{{ $contract->contract_no }}</div>

            <div class="meta-label mt-3">Status</div>
            <div class="meta-value text-uppercase">{{ $contract->status }}</div>
        </div>
    </div>

    <div class="contract-title">
        <h3>{{ strtoupper($contract->title ?? 'SERVICE CONTRACT AGREEMENT') }}</h3>
    </div>

    <div class="contract-meta">
        <div class="meta-box">
            <div class="meta-label">Client Name</div>
            <div class="meta-value">{{ $contract->client_name }}</div>
        </div>

        <div class="meta-box">
            <div class="meta-label">Contract Date</div>
            <div class="meta-value">{{ optional($contract->contract_date)->format('F d, Y') ?? '—' }}</div>
        </div>

        <div class="meta-box">
            <div class="meta-label">Client Address</div>
            <div class="meta-value">{{ $contract->client_address ?: '—' }}</div>
        </div>

        <div class="meta-box">
            <div class="meta-label">Project Address</div>
            <div class="meta-value">{{ $contract->project_address ?: '—' }}</div>
        </div>

        <div class="meta-box">
            <div class="meta-label">Start Date</div>
            <div class="meta-value">{{ optional($contract->start_date)->format('F d, Y') ?? 'To be agreed' }}</div>
        </div>

        <div class="meta-box">
            <div class="meta-label">End Date</div>
            <div class="meta-value">{{ optional($contract->end_date)->format('F d, Y') ?? 'To be agreed' }}</div>
        </div>

        <div class="meta-box">
            <div class="meta-label">Client Accepted At</div>
            <div class="meta-value">{{ optional($contract->client_accepted_at)->format('F d, Y h:i A') ?? 'Not yet accepted' }}</div>
        </div>

        <div class="meta-box">
            <div class="meta-label">Finalized At</div>
            <div class="meta-value">{{ optional($contract->finalized_at)->format('F d, Y h:i A') ?? 'Not yet finalized' }}</div>
        </div>
    </div>

    <div class="contract-sections">
        <div class="section-box">
            <div class="section-label">Service / Project Basis</div>
            <div class="section-value">
                {{ $contract->quotation->quotation_no }} —
                {{ $contract->quotation->request->service_category ?? 'Service' }}
                /
                {{ $contract->quotation->request->service_type ?? 'Project' }}
            </div>
        </div>

        <div class="section-box">
            <div class="section-label">Scope of Work</div>
            <div class="section-value">{{ $contract->scope_of_work ?: '—' }}</div>
        </div>

        @if (!empty($contract->special_terms))
            <div class="section-box">
                <div class="section-label">Special Terms and Conditions</div>
                <div class="section-value">{{ $contract->special_terms }}</div>
            </div>
        @endif

        <div class="section-box">
            <div class="section-label">Contract Price</div>
            <div class="section-value">PHP {{ number_format((float) $contract->total_contract_price, 2) }}</div>
        </div>

        <div class="section-box">
            <div class="section-label">Payment Terms</div>

            @if (!empty($contract->payment_terms['phases']))
                @foreach ($contract->payment_terms['phases'] as $phase)
                    <div class="payment-phase">
                        <div>
                            <div class="fw-semibold">{{ $phase['label'] }}</div>
                            <div class="text-muted small">{{ $phase['percent'] }}%</div>
                        </div>
                        <div class="fw-semibold">
                            PHP {{ number_format((float) $phase['amount'], 2) }}
                        </div>
                    </div>
                @endforeach
            @else
                <div class="section-value">No payment terms available.</div>
            @endif
        </div>

        <div class="section-box">
            <div class="section-label">Quotation Item Basis</div>

            <div class="table-responsive">
                <table class="items-table">
                    <thead>
                        <tr>
                            <th>Description</th>
                            <th>Category</th>
                            <th>Qty</th>
                            <th>Unit Price</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($contract->quotation->items as $item)
                            <tr>
                                <td>{{ $item->description }}</td>
                                <td class="text-capitalize">{{ $item->item_category }}</td>
                                <td>{{ number_format((float) $item->quantity, 2) }}</td>
                                <td>PHP {{ number_format((float) $item->unit_price, 2) }}</td>
                                <td>PHP {{ number_format((float) $item->total_price, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="section-box">
            <div class="section-label">Agreement Statement</div>
            <div class="section-value">
                This contract serves as the formal agreement between WR Plumbing and Construction Services and the client named above for the completion of the specified work under the agreed quotation, payment terms, and conditions stated herein. Any variation in scope, materials, labor, or timeline shall be subject to further agreement by both parties.
            </div>
        </div>
    </div>

    <div class="section-label">Signatures</div>

    <div class="signature-grid">
        <div class="signature-box">
            <div class="signature-line">Client Signature / Printed Name</div>
        </div>

        <div class="signature-box">
            <div class="signature-line">Authorized Representative / Printed Name</div>
        </div>
    </div>
</div>
@endsection