<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Quotation - WRPlumb</title>

    <link rel="icon" type="image/png" href="{{ asset('image/logo.png') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/common.css') }}">

    <style>
        body {
            background: #f5f8fc;
        }

        .public-quotation-page {
            min-height: 100vh;
            padding: 32px 16px;
        }

        .quotation-card {
            max-width: 900px;
            margin: 0 auto;
            background: #ffffff;
            border: 1px solid var(--wr-border);
            border-radius: 24px;
            box-shadow: var(--wr-shadow);
            overflow: hidden;
        }

        .quotation-header {
            padding: 28px;
            border-bottom: 1px solid var(--wr-border);
            background: linear-gradient(135deg, #ffffff, #f7fbff);
        }

        .quotation-title {
            font-size: 1.8rem;
            font-weight: 900;
            color: #0f172a;
            margin-bottom: 6px;
        }

        .quotation-subtitle {
            color: var(--wr-muted);
            margin: 0;
        }

        .quotation-body {
            padding: 28px;
        }

        .info-box {
            border: 1px solid #edf2f7;
            border-radius: 18px;
            padding: 16px;
            background: #fbfdff;
            height: 100%;
        }

        .info-label {
            font-size: 0.78rem;
            text-transform: uppercase;
            font-weight: 900;
            color: var(--wr-muted);
            letter-spacing: 0.04em;
            margin-bottom: 4px;
        }

        .info-value {
            font-weight: 800;
            color: #0f172a;
        }

        .total-box {
            background: #eaf4ff;
            color: #0f4c81;
            border-radius: 18px;
            padding: 18px;
            text-align: right;
        }

        .total-label {
            font-weight: 800;
            color: #475569;
        }

        .total-value {
            font-size: 1.7rem;
            font-weight: 950;
        }

        .action-footer {
            padding: 22px 28px;
            background: #fbfdff;
            border-top: 1px solid var(--wr-border);
        }
    </style>
</head>
<body>
    <div class="public-quotation-page">
        <div class="quotation-card">
            <div class="quotation-header">
                <div class="quotation-title">WRPlumb Service Quotation</div>
                <p class="quotation-subtitle">
                    Please review the quotation details and confirm your response.
                </p>
            </div>

            <div class="quotation-body">
                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                @if (session('info'))
                    <div class="alert alert-info">{{ session('info') }}</div>
                @endif

                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="info-box">
                            <div class="info-label">Quotation No.</div>
                            <div class="info-value">{{ $quotation->quotation_no }}</div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="info-box">
                            <div class="info-label">Client</div>
                            <div class="info-value">
                                {{ $quotation->request->full_name ?? $quotation->request->email ?? '—' }}
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="info-box">
                            <div class="info-label">Service Type</div>
                            <div class="info-value">
                                {{ $quotation->request->service_type ?? '—' }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="table-responsive mb-4">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>Description</th>
                                <th class="text-center">Qty</th>
                                <th class="text-end">Unit Price</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($quotation->items as $item)
                                <tr>
                                    <td>{{ $item->description }}</td>
                                    <td class="text-center">{{ $item->quantity }}</td>
                                    <td class="text-end">PHP {{ number_format((float) $item->unit_price, 2) }}</td>
                                    <td class="text-end">PHP {{ number_format((float) $item->total_price, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">
                                        No quotation items found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="total-box mb-4">
                    <div class="total-label">Grand Total</div>
                    <div class="total-value">
                        PHP {{ number_format((float) $quotation->grand_total, 2) }}
                    </div>
                </div>

                @if ($quotation->client_response)
                    <div class="alert alert-info mb-0">
                        This quotation has already been
                        <strong>{{ strtoupper($quotation->client_response) }}</strong>.
                    </div>
                @endif
            </div>

            @if (!$quotation->client_response)
                <div class="action-footer">
                    <div class="d-flex flex-wrap gap-2 justify-content-end">
                        <form method="POST" action="{{ route('public.quotations.decline', $quotation->acceptance_token) }}">
                            @csrf
                            <button type="submit" class="btn btn-outline-secondary">
                                Decline Quotation
                            </button>
                        </form>

                        <form method="POST" action="{{ route('public.quotations.accept', $quotation->acceptance_token) }}">
                            @csrf
                            <button type="submit" class="btn btn-primary">
                                Accept Quotation
                            </button>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </div>
</body>
</html>