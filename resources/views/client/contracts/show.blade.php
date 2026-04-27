@extends('client.layouts.app')

@section('title', 'Contract Details')

@section('content')
<style>
    .contract-wrapper {
        background: #fff;
        border: 1px solid #dbe5f1;
        border-radius: 20px;
        padding: 32px;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.05);
    }

    .contract-page-heading {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 16px;
        flex-wrap: wrap;
        margin-bottom: 18px;
    }

    .contract-page-heading h1 {
        margin: 0;
        font-size: 32px;
        font-weight: 700;
        color: #0f172a;
    }

    .contract-page-heading p {
        margin: 6px 0 0;
        color: #64748b;
        font-size: 14px;
    }

    .contract-header-meta {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
    }

    .contract-number-box,
    .contract-status-box {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 12px 16px;
        min-width: 160px;
        box-shadow: 0 4px 14px rgba(15, 23, 42, 0.04);
    }

    .contract-number-box .label,
    .contract-status-box .label {
        display: block;
        font-size: 12px;
        color: #64748b;
        margin-bottom: 4px;
    }

    .contract-number-box strong {
        font-size: 15px;
        color: #0f172a;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 6px 12px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: 0.4px;
    }

    .status-generated {
        background: #e0f2fe;
        color: #0369a1;
    }

    .status-sent {
        background: #fef3c7;
        color: #92400e;
    }

    .status-accepted {
        background: #dcfce7;
        color: #166534;
    }

    .status-finalized {
        background: #ede9fe;
        color: #6d28d9;
    }

    .status-cancelled {
        background: #fee2e2;
        color: #b91c1c;
    }

    .contract-header {
        display: flex;
        justify-content: space-between;
        align-items: start;
        gap: 20px;
        border-bottom: 2px solid #e2e8f0;
        padding-bottom: 20px;
        margin-bottom: 24px;
    }

    .contract-brand h2 {
        margin: 0;
        font-weight: 800;
        font-size: 1.5rem;
    }

    .contract-brand p {
        margin: 6px 0 0;
        color: #64748b;
    }

    .contract-title {
        text-align: center;
        margin-bottom: 24px;
    }

    .contract-title h3 {
        margin: 0;
        font-size: 1.4rem;
        font-weight: 800;
        letter-spacing: 0.04em;
    }

    .contract-meta {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 16px;
        margin-bottom: 24px;
    }

    .meta-box,
    .section-box {
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 18px;
        background: #f8fafc;
    }

    .meta-label,
    .section-label {
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #64748b;
        margin-bottom: 8px;
        font-weight: 700;
    }

    .meta-value,
    .section-value {
        color: #0f172a;
        font-weight: 600;
        line-height: 1.6;
    }

    .contract-sections {
        display: grid;
        gap: 18px;
        margin-bottom: 24px;
    }

    .payment-phase {
        display: flex;
        justify-content: space-between;
        gap: 16px;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 14px 16px;
        margin-bottom: 10px;
        background: #fff;
    }

    .payment-phase:last-child {
        margin-bottom: 0;
    }

    .items-table {
        width: 100%;
        border-collapse: collapse;
    }

    .items-table th,
    .items-table td {
        border: 1px solid #e2e8f0;
        padding: 10px 12px;
        text-align: left;
        vertical-align: top;
    }

    .items-table th {
        background: #f8fafc;
        font-weight: 700;
    }

    .signature-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 40px;
        margin-top: 36px;
    }

    .signature-box {
        text-align: center;
    }

    .signature-line {
        border-top: 1px solid #0f172a;
        margin-top: 60px;
        padding-top: 8px;
        font-weight: 600;
    }

    .print-actions {
        display: flex;
        gap: 12px;
        margin-bottom: 20px;
        flex-wrap: wrap;
    }

    @page {
        margin: 12mm;
    }

    @media (max-width: 768px) {
        .contract-meta {
            grid-template-columns: 1fr;
        }

        .contract-header {
            flex-direction: column;
        }

        .contract-page-heading {
            flex-direction: column;
        }

        .contract-header-meta {
            width: 100%;
        }

        .contract-number-box,
        .contract-status-box {
            width: 100%;
        }

        .signature-grid {
            grid-template-columns: 1fr;
        }

        .contract-wrapper {
            padding: 20px;
        }
    }

    @media print {
        body * {
            visibility: hidden;
        }

        .contract-wrapper,
        .contract-wrapper * {
            visibility: visible;
        }

        .contract-wrapper {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            margin: 0;
            padding: 0;
            box-shadow: none !important;
            border: none !important;
            background: #fff !important;
        }

        .no-print {
            display: none !important;
        }

        body {
            background: #fff !important;
        }
    }
</style>

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