<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Quotation;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GeneratedQuotationController extends Controller
{
    public function index(Request $request)
    {
        $query = Quotation::query()
            ->with(['request', 'items', 'preparedBy', 'invoice', 'contract'])
            ->withCount('items')
            ->whereNull('archived_at');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = trim($request->search);

            $query->where(function ($q) use ($search) {
                $q->where('quotation_no', 'like', "%{$search}%")
                    ->orWhereHas('request', function ($requestQuery) use ($search) {
                        $requestQuery
                            ->where('full_name', 'like', "%{$search}%")
                            ->orWhere('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('service_type', 'like', "%{$search}%");
                    });
            });
        }

        $summaryBase = Quotation::query()->whereNull('archived_at');

        $summary = [
            'total' => (clone $summaryBase)->count(),
            'draft' => (clone $summaryBase)->where('status', 'draft')->count(),
            'sent' => (clone $summaryBase)->where('status', 'sent')->count(),
            'accepted' => (clone $summaryBase)->where('status', 'accepted')->count(),
        ];

        $quotations = $query
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.generated-quotations.index', compact('quotations', 'summary'));
    }

    public function show(Quotation $quotation)
    {
        $quotation->load([
            'request.inspectionReport.inspector',
            'request.inspectionReport.materialItems',
            'items',
            'preparedBy',
            'invoice',
            'contract',
        ]);

        return view('admin.generated-quotations.show', compact('quotation'));
    }

    public function edit(Quotation $quotation)
    {
        abort_unless(
            in_array($quotation->status, ['draft', 'sent'], true),
            422,
            'Only draft or sent quotations can be edited. Accepted or finalized records must remain unchanged.'
        );

        $quotation->load(['request.inspectionReport', 'items', 'preparedBy']);

        return view('admin.generated-quotations.edit', compact('quotation'));
    }

    public function update(Request $request, Quotation $quotation)
    {
        abort_unless(
            in_array($quotation->status, ['draft', 'sent'], true),
            422,
            'Only draft or sent quotations can be edited.'
        );

        $validated = $request->validate([
            'notes' => ['nullable', 'string'],
            'payment_plan' => ['required', 'in:auto,full,5050,30303010'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.description' => ['required', 'string', 'max:255'],
            'items.*.item_category' => ['required', 'in:material,labor,misc'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit' => ['required', 'string', 'max:50'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ]);

        $oldValues = $quotation->only([
            'status',
            'notes',
            'payment_plan',
            'materials_cost',
            'labor_cost',
            'miscellaneous_cost',
            'subtotal_amount',
            'tax_rate',
            'tax_amount',
            'grand_total',
            'sent_at',
        ]);

        $itemRows = [];
        $categoryTotals = [
            'material' => 0,
            'labor' => 0,
            'misc' => 0,
        ];

        foreach ($validated['items'] as $item) {
            $lineTotal = round(
                ((float) $item['quantity']) * ((float) $item['unit_price']),
                2
            );

            $itemRows[] = [
                'description' => $item['description'],
                'item_category' => $item['item_category'],
                'quantity' => $item['quantity'],
                'unit' => $item['unit'],
                'unit_price' => $item['unit_price'],
                'total_price' => $lineTotal,
            ];

            $categoryTotals[$item['item_category']] += $lineTotal;
        }

        $materials = round($categoryTotals['material'], 2);
        $labor = round($categoryTotals['labor'], 2);
        $misc = round($categoryTotals['misc'], 2);
        $subtotal = round($materials + $labor + $misc, 2);

        $taxRate = 12.00;
        $taxAmount = round(($subtotal * $taxRate) / 100, 2);
        $grandTotal = round($subtotal + $taxAmount, 2);

        $paymentTerms = $this->buildPaymentTerms(
            $grandTotal,
            $validated['payment_plan']
        );

        $wasSent = $quotation->status === 'sent';

        DB::transaction(function () use (
            $quotation,
            $validated,
            $materials,
            $labor,
            $misc,
            $subtotal,
            $taxRate,
            $taxAmount,
            $grandTotal,
            $paymentTerms,
            $itemRows,
            $wasSent
        ) {
            $quotation->update([
                'status' => 'draft',
                'notes' => $validated['notes'] ?? null,
                'payment_plan' => $validated['payment_plan'],
                'payment_terms_json' => $paymentTerms,
                'materials_cost' => $materials,
                'labor_cost' => $labor,
                'miscellaneous_cost' => $misc,
                'subtotal_amount' => $subtotal,
                'tax_rate' => $taxRate,
                'tax_amount' => $taxAmount,
                'grand_total' => $grandTotal,
                'sent_at' => $wasSent ? null : $quotation->sent_at,
                'prepared_by' => Auth::id(),
            ]);

            $quotation->items()->delete();

            foreach ($itemRows as $item) {
                $quotation->items()->create($item);
            }
        });

        AuditLogService::log(
            'Admin Updated Generated Quotation',
            'Generated Quotations',
            $quotation,
            $oldValues,
            [
                'status' => $quotation->status,
                'grand_total' => $quotation->grand_total,
                'tax_rate' => $quotation->tax_rate,
                'prepared_by' => Auth::id(),
            ],
            "Admin updated generated quotation {$quotation->quotation_no}."
        );

        $message = $wasSent
            ? 'Quotation updated and returned to Draft. Please have HR review/send it again before client acceptance.'
            : 'Quotation updated successfully.';

        return redirect()
            ->route('admin.generated-quotations.show', $quotation)
            ->with('success', $message);
    }

    private function buildPaymentTerms(float $grandTotal, string $paymentPlan): array
    {
        $resolvedPlan = $paymentPlan;

        if ($resolvedPlan === 'auto') {
            $resolvedPlan = $grandTotal >= 100000
                ? '30303010'
                : '5050';
        }

        $phases = match ($resolvedPlan) {
            'full' => [
                ['label' => 'Full Payment', 'percent' => 100],
            ],
            '30303010' => [
                ['label' => 'Downpayment', 'percent' => 30],
                ['label' => 'Progress 1', 'percent' => 30],
                ['label' => 'Progress 2', 'percent' => 30],
                ['label' => 'Retention', 'percent' => 10],
            ],
            default => [
                ['label' => 'Downpayment', 'percent' => 50],
                ['label' => 'Final', 'percent' => 50],
            ],
        };

        $running = 0;

        return collect($phases)
            ->map(function ($phase, $index) use ($phases, $grandTotal, &$running) {
                if ($index === count($phases) - 1) {
                    $amount = max(0, $grandTotal - $running);
                } else {
                    $amount = round(
                        ($grandTotal * $phase['percent']) / 100,
                        2
                    );

                    $running += $amount;
                }

                return [
                    'label' => $phase['label'],
                    'percent' => $phase['percent'],
                    'amount' => round($amount, 2),
                ];
            })
            ->values()
            ->all();
    }
}