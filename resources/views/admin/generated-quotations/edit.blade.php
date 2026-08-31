@extends('admin.layouts.app')

@section('title', 'Edit Generated Quotation')
@section('topbar_title', 'Edit Quotation')
@section('topbar_subtitle', 'Revise itemized pricing before the quotation is accepted by the client.')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin/generated-quotations.css') }}?v=generated-quotations-01">
@endpush

@section('content')
<div class="generated-quotation-page">
    @if ($quotation->status === 'sent')
        <div class="gq-warning-note">
            <i class="fas fa-triangle-exclamation"></i>
            <div>
                <strong>This quotation has already been sent</strong>
                <span>Saving changes will return it to Draft so the revised quotation can be reviewed and sent again.</span>
            </div>
        </div>
    @endif

    <section class="gq-detail-hero">
        <div>
            <span>EDIT QUOTATION</span>
            <h2>{{ $quotation->quotation_no }}</h2>
            <p>{{ $quotation->request?->full_name }} · {{ $quotation->request?->service_type }}</p>
        </div>

        <a href="{{ route('admin.generated-quotations.show', $quotation) }}" class="gq-btn">
            <i class="fas fa-xmark"></i>Cancel
        </a>
    </section>

    <form method="POST" action="{{ route('admin.generated-quotations.update', $quotation) }}" id="adminQuotationEditForm">
        @csrf
        @method('PUT')

        <div class="gq-edit-grid">
            <section class="gq-detail-card">
                <header class="gq-edit-head">
                    <div>
                        <h3>Quotation Items</h3>
                        <p>Admin may revise materials, labor, and miscellaneous charges.</p>
                    </div>

                    <button type="button" class="gq-btn primary" id="addAdminQuotationItem">
                        <i class="fas fa-plus"></i>Add Item
                    </button>
                </header>

                <div class="gq-edit-items" id="adminQuotationItems">
                    @foreach ($quotation->items as $index => $item)
                        <div class="gq-edit-item">
                            <div><label>Description</label><input type="text" name="items[{{ $index }}][description]" value="{{ old("items.$index.description", $item->description) }}" required></div>
                            <div>
                                <label>Category</label>
                                <select name="items[{{ $index }}][item_category]" class="item-category" required>
                                    @foreach (['material' => 'Material', 'labor' => 'Labor', 'misc' => 'Misc'] as $value => $label)
                                        <option value="{{ $value }}" @selected(old("items.$index.item_category", $item->item_category) === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div><label>Qty</label><input type="number" class="item-quantity" name="items[{{ $index }}][quantity]" value="{{ old("items.$index.quantity", $item->quantity) }}" min="0.01" step="0.01" required></div>
                            <div><label>Unit</label><input type="text" name="items[{{ $index }}][unit]" value="{{ old("items.$index.unit", $item->unit) }}" required></div>
                            <div><label>Unit Price</label><input type="number" class="item-price" name="items[{{ $index }}][unit_price]" value="{{ old("items.$index.unit_price", $item->unit_price) }}" min="0" step="0.01" required></div>
                            <div><label>Line Total</label><input type="text" class="item-total-display" readonly></div>
                            <button type="button" class="gq-remove-item" title="Remove item"><i class="fas fa-trash"></i></button>
                        </div>
                    @endforeach
                </div>

                <div class="gq-notes-field">
                    <label>Quotation Notes</label>
                    <textarea name="notes" rows="4" placeholder="Optional quotation notes...">{{ old('notes', $quotation->notes) }}</textarea>
                </div>
            </section>

            <aside class="gq-detail-card gq-sticky-summary">
                <header><h3>Cost Summary</h3></header>

                <div class="gq-cost-list">
                    <div><span>Materials</span><strong id="adminMaterialsTotal">PHP 0.00</strong></div>
                    <div><span>Labor</span><strong id="adminLaborTotal">PHP 0.00</strong></div>
                    <div><span>Miscellaneous</span><strong id="adminMiscTotal">PHP 0.00</strong></div>
                    <div><span>Subtotal</span><strong id="adminSubtotal">PHP 0.00</strong></div>
                    <div><span>VAT 12%</span><strong id="adminTax">PHP 0.00</strong></div>
                    <div class="total"><span>Grand Total</span><strong id="adminGrandTotal">PHP 0.00</strong></div>
                </div>

                <div class="gq-notes-field">
                    <label>Payment Plan</label>
                    <select name="payment_plan" required>
                        <option value="auto" @selected(old('payment_plan', $quotation->payment_plan) === 'auto')>Auto</option>
                        <option value="full" @selected(old('payment_plan', $quotation->payment_plan) === 'full')>Full Payment</option>
                        <option value="5050" @selected(old('payment_plan', $quotation->payment_plan) === '5050')>50 / 50</option>
                        <option value="30303010" @selected(old('payment_plan', $quotation->payment_plan) === '30303010')>30 / 30 / 30 / 10</option>
                    </select>
                </div>

                <button type="submit" class="gq-save-btn">
                    <i class="fas fa-floppy-disk"></i>Save Changes
                </button>
            </aside>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const container = document.getElementById('adminQuotationItems');
    const addButton = document.getElementById('addAdminQuotationItem');
    let itemIndex = container.querySelectorAll('.gq-edit-item').length;

    function money(value) {
        return 'PHP ' + new Intl.NumberFormat('en-PH', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }).format(Number(value) || 0);
    }

    function recalculate() {
        let materials = 0;
        let labor = 0;
        let misc = 0;

        container.querySelectorAll('.gq-edit-item').forEach(function (row) {
            const qty = parseFloat(row.querySelector('.item-quantity')?.value || 0);
            const price = parseFloat(row.querySelector('.item-price')?.value || 0);
            const category = row.querySelector('.item-category')?.value || 'material';
            const lineTotal = qty * price;

            row.querySelector('.item-total-display').value = money(lineTotal);

            if (category === 'labor') labor += lineTotal;
            else if (category === 'misc') misc += lineTotal;
            else materials += lineTotal;
        });

        const subtotal = materials + labor + misc;
        const tax = subtotal * 0.12;
        const grandTotal = subtotal + tax;

        document.getElementById('adminMaterialsTotal').textContent = money(materials);
        document.getElementById('adminLaborTotal').textContent = money(labor);
        document.getElementById('adminMiscTotal').textContent = money(misc);
        document.getElementById('adminSubtotal').textContent = money(subtotal);
        document.getElementById('adminTax').textContent = money(tax);
        document.getElementById('adminGrandTotal').textContent = money(grandTotal);
    }

    function newItem(index) {
        const wrapper = document.createElement('div');
        wrapper.className = 'gq-edit-item';

        wrapper.innerHTML = `
            <div><label>Description</label><input type="text" name="items[${index}][description]" required></div>
            <div><label>Category</label><select name="items[${index}][item_category]" class="item-category" required><option value="material">Material</option><option value="labor">Labor</option><option value="misc">Misc</option></select></div>
            <div><label>Qty</label><input type="number" class="item-quantity" name="items[${index}][quantity]" value="1" min="0.01" step="0.01" required></div>
            <div><label>Unit</label><input type="text" name="items[${index}][unit]" value="pcs" required></div>
            <div><label>Unit Price</label><input type="number" class="item-price" name="items[${index}][unit_price]" value="0" min="0" step="0.01" required></div>
            <div><label>Line Total</label><input type="text" class="item-total-display" value="PHP 0.00" readonly></div>
            <button type="button" class="gq-remove-item" title="Remove item"><i class="fas fa-trash"></i></button>
        `;

        return wrapper;
    }

    addButton.addEventListener('click', function () {
        container.appendChild(newItem(itemIndex));
        itemIndex++;
        recalculate();
    });

    container.addEventListener('input', recalculate);
    container.addEventListener('change', recalculate);

    container.addEventListener('click', function (event) {
        const remove = event.target.closest('.gq-remove-item');
        if (!remove) return;

        if (container.querySelectorAll('.gq-edit-item').length <= 1) {
            return;
        }

        remove.closest('.gq-edit-item').remove();
        recalculate();
    });

    recalculate();
});
</script>
@endpush