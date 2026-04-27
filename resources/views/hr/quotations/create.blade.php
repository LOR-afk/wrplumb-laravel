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

<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body">
        <h5 class="mb-3">Request Summary</h5>
        <div class="row g-3">
            <div class="col-md-4">
                <div class="small text-muted">Client</div>
                <div class="fw-semibold">{{ $quotationRequest->full_name }}</div>
            </div>
            <div class="col-md-4">
                <div class="small text-muted">Email</div>
                <div class="fw-semibold">{{ $quotationRequest->email }}</div>
            </div>
            <div class="col-md-4">
                <div class="small text-muted">Phone</div>
                <div class="fw-semibold">{{ $quotationRequest->phone }}</div>
            </div>

            <div class="col-md-4">
                <div class="small text-muted">Service Category</div>
                <div class="fw-semibold">{{ $quotationRequest->service_category }}</div>
            </div>
            <div class="col-md-4">
                <div class="small text-muted">Service Type</div>
                <div class="fw-semibold">{{ $quotationRequest->service_type }}</div>
            </div>
            <div class="col-md-4">
                <div class="small text-muted">Project Type</div>
                <div class="fw-semibold">{{ $quotationRequest->project_type }}</div>
            </div>

            <div class="col-md-6">
                <div class="small text-muted">Address</div>
                <div class="fw-semibold">{{ $quotationRequest->address }}</div>
            </div>
            <div class="col-md-6">
                <div class="small text-muted">Appointment Schedule</div>
                <div class="fw-semibold">
                    @if ($quotationRequest->appointment_date && $quotationRequest->appointment_time)
                        {{ date('M d, Y', strtotime($quotationRequest->appointment_date)) }}
                        •
                        {{ date('h:i A', strtotime($quotationRequest->appointment_time)) }}
                    @else
                        Not yet scheduled
                    @endif
                </div>
            </div>

            <div class="col-12">
                <div class="small text-muted">Problem Details</div>
                <div class="fw-semibold">{{ $quotationRequest->details }}</div>
            </div>
        </div>
    </div>
</div>

<form method="POST" action="{{ route('hr.quotations.store') }}" id="quotationForm">
    @csrf
    <input type="hidden" name="quotation_request_id" value="{{ $quotationRequest->id }}">

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">Quotation Items</h5>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="addItemBtn">
                            Add Item
                        </button>
                    </div>

                    <div id="quotationItemsContainer">
                        <div class="quotation-item-row border rounded-4 p-3 mb-3">
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
                                <button type="button" class="btn btn-sm btn-outline-danger remove-item-btn">
                                    Remove
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="mt-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="4">{{ old('notes') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body">
                    <h5 class="mb-3">Cost Summary</h5>

                    <div class="mb-2 d-flex justify-content-between">
                        <span>Materials</span>
                        <strong id="materialsTotal">PHP 0.00</strong>
                    </div>

                    <div class="mb-2 d-flex justify-content-between">
                        <span>Labor</span>
                        <strong id="laborTotal">PHP 0.00</strong>
                    </div>

                    <div class="mb-2 d-flex justify-content-between">
                        <span>Miscellaneous</span>
                        <strong id="miscTotal">PHP 0.00</strong>
                    </div>

                    <hr>

                    <div class="mb-3 d-flex justify-content-between">
                        <span>Subtotal</span>
                        <strong id="subtotalAmount">PHP 0.00</strong>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Tax Rate (%)</label>
                        <input type="number" step="0.01" min="0" max="100" name="tax_rate" id="taxRate" class="form-control" value="{{ old('tax_rate', 0) }}">
                    </div>

                    <div class="mb-2 d-flex justify-content-between">
                        <span>Tax Amount</span>
                        <strong id="taxAmount">PHP 0.00</strong>
                    </div>

                    <div class="mb-3 d-flex justify-content-between">
                        <span>Grand Total</span>
                        <strong id="grandTotal">PHP 0.00</strong>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Payment Plan</label>
                        <select name="payment_plan" id="paymentPlan" class="form-select">
                            <option value="auto">Auto</option>
                            <option value="5050">50 / 50</option>
                            <option value="30303010">30 / 30 / 30 / 10</option>
                        </select>
                    </div>

                    <div>
                        <label class="form-label">Payment Breakdown</label>
                        <div id="paymentBreakdown" class="small text-muted border rounded-3 p-3 bg-light">
                            No payment breakdown yet.
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary">
                    Save Draft Quotation
                </button>
                <a href="{{ route('hr.dashboard') }}" class="btn btn-outline-secondary">
                    Cancel
                </a>
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
            <div class="d-flex justify-content-between mb-1">
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
        row.className = 'quotation-item-row border rounded-4 p-3 mb-3';
        row.innerHTML = `
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Description</label>
                    <input type="text" name="items[${itemIndex}][description]" class="form-control item-description" required>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Category</label>
                    <select name="items[${itemIndex}][item_category]" class="form-select item-category" required>
                        <option value="material">Material</option>
                        <option value="labor">Labor</option>
                        <option value="misc">Misc</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Quantity</label>
                    <input type="number" name="items[${itemIndex}][quantity]" class="form-control item-quantity" step="0.01" min="0.01" value="1" required>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Unit Price</label>
                    <input type="number" name="items[${itemIndex}][unit_price]" class="form-control item-price" step="0.01" min="0" value="0" required>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Line Total</label>
                    <input type="text" class="form-control item-total-display" value="PHP 0.00" readonly>
                </div>
            </div>

            <div class="mt-3 text-end">
                <button type="button" class="btn btn-sm btn-outline-danger remove-item-btn">
                    Remove
                </button>
            </div>
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
@endsection