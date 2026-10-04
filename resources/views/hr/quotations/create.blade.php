@extends('hr.layouts.app')

@section('title', 'Create Quotation')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/hr/quotation-create.css') }}?v=quotation-create-04">
<link rel="stylesheet" href="{{ asset('css/hr/inspection-reports.css') }}?v=inspection-reports-01">
@endpush

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

@if ($inspectionReport && $inspectionReport->status === 'submitted')
    <div class="inspection-source-card mb-4">
        <div class="inspection-source-icon">
            <i class="fas fa-clipboard-check"></i>
        </div>

        <div class="inspection-source-copy">
            <span>INSPECTION REPORT AVAILABLE</span>
            <h5>{{ $inspectionReport->report_no }}</h5>
            <p>
                Inspector estimate has been loaded as a starting point.
                Review and adjust all items before sending the quotation.
            </p>
        </div>

        <div class="inspection-source-meta">
            <div>
                <span>Inspector Estimate</span>
                <strong>PHP {{ number_format((float) $inspectionReport->estimated_total_cost, 2) }}</strong>
            </div>

            <a
                href="{{ route('hr.inspection-reports.show', $inspectionReport) }}"
                class="btn btn-sm btn-outline-primary"
                target="_blank"
            >
                <i class="fas fa-eye me-1"></i>
                View Report
            </a>
        </div>
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

            @php
                $scopeRows = old('scope_items', ['']);
            @endphp

            <div class="quotation-form-card quotation-scope-card mb-4">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
                    <div>
                        <h5 class="fw-bold mb-1">Scope of Works</h5>
                        <p class="text-muted mb-0 small">
                            List the work activities included in this quotation.
                        </p>
                    </div>

                    <button
                        type="button"
                        class="btn btn-sm btn-outline-primary quotation-round-btn"
                        id="addScopeItemBtn"
                    >
                        Add Scope Item
                    </button>
                </div>

                <div id="scopeItemsContainer">
                    @foreach ($scopeRows as $scopeIndex => $scopeItem)
                        <div class="scope-work-row">
                            <div class="scope-work-number">
                                {{ $loop->iteration }}
                            </div>

                            <div class="scope-work-field">
                                <label class="form-label">
                                    Work Description
                                </label>

                                <textarea
                                    name="scope_items[]"
                                    class="form-control scope-work-input"
                                    rows="2"
                                    maxlength="2000"
                                    placeholder="Example: Installation of PE pipe from water meter to the main water supply line."
                                    required
                                >{{ $scopeItem }}</textarea>
                            </div>

                            <button
                                type="button"
                                class="btn btn-outline-danger remove-scope-btn"
                                aria-label="Remove scope item"
                            >
                                Remove
                            </button>
                        </div>
                    @endforeach
                </div>
            </div>
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
                    @foreach ($prefillItems as $index => $prefillItem)
                        <div class="quotation-item-row">
                            <div class="row g-3 quotation-item-grid">
                                <div class="col-md-3">
                                    <label class="form-label">Description</label>
                                    <input
                                        type="text"
                                        name="items[{{ $index }}][description]"
                                        class="form-control item-description"
                                        value="{{ old("items.$index.description", $prefillItem['description']) }}"
                                        required
                                    >
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label">Category</label>
                                    <select
                                        name="items[{{ $index }}][item_category]"
                                        class="form-select item-category"
                                        required
                                    >
                                        <option value="material" @selected(old("items.$index.item_category", $prefillItem['item_category']) === 'material')>Material</option>
                                        <option value="labor" @selected(old("items.$index.item_category", $prefillItem['item_category']) === 'labor')>Labor</option>
                                        <option value="misc" @selected(old("items.$index.item_category", $prefillItem['item_category']) === 'misc')>Misc</option>
                                    </select>
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label">Quantity</label>
                                    <input
                                        type="number"
                                        name="items[{{ $index }}][quantity]"
                                        class="form-control item-quantity"
                                        step="0.01"
                                        min="0.01"
                                        value="{{ old("items.$index.quantity", $prefillItem['quantity']) }}"
                                        required
                                    >
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label">Unit</label>
                                    @php
                                        $unitValue = old("items.$index.unit", $prefillItem['unit']);
                                        $unitOptions = [
                                            'pcs', 'set', 'box', 'pack', 'roll', 'meter', 'foot',
                                            'length', 'liter', 'gallon', 'kg', 'bag', 'sack',
                                            'bundle', 'lot', 'service', 'hour', 'day'
                                        ];
                                    @endphp

                                    <select
                                        name="items[{{ $index }}][unit]"
                                        class="form-select item-unit"
                                        required
                                    >
                                        @foreach ($unitOptions as $unitOption)
                                            <option value="{{ $unitOption }}" @selected($unitValue === $unitOption)>
                                                {{ $unitOption }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label">Unit Price</label>
                                    <input
                                        type="number"
                                        name="items[{{ $index }}][unit_price]"
                                        class="form-control item-price"
                                        step="0.01"
                                        min="0"
                                        value="{{ old("items.$index.unit_price", $prefillItem['unit_price']) }}"
                                        required
                                    >
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
                    @endforeach
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

                <input type="hidden" name="tax_rate" id="taxRate" value="12">

                <div class="vat-rate-card">
                    <div>
                        <span>VAT Rate</span>
                        <small>Fixed company VAT rate</small>
                    </div>
                    <strong>12%</strong>
                </div>

                <div class="cost-line"><span>Tax Amount</span><strong id="taxAmount">PHP 0.00</strong></div>
                <div class="grand-total-box"><span>Grand Total</span><strong id="grandTotal">PHP 0.00</strong></div>

                <div class="mt-3">
                    <label class="form-label">Payment Plan</label>
                    <select name="payment_plan" id="paymentPlan" class="form-select">
                        <option value="auto">Auto</option>
                        <option value="full">Full Payment (100%)</option>
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
    const scopeContainer = document.getElementById('scopeItemsContainer');
    const addScopeItemBtn = document.getElementById('addScopeItemBtn');
    const taxRateInput = document.getElementById('taxRate');
    const paymentPlan = document.getElementById('paymentPlan');

    let itemIndex = document.querySelectorAll('.quotation-item-row').length;


    function renumberScopeItems() {
        scopeContainer.querySelectorAll('.scope-work-row').forEach((row, index) => {
            row.querySelector('.scope-work-number').textContent = index + 1;
        });
    }

    function attachScopeRowEvents(row) {
        const removeBtn = row.querySelector('.remove-scope-btn');

        removeBtn.addEventListener('click', function () {
            const rows = scopeContainer.querySelectorAll('.scope-work-row');

            if (rows.length > 1) {
                row.remove();
                renumberScopeItems();
            }
        });
    }

    addScopeItemBtn.addEventListener('click', function () {
        const row = document.createElement('div');
        row.className = 'scope-work-row';

        row.innerHTML = `
            <div class="scope-work-number"></div>

            <div class="scope-work-field">
                <label class="form-label">Work Description</label>

                <textarea
                    name="scope_items[]"
                    class="form-control scope-work-input"
                    rows="2"
                    maxlength="2000"
                    placeholder="Describe the work activity included in this quotation."
                    required
                ></textarea>
            </div>

            <button
                type="button"
                class="btn btn-outline-danger remove-scope-btn"
                aria-label="Remove scope item"
            >
                Remove
            </button>
        `;

        scopeContainer.appendChild(row);
        attachScopeRowEvents(row);
        renumberScopeItems();
        row.querySelector('.scope-work-input').focus();
    });

    scopeContainer.querySelectorAll('.scope-work-row').forEach(attachScopeRowEvents);
    renumberScopeItems();

    function formatMoney(value) {
        return 'PHP ' + new Intl.NumberFormat('en-PH', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }).format(Number(value) || 0);
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

        let phases;

        if (chosenPlan === 'full') {
            phases = [
                { label: 'Full Payment', percent: 100 },
            ];
        } else if (chosenPlan === '30303010') {
            phases = [
                { label: 'Downpayment', percent: 30 },
                { label: 'Progress 1', percent: 30 },
                { label: 'Progress 2', percent: 30 },
                { label: 'Retention', percent: 10 },
            ];
        } else {
            phases = [
                { label: 'Downpayment', percent: 50 },
                { label: 'Final', percent: 50 },
            ];
        }

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
            <div class="row g-3 quotation-item-grid">
                <div class="col-md-3"><label class="form-label">Description</label><input type="text" name="items[${itemIndex}][description]" class="form-control item-description" required></div>
                <div class="col-md-2"><label class="form-label">Category</label><select name="items[${itemIndex}][item_category]" class="form-select item-category" required><option value="material">Material</option><option value="labor">Labor</option><option value="misc">Misc</option></select></div>
                <div class="col-md-2"><label class="form-label">Quantity</label><input type="number" name="items[${itemIndex}][quantity]" class="form-control item-quantity" step="0.01" min="0.01" value="1" required></div>
                <div class="col-md-2"><label class="form-label">Unit</label><select name="items[${itemIndex}][unit]" class="form-select item-unit" required><option value="pcs">pcs</option><option value="set">set</option><option value="box">box</option><option value="pack">pack</option><option value="roll">roll</option><option value="meter">meter</option><option value="foot">foot</option><option value="length">length</option><option value="liter">liter</option><option value="gallon">gallon</option><option value="kg">kg</option><option value="bag">bag</option><option value="sack">sack</option><option value="bundle">bundle</option><option value="lot">lot</option><option value="service">service</option><option value="hour">hour</option><option value="day">day</option></select></div>
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
    paymentPlan.addEventListener('change', recalculateTotals);
    recalculateTotals();
});
</script>

@endsection