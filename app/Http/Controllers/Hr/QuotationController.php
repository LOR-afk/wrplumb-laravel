<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use App\Models\Quotation;
use App\Models\QuotationRequest;
use App\Services\AlertService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;

class QuotationController extends Controller
{
    public function index(Request $request)
    {
        $baseActiveQuery = Quotation::query()
            ->whereNull('archived_at')
            ->whereHas('request', function ($requestQuery) {
                $requestQuery->whereNull('archived_at');
            });

        $query = Quotation::query()
            ->with(['request', 'items', 'preparedBy', 'invoice'])
            ->withCount('items')
            ->whereNull('archived_at')
            ->whereHas('request', function ($requestQuery) {
                $requestQuery->whereNull('archived_at');
            });

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = trim($request->search);

            $query->where(function ($q) use ($search) {
                $q->where('quotation_no', 'like', "%{$search}%")
                    ->orWhereHas('request', function ($requestQuery) use ($search) {
                        $requestQuery->where('full_name', 'like', "%{$search}%")
                            ->orWhere('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%")
                            ->orWhere('service_type', 'like', "%{$search}%")
                            ->orWhere('service_category', 'like', "%{$search}%");
                    });
            });
        }

        $summary = [
            'total_quotations' => (clone $baseActiveQuery)->count(),
            'sent_quotations' => (clone $baseActiveQuery)->where('status', 'sent')->count(),
            'total_amount' => (clone $baseActiveQuery)->sum('grand_total'),
            'latest_created' => (clone $baseActiveQuery)->max('created_at'),
        ];

        $statusCounts = [
            'all' => (clone $baseActiveQuery)->count(),
            'draft' => (clone $baseActiveQuery)->where('status', 'draft')->count(),
            'sent' => (clone $baseActiveQuery)->where('status', 'sent')->count(),
            'accepted' => (clone $baseActiveQuery)->where('status', 'accepted')->count(),
            'rejected' => (clone $baseActiveQuery)->whereIn('status', ['rejected', 'declined'])->count(),
        ];

    $quotationRequestsForCreate = QuotationRequest::query()
        ->with([
            'worker',
            'jobOrder',
            'forwardedToHrBy',
        ])
        ->whereNull('archived_at')
        ->where('status', 'ready_for_quotation')
        ->whereNotNull('ready_for_quotation_at')
        ->whereDoesntHave('quotation')
        ->latest('ready_for_quotation_at')
        ->take(6)
        ->get();

        $quotations = $query
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('hr.quotations.index', compact(
            'quotations',
            'summary',
            'statusCounts',
            'quotationRequestsForCreate'
        ));
    }


    public function archived(Request $request)
    {
        $query = Quotation::query()
            ->with(['request', 'items', 'preparedBy', 'invoice'])
            ->withCount('items')
            ->whereNotNull('archived_at');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = trim($request->search);

            $query->where(function ($q) use ($search) {
                $q->where('quotation_no', 'like', "%{$search}%")
                    ->orWhereHas('request', function ($requestQuery) use ($search) {
                        $requestQuery->where('full_name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%")
                            ->orWhere('service_type', 'like', "%{$search}%")
                            ->orWhere('service_category', 'like', "%{$search}%");
                    });
            });
        }

        $summary = [
            'archived_quotations' => Quotation::whereNotNull('archived_at')->count(),
            'latest_archived' => Quotation::whereNotNull('archived_at')->max('archived_at'),
        ];

        $quotations = $query
            ->latest('archived_at')
            ->paginate(10)
            ->withQueryString();

        return view('hr.quotations.archived', compact('quotations', 'summary'));
    }

public function archive(Quotation $quotation)
{
    $quotation->archived_at = now();
    $quotation->archive_reason = 'Manually archived by HR';
    $quotation->save();

    return redirect()
        ->route('hr.quotations.index')
        ->with('success', 'Quotation archived successfully.');
}

public function restore(Quotation $quotation)
{
    $quotation->archived_at = null;
    $quotation->archive_reason = null;
    $quotation->save();

    return redirect()
        ->route('hr.quotations.archived')
        ->with('success', 'Quotation restored successfully.');
}

