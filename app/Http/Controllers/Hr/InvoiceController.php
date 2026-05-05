<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Quotation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $query = Invoice::with([
            'quotation.request',
            'items',
            'creator',
            'paymentSchedules',
            'payments',
        ]);

        if ($request->filled('search')) {
            $search = trim((string) $request->search);

            $query->where(function ($q) use ($search) {
                $q->where('invoice_no', 'like', "%{$search}%")
                    ->orWhereHas('quotation.request', function ($requestQuery) use ($search) {
                        $requestQuery->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%")
                            ->orWhere('service_type', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('invoice_date_from')) {
            $query->whereDate('invoice_date', '>=', $request->invoice_date_from);
        }

        if ($request->filled('invoice_date_to')) {
            $query->whereDate('invoice_date', '<=', $request->invoice_date_to);
        }

        $summaryInvoices = (clone $query)
            ->with(['paymentSchedules'])
            ->get();

        $summary = [
            'total_invoices' => $summaryInvoices->count(),
            'total_amount' => $summaryInvoices->sum(fn ($invoice) => (float) $invoice->total_amount),
            'total_paid' => $summaryInvoices->sum(function ($invoice) {
                return (float) $invoice->paymentSchedules->sum('amount_paid');
            }),
            'total_remaining' => $summaryInvoices->sum(function ($invoice) {
                $paid = (float) $invoice->paymentSchedules->sum('amount_paid');
                return max((float) $invoice->total_amount - $paid, 0);
            }),
            'paid_invoices' => $summaryInvoices->filter(function ($invoice) {
                $paid = (float) $invoice->paymentSchedules->sum('amount_paid');
                return strtolower((string) $invoice->status) === 'paid'
                    || ((float) $invoice->total_amount > 0 && $paid >= (float) $invoice->total_amount);
            })->count(),
        ];

        $invoices = $query
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('hr.invoices.index', compact('invoices', 'summary'));
    }

    public function create(Quotation $quotation)
    {
        $quotation->load(['request', 'items', 'invoice']);

        if ($quotation->invoice) {
            return redirect()
                ->route('hr.invoices.show', $quotation->invoice)
                ->with('info', 'An invoice already exists for this quotation.');
        }

        return view('hr.invoices.create', compact('quotation'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'quotation_id' => ['required', 'exists:quotations,id'],
            'invoice_date' => ['required', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:invoice_date'],
            'description' => ['nullable', 'string'],
        ]);

        $quotation = Quotation::with(['request', 'items', 'invoice'])->findOrFail($validated['quotation_id']);

        if ($quotation->invoice) {
            return back()->withErrors([
                'quotation_id' => 'This quotation already has an invoice.'
            ])->withInput();
        }

        if ($quotation->items->isEmpty()) {
            return back()->withErrors([
                'quotation_id' => 'This quotation has no items to invoice.'
            ])->withInput();
        }

        $invoice = null;

        DB::transaction(function () use ($validated, $quotation, &$invoice) {
            $invoice = Invoice::create([
                'quotation_id' => $quotation->id,
                'invoice_no' => $this->generateInvoiceNumber(),
                'status' => 'unpaid',
                'invoice_date' => $validated['invoice_date'],
                'due_date' => $validated['due_date'] ?? null,
                'description' => $validated['description'] ?? null,
                'total_amount' => (float) $quotation->grand_total,
                'created_by' => Auth::id(),
            ]);

            foreach ($quotation->items as $item) {
                $invoice->items()->create([
                    'description' => $item->description,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'total_price' => $item->total_price,
                ]);
            }

            $this->generatePaymentSchedules($invoice, $quotation);
        });

        return redirect()
            ->route('hr.invoices.show', $invoice)
            ->with('success', 'Invoice created successfully.');
    }

    public function show(Invoice $invoice)
    {
        $invoice->load([
            'quotation.request',
            'items',
            'creator',
            'paymentSchedules',
            'payments.paymentSchedule',
            'payments.receipt',
        ]);

        return view('hr.invoices.show', compact('invoice'));
    }

    protected function generateInvoiceNumber(): string
    {
        $nextId = (Invoice::max('id') ?? 0) + 1;

        return 'INV-' . now()->format('Y') . '-' . str_pad((string) $nextId, 4, '0', STR_PAD_LEFT);
    }

    protected function generatePaymentSchedules(Invoice $invoice, Quotation $quotation): void
    {
        $paymentTerms = $quotation->payment_terms_json ?? [];

        if (empty($paymentTerms['phases']) || !is_array($paymentTerms['phases'])) {
            return;
        }

        $invoiceDate = $invoice->invoice_date
            ? \Carbon\Carbon::parse($invoice->invoice_date)
            : now();

        foreach ($paymentTerms['phases'] as $index => $phase) {
            $invoice->paymentSchedules()->create([
                'label' => $phase['label'] ?? ('Phase ' . ($index + 1)),
                'percent' => (float) ($phase['percent'] ?? 0),
                'amount_due' => (float) ($phase['amount'] ?? 0),
                'amount_paid' => 0,
                'due_date' => $invoiceDate->copy()->addDays($index * 15)->toDateString(),
                'status' => 'pending',
                'notes' => null,
                'sort_order' => $index + 1,
            ]);
        }
    }
}
