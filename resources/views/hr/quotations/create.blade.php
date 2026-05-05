@extends('hr.layouts.app')

@section('title', 'Create Quotation')

@section('content')
<div class="page-header-card mb-4">
    <h2 class="mb-1">Create Quotation</h2>
    <p class="text-muted mb-0">Prepare an itemized quotation for this service request.</p>
</div>

@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="quotation-request-card mb-4">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
        <div>
            <h5 class="fw-bold mb-1">Request Summary</h5>
            <p class="text-muted mb-0">Review client request details before preparing the quotation.</p>
        </div>
        <span class="request-service-chip"><i class="fas fa-tools me-1"></i>{{ $quotationRequest->service_type }}</span>
    </div>

    <div class="request-summary-grid">
        <div class="summary-box"><div class="summary-label">Client</div><div class="summary-value">{{ $quotationRequest->full_name }}</div></div>
        <div class="summary-box"><div class="summary-label">Email</div><div class="summary-value">{{ $quotationRequest->email }}</div></div>
        <div class="summary-box"><div class="summary-label">Phone</div><div class="summary-value">{{ $quotationRequest->phone }}</div></div>
        <div class="summary-box"><div class="summary-label">Service Category</div><div class="summary-value text-capitalize">{{ $quotationRequest->service_category }}</div></div>
        <div class="summary-box"><div class="summary-label">Service Type</div><div class="summary-value">{{ $quotationRequest->service_type }}</div></div>
        <div class="summary-box"><div class="summary-label">Project Type</div><div class="summary-value">{{ $quotationRequest->project_type }}</div></div>
        <div class="summary-box wide"><div class="summary-label">Address</div><div class="summary-value">{{ $quotationRequest->address }}</div></div>
        <div class="summary-box">
            <div class="summary-label">Appointment Schedule</div>
            <div class="summary-value">
                @if ($quotationRequest->appointment_date && $quotationRequest->appointment_time)
                    {{ date('M d, Y', strtotime($quotationRequest->appointment_date)) }} • {{ date('h:i A', strtotime($quotationRequest->appointment_time)) }}
                @else
                    Not yet scheduled
                @endif
            </div>
        </div>
        <div class="summary-box full"><div class="summary-label">Problem Details</div><div class="summary-value">{{ $quotationRequest->details }}</div></div>
    </div>
</div>

