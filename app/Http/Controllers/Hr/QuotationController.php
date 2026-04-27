<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use App\Models\Quotation;
use App\Models\QuotationRequest;
use App\Services\AlertService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class QuotationController extends Controller
{
    public function index()
    {
        $quotations = Quotation::with(['request', 'items', 'preparedBy'])
            ->latest()
            ->paginate(10);

        return view('hr.quotations.index', compact('quotations'));
    }

    public function create(QuotationRequest $quotationRequest)
    {
        $quotationRequest->load('worker', 'quotation');

        if ($quotationRequest->quotation) {
            return redirect()
                ->route('hr.quotations.show', $quotationRequest->quotation)
                ->with('info', 'A quotation already exists for this request.');
        }

        return view('hr.quotations.create', compact('quotationRequest'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'quotation_request_id' => ['required', 'exists:quotation_requests,id'],
            'notes' => ['nullable', 'string'],
            'payment_plan' => ['required', 'in:auto,5050,30303010'],
            'tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],

            'items' => ['required', 'array', 'min:1'],
            'items.*.description' => ['required', 'string', 'max:255'],
            'items.*.item_category' => ['required', 'in:material,labor,misc'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ]);

        $quotationRequest = QuotationRequest::with('quotation')->findOrFail($validated['quotation_request_id']);

        if ($quotationRequest->quotation) {
            return back()->withErrors([
                'quotation_request_id' => 'This request already has a quotation.'
            ])->withInput();
        }

        $itemRows = [];
        $categoryTotals = [
            'material' => 0,
            'labor' => 0,
            'misc' => 0,
        ];

        foreach ($validated['items'] as $item) {
            $lineTotal = round(((float) $item['quantity']) * ((float) $item['unit_price']), 2);

            $itemRows[] = [
                'description' => $item['description'],
                'item_category' => $item['item_category'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'total_price' => $lineTotal,
            ];

            $categoryTotals[$item['item_category']] += $lineTotal;
        }

        $materials = round($categoryTotals['material'], 2);
        $labor = round($categoryTotals['labor'], 2);
        $misc = round($categoryTotals['misc'], 2);
        $subtotal = round($materials + $labor + $misc, 2);

        $taxRate = round((float) ($validated['tax_rate'] ?? 0), 2);
        $taxAmount = round(($subtotal * $taxRate) / 100, 2);
        $grandTotal = round($subtotal + $taxAmount, 2);

        $paymentTerms = $this->buildPaymentTerms($grandTotal, $validated['payment_plan']);

        DB::transaction(function () use (
            $quotationRequest,
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
            &$quotation
        ) {
            $quotation = Quotation::create([
                'quotation_request_id' => $quotationRequest->id,
                'quotation_no' => $this->generateQuotationNumber(),
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
                'prepared_by' => Auth::id(),
            ]);

            foreach ($itemRows as $item) {
                $quotation->items()->create($item);
            }
        });

        return redirect()
            ->route('hr.quotations.show', $quotation)
            ->with('success', 'Quotation created successfully.');
    }

    public function show(Quotation $quotation)
    {
        $quotation->load(['request', 'items', 'preparedBy', 'invoice', 'contract']);

        return view('hr.quotations.show', compact('quotation'));
    }

    public function send(Quotation $quotation)
    {
        if ($quotation->status !== 'draft') {
            return back()->withErrors([
                'quotation' => 'Only draft quotations can be sent.'
            ]);
        }

        $quotation->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        $request = $quotation->request;

        $client = \App\Models\User::where('email', $request->email)
            ->where('role', 'client')
            ->first();

        if ($client) {
            AlertService::send(
                $client,
                'Quotation sent',
                'A quotation has been prepared for your service request.',
                route('client.quotations.show', $quotation),
                'info'
            );
        }

        return back()->with('success', 'Quotation sent to client successfully.');
    }

    protected function generateQuotationNumber(): string
    {
        $nextId = (Quotation::max('id') ?? 0) + 1;

        return 'QT-' . now()->format('Y') . '-' . str_pad((string) $nextId, 4, '0', STR_PAD_LEFT);
    }

    protected function buildPaymentTerms(float $total, string $planKey = 'auto'): array
    {
        if ($planKey === 'auto') {
            $planKey = $total >= 100000 ? '30303010' : '5050';
        }

        $phases = $planKey === '30303010'
            ? [
                ['label' => 'Downpayment', 'percent' => 30],
                ['label' => 'Progress 1', 'percent' => 30],
                ['label' => 'Progress 2', 'percent' => 30],
                ['label' => 'Retention', 'percent' => 10],
            ]
            : [
                ['label' => 'Downpayment', 'percent' => 50],
                ['label' => 'Final', 'percent' => 50],
            ];

        $running = 0;

        foreach ($phases as $i => &$phase) {
            if ($i === count($phases) - 1) {
                $phase['amount'] = round(max(0, $total - $running), 2);
            } else {
                $phase['amount'] = round(($total * $phase['percent']) / 100, 2);
                $running += $phase['amount'];
            }
        }

        return [
            'plan' => $planKey,
            'total' => round($total, 2),
            'currency' => 'PHP',
            'phases' => $phases,
        ];
    }
}