    public function create(QuotationRequest $quotationRequest)
    {
        $quotationRequest->load([
            'worker',
            'quotation',
            'inspectionReport.materialItems',
            'inspectionReport.inspector',
        ]);

        if ($quotationRequest->quotation) {
            return redirect()
                ->route('hr.quotations.show', $quotationRequest->quotation)
                ->with('info', 'A quotation already exists for this request.');
        }

        $inspectionReport = $quotationRequest->inspectionReport;

        $prefillItems = collect();

        if ($inspectionReport && $inspectionReport->status === 'submitted') {
            foreach ($inspectionReport->materialItems as $material) {
                $prefillItems->push([
                    'description' => $material->item_name,
                    'item_category' => 'material',
                    'quantity' => (float) $material->quantity,
                    'unit' => $material->unit,
                    'unit_price' => (float) $material->unit_cost,
                ]);
            }

            if ((float) $inspectionReport->estimated_labor_cost > 0) {
                $prefillItems->push([
                    'description' => 'Labor',
                    'item_category' => 'labor',
                    'quantity' => 1,
                    'unit' => 'service',
                    'unit_price' => (float) $inspectionReport->estimated_labor_cost,
                ]);
            }

            if ((float) $inspectionReport->estimated_miscellaneous_cost > 0) {
                $prefillItems->push([
                    'description' => 'Miscellaneous',
                    'item_category' => 'misc',
                    'quantity' => 1,
                    'unit' => 'lot',
                    'unit_price' => (float) $inspectionReport->estimated_miscellaneous_cost,
                ]);
            }
        }

        if ($prefillItems->isEmpty()) {
            $prefillItems->push([
                'description' => '',
                'item_category' => 'material',
                'quantity' => 1,
                'unit' => 'pcs',
                'unit_price' => 0,
            ]);
        }

        return view('hr.quotations.create', compact(
            'quotationRequest',
            'inspectionReport',
            'prefillItems'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'quotation_request_id' => ['required', 'exists:quotation_requests,id'],
            'notes' => ['nullable', 'string'],
            'payment_plan' => ['required', 'in:auto,full,5050,30303010'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.description' => ['required', 'string', 'max:255'],
            'items.*.item_category' => ['required', 'in:material,labor,misc'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit' => ['required', 'string', 'max:50'],
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
                'acceptance_token' => Str::random(64),
                'client_response' => null,
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
            if (!in_array($quotation->status, ['draft', 'sent'])) {
                return back()->withErrors([
                    'quotation' => 'Only draft or sent quotations can be emailed.'
                ]);
            }

            $quotation->loadMissing(['request', 'items']);

            if (!$quotation->request || !$quotation->request->email) {
                return back()->withErrors([
                    'quotation' => 'This quotation has no client email address.'
                ]);
            }

            if (!$quotation->acceptance_token) {
                $quotation->acceptance_token = Str::random(64);
            }

            $quotation->status = 'sent';
            $quotation->sent_at = now();
            $quotation->save();

            $publicUrl = route('public.quotations.show', $quotation->acceptance_token);

            $clientName = $quotation->request->full_name
                ?? trim(($quotation->request->first_name ?? '') . ' ' . ($quotation->request->last_name ?? ''));

            if (!$clientName) {
                $clientName = 'Client';
            }

            $serviceType = $quotation->request->service_type ?? 'your requested service';

            Mail::send('emails.quotation-ready', [
                'quotation' => $quotation,
                'publicUrl' => $publicUrl,
                'clientName' => $clientName,
                'serviceType' => $serviceType,
            ], function ($message) use ($quotation, $clientName) {
                $message->to($quotation->request->email, $clientName)
                    ->subject('Your WRPlumb Quotation is Ready - ' . $quotation->quotation_no);
            });

            $client = \App\Models\User::where('email', $quotation->request->email)
                ->where('role', 'client')
                ->first();

            if ($client) {
                AlertService::send(
                    $client,
                    'Quotation sent',
                    'A quotation has been prepared for your service request.',
                    route('client.quotations.show', $quotation, false),
                    'info'
                );
            }

            return back()->with('success', 'Quotation email sent successfully.');
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

        if ($planKey === 'full') {
            $phases = [
                ['label' => 'Full Payment', 'percent' => 100],
            ];
        } elseif ($planKey === '30303010') {
            $phases = [
                ['label' => 'Downpayment', 'percent' => 30],
                ['label' => 'Progress 1', 'percent' => 30],
                ['label' => 'Progress 2', 'percent' => 30],
                ['label' => 'Retention', 'percent' => 10],
            ];
        } else {
            $phases = [
                ['label' => 'Downpayment', 'percent' => 50],
                ['label' => 'Final', 'percent' => 50],
            ];
        }

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