<form method="POST" action="{{ route('hr.quotations.store') }}" id="quotationForm">
    @csrf
    <input type="hidden" name="quotation_request_id" value="{{ $quotationRequest->id }}">

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="quotation-form-card">
                <div class="d-flex justify-content-between align-items-center mb-3 gap-2">
                    <div>
                        <h5 class="fw-bold mb-1">Quotation Items</h5>
                        <p class="text-muted mb-0 small">Add materials, labor, and miscellaneous costs.</p>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-primary quotation-round-btn" id="addItemBtn">
                        <i class="fas fa-plus me-1"></i>Add Item
                    </button>
                </div>

                <div id="quotationItemsContainer">
                    <div class="quotation-item-row">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Description</label>
                                <input type="text" name="items[0][description]" class="form-control item-description" required>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label">Category</label>
                                <select name="items[0][item_category]" class="form-select item-category" required>
                                    <option value="material">Material</option>
                                    <option value="labor">Labor</option>
                                    <option value="misc">Misc</option>
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label">Quantity</label>
                                <input type="number" name="items[0][quantity]" class="form-control item-quantity" step="0.01" min="0.01" value="1" required>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label">Unit Price</label>
                                <input type="number" name="items[0][unit_price]" class="form-control item-price" step="0.01" min="0" value="0" required>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label">Line Total</label>
                                <input type="text" class="form-control item-total-display" value="PHP 0.00" readonly>
                            </div>
                        </div>

                        <div class="mt-3 text-end">
                            <button type="button" class="btn btn-sm btn-outline-danger quotation-round-btn remove-item-btn">
                                <i class="fas fa-trash me-1"></i>Remove
                            </button>
                        </div>
                    </div>
                </div>

                <div class="mt-3">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" class="form-control" rows="4" placeholder="Optional quotation notes...">{{ old('notes') }}</textarea>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="quotation-cost-card">
                <h5 class="fw-bold mb-3">Cost Summary</h5>

                <div class="cost-line"><span>Materials</span><strong id="materialsTotal">PHP 0.00</strong></div>
                <div class="cost-line"><span>Labor</span><strong id="laborTotal">PHP 0.00</strong></div>
                <div class="cost-line"><span>Miscellaneous</span><strong id="miscTotal">PHP 0.00</strong></div>

                <hr>

                <div class="cost-line"><span>Subtotal</span><strong id="subtotalAmount">PHP 0.00</strong></div>

                <div class="mb-3">
                    <label class="form-label">Tax Rate (%)</label>
                    <input type="number" step="0.01" min="0" max="100" name="tax_rate" id="taxRate" class="form-control" value="{{ old('tax_rate', 0) }}">
                </div>

                <div class="cost-line"><span>Tax Amount</span><strong id="taxAmount">PHP 0.00</strong></div>
                <div class="grand-total-box"><span>Grand Total</span><strong id="grandTotal">PHP 0.00</strong></div>

                <div class="mt-3">
                    <label class="form-label">Payment Plan</label>
                    <select name="payment_plan" id="paymentPlan" class="form-select">
                        <option value="auto">Auto</option>
                        <option value="5050">50 / 50</option>
                        <option value="30303010">30 / 30 / 30 / 10</option>
                    </select>
                </div>

                <div class="mt-3">
                    <label class="form-label">Payment Breakdown</label>
                    <div id="paymentBreakdown" class="payment-breakdown-box">No payment breakdown yet.</div>
                </div>
            </div>

            <div class="d-grid gap-2 mt-4">
                <button type="submit" class="btn btn-primary quotation-submit-btn"><i class="fas fa-save me-1"></i>Save Draft Quotation</button>
                <a href="{{ route('hr.dashboard') }}" class="btn btn-outline-secondary quotation-submit-btn">Cancel</a>
            </div>
        </div>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const container = document.getElementById('quotationItemsContainer');
    const addItemBtn = document.getElementById('addItemBtn');
    const taxRateInput = document.getElementById('taxRate');
    const paymentPlan = document.getElementById('paymentPlan');

    let itemIndex = 1;

    function formatMoney(value) {
        return 'PHP ' + Number(value).toFixed(2);
    }

    function parseNumber(value) {
        const parsed = parseFloat(value);
        return Number.isFinite(parsed) ? parsed : 0;
    }

    function buildPaymentTerms(total, planKey) {
        let chosenPlan = planKey;

        if (chosenPlan === 'auto') {
            chosenPlan = total >= 100000 ? '30303010' : '5050';
        }

        const phases = chosenPlan === '30303010'
            ? [
                { label: 'Downpayment', percent: 30 },
                { label: 'Progress 1', percent: 30 },
                { label: 'Progress 2', percent: 30 },
                { label: 'Retention', percent: 10 },
            ]
            : [
                { label: 'Downpayment', percent: 50 },
                { label: 'Final', percent: 50 },
            ];

        let running = 0;

        return phases.map((phase, index) => {
            let amount;

            if (index === phases.length - 1) {
                amount = Math.max(0, total - running);
            } else {
                amount = (total * phase.percent) / 100;
                running += amount;
            }

            return {
                ...phase,
                amount: Math.round(amount * 100) / 100
            };
        });
    }

    function renderPaymentBreakdown(total) {
        const breakdown = document.getElementById('paymentBreakdown');
        const phases = buildPaymentTerms(total, paymentPlan.value);

        breakdown.innerHTML = phases.map(phase => `
            <div class="d-flex justify-content-between mb-2">
                <span>${phase.label} (${phase.percent}%)</span>
                <strong>${formatMoney(phase.amount)}</strong>
            </div>
        `).join('');
    }

    function recalculateTotals() {
        let materials = 0;
        let labor = 0;
        let misc = 0;

        document.querySelectorAll('.quotation-item-row').forEach(row => {
            const category = row.querySelector('.item-category').value;
            const quantity = parseNumber(row.querySelector('.item-quantity').value);
            const price = parseNumber(row.querySelector('.item-price').value);
            const total = quantity * price;

            row.querySelector('.item-total-display').value = formatMoney(total);

            if (category === 'material') materials += total;
            if (category === 'labor') labor += total;
            if (category === 'misc') misc += total;
        });

        const subtotal = materials + labor + misc;
        const taxRate = parseNumber(taxRateInput.value);
        const taxAmount = (subtotal * taxRate) / 100;
        const grandTotal = subtotal + taxAmount;

        document.getElementById('materialsTotal').textContent = formatMoney(materials);
        document.getElementById('laborTotal').textContent = formatMoney(labor);
        document.getElementById('miscTotal').textContent = formatMoney(misc);
        document.getElementById('subtotalAmount').textContent = formatMoney(subtotal);
        document.getElementById('taxAmount').textContent = formatMoney(taxAmount);
        document.getElementById('grandTotal').textContent = formatMoney(grandTotal);

        renderPaymentBreakdown(grandTotal);
    }

    function attachRowEvents(row) {
        row.querySelectorAll('.item-category, .item-quantity, .item-price').forEach(field => {
            field.addEventListener('input', recalculateTotals);
            field.addEventListener('change', recalculateTotals);
        });

        row.querySelector('.remove-item-btn').addEventListener('click', function () {
            const rows = document.querySelectorAll('.quotation-item-row');
            if (rows.length > 1) {
                row.remove();
                recalculateTotals();
            }
        });
    }

    addItemBtn.addEventListener('click', function () {
        const row = document.createElement('div');
        row.className = 'quotation-item-row';
        row.innerHTML = `
            <div class="row g-3">
                <div class="col-md-4"><label class="form-label">Description</label><input type="text" name="items[${itemIndex}][description]" class="form-control item-description" required></div>
                <div class="col-md-2"><label class="form-label">Category</label><select name="items[${itemIndex}][item_category]" class="form-select item-category" required><option value="material">Material</option><option value="labor">Labor</option><option value="misc">Misc</option></select></div>
                <div class="col-md-2"><label class="form-label">Quantity</label><input type="number" name="items[${itemIndex}][quantity]" class="form-control item-quantity" step="0.01" min="0.01" value="1" required></div>
                <div class="col-md-2"><label class="form-label">Unit Price</label><input type="number" name="items[${itemIndex}][unit_price]" class="form-control item-price" step="0.01" min="0" value="0" required></div>
                <div class="col-md-2"><label class="form-label">Line Total</label><input type="text" class="form-control item-total-display" value="PHP 0.00" readonly></div>
            </div>
            <div class="mt-3 text-end"><button type="button" class="btn btn-sm btn-outline-danger quotation-round-btn remove-item-btn"><i class="fas fa-trash me-1"></i>Remove</button></div>
        `;

        container.appendChild(row);
        attachRowEvents(row);
        itemIndex++;
        recalculateTotals();
    });

    document.querySelectorAll('.quotation-item-row').forEach(attachRowEvents);
    taxRateInput.addEventListener('input', recalculateTotals);
    paymentPlan.addEventListener('change', recalculateTotals);
    recalculateTotals();
});
</script>

<style>
    .quotation-request-card,
    .quotation-form-card,
    .quotation-cost-card {
        background: #fff;
        border: 1px solid rgba(15, 76, 129, 0.12);
        border-radius: 24px;
        box-shadow: 0 12px 34px rgba(15, 23, 42, 0.07);
        padding: 22px;
    }

    .request-service-chip {
        display: inline-flex;
        align-items: center;
        border-radius: 999px;
        padding: 8px 13px;
        background: #eaf6ff;
        color: #0f4c81;
        font-weight: 900;
        font-size: 0.84rem;
    }

    .request-summary-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 14px;
    }

    .summary-box {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        padding: 14px;
    }

    .summary-box.wide { grid-column: span 2; }
    .summary-box.full { grid-column: 1 / -1; }

    .summary-label {
        color: #64748b;
        font-size: 0.75rem;
        font-weight: 950;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        margin-bottom: 4px;
    }

    .summary-value {
        color: #0f172a;
        font-weight: 850;
        line-height: 1.5;
    }

    .quotation-item-row {
        border: 1px solid #dbe5f1;
        border-radius: 22px;
        padding: 18px;
        margin-bottom: 16px;
        background: #f8fbff;
    }

    .quotation-round-btn,
    .quotation-submit-btn {
        border-radius: 14px;
        font-weight: 900;
    }

    .form-label { font-weight: 850; color: #334155; }
    .form-control,
    .form-select { border-radius: 14px; padding: 11px 14px; }

    .quotation-cost-card { position: sticky; top: 96px; }

    .cost-line {
        display: flex;
        justify-content: space-between;
        gap: 14px;
        margin-bottom: 10px;
        color: #334155;
    }

    .grand-total-box {
        display: flex;
        justify-content: space-between;
        gap: 14px;
        background: linear-gradient(135deg, #0f4c81, #102a43);
        color: #fff;
        border-radius: 18px;
        padding: 16px;
        font-weight: 950;
        margin-top: 12px;
    }

    .payment-breakdown-box {
        border: 1px solid #dbe5f1;
        border-radius: 18px;
        background: #f8fafc;
        padding: 14px;
        color: #334155;
    }

    @media (max-width: 991.98px) {
        .request-summary-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .summary-box.wide { grid-column: span 1; }
        .quotation-cost-card { position: static; }
    }

    @media (max-width: 575.98px) {
        .request-summary-grid { grid-template-columns: 1fr; }
    }
</style>
@endsection
