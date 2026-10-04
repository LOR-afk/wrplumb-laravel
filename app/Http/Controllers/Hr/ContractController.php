<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\Quotation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ContractController extends Controller
{
    public function index()
    {
        $contracts = Contract::with(['quotation.request', 'generator'])
            ->latest()
            ->paginate(10);

        return view('hr.contracts.index', compact('contracts'));
    }

    public function create(Quotation $quotation)
    {
        $quotation->load([
            'request',
            'items',
            'scopeItems',
            'contract',
        ]);

        if ($quotation->contract) {
            return redirect()
                ->route('hr.contracts.show', $quotation->contract)
                ->with('info', 'A contract already exists for this quotation.');
        }

        if (!$this->quotationIsAccepted($quotation)) {
            return redirect()
                ->route('hr.quotations.show', $quotation)
                ->withErrors([
                    'quotation' => 'A contract can only be generated after the client accepts the quotation.',
                ]);
        }

        $quotationScope = $this->quotationScopeOfWork($quotation);

        return view('hr.contracts.create', compact(
            'quotation',
            'quotationScope'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'quotation_id' => ['required', 'exists:quotations,id'],
            'title' => ['nullable', 'string', 'max:255'],
            'contract_date' => ['required', 'date'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'scope_of_work' => ['required', 'string'],
            'special_terms' => ['nullable', 'string'],
        ]);

        $quotation = Quotation::with([
            'request',
            'items',
            'scopeItems',
            'contract',
        ])->findOrFail($validated['quotation_id']);

        if ($quotation->contract) {
            return back()
                ->withErrors([
                    'quotation_id' => 'This quotation already has a contract.',
                ])
                ->withInput();
        }

        if (!$this->quotationIsAccepted($quotation)) {
            return back()
                ->withErrors([
                    'quotation_id' => 'A contract can only be generated after the client accepts the quotation.',
                ])
                ->withInput();
        }

        $quotationScope = $this->quotationScopeOfWork($quotation);

        $contract = Contract::create([
            'quotation_id' => $quotation->id,
            'contract_no' => $this->generateContractNumber(),
            'title' => $validated['title']
                ?: ('Service Contract for ' . ($quotation->request->service_type ?? 'Project')),
            'contract_date' => $validated['contract_date'],
            'start_date' => $validated['start_date'] ?? null,
            'end_date' => $validated['end_date'] ?? null,
            'status' => 'generated',
            'revision_no' => 1,
            'client_name' => trim(
                ($quotation->request->first_name ?? '') . ' ' .
                ($quotation->request->middle_initial
                    ? $quotation->request->middle_initial . '. '
                    : '') .
                ($quotation->request->last_name ?? '')
            ),
            'client_address' => $quotation->request->address,
            'project_address' => $quotation->request->address,
            'scope_of_work' => trim($validated['scope_of_work'])
                ?: ($quotationScope ?: ($quotation->request->details ?? '')),
            'payment_terms' => $quotation->payment_terms_json,
            'total_contract_price' => (float) $quotation->grand_total,
            'special_terms' => $validated['special_terms'] ?? null,
            'generated_by' => Auth::id(),
        ]);

        return redirect()
            ->route('hr.contracts.show', $contract)
            ->with('success', 'Contract generated successfully.');
    }

    public function show(Contract $contract)
    {
        $contract->load([
            'quotation.request',
            'quotation.items',
            'quotation.scopeItems',
            'generator',
            'voider',
        ]);

        return view('hr.contracts.show', compact('contract'));
    }

    public function edit(Contract $contract)
    {
        $contract->load([
            'quotation.request',
            'quotation.items',
            'quotation.scopeItems',
            'generator',
        ]);

        if ($contract->status === 'voided') {
            return redirect()
                ->route('hr.contracts.show', $contract)
                ->withErrors([
                    'contract' => 'A voided contract can no longer be edited.',
                ]);
        }

        if ($contract->status === 'finalized') {
            return redirect()
                ->route('hr.contracts.show', $contract)
                ->withErrors([
                    'contract' => 'A finalized contract cannot be edited directly. Void it first if it must no longer remain valid.',
                ]);
        }

        return view('hr.contracts.edit', compact('contract'));
    }

    public function update(Request $request, Contract $contract)
    {
        if ($contract->status === 'voided') {
            return back()->withErrors([
                'contract' => 'A voided contract can no longer be edited.',
            ]);
        }

        if ($contract->status === 'finalized') {
            return back()->withErrors([
                'contract' => 'A finalized contract cannot be edited directly.',
            ]);
        }

        $validated = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'contract_date' => ['required', 'date'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'client_name' => ['required', 'string', 'max:255'],
            'client_address' => ['nullable', 'string', 'max:1000'],
            'project_address' => ['nullable', 'string', 'max:1000'],
            'scope_of_work' => ['required', 'string'],
            'total_contract_price' => ['required', 'numeric', 'min:0'],
            'special_terms' => ['nullable', 'string'],
        ]);

        $oldStatus = $contract->status;
        $requiresReacceptance = in_array(
            $oldStatus,
            ['sent', 'accepted'],
            true
        );

        $newTotal = (float) $validated['total_contract_price'];

        $contract->update([
            'title' => $validated['title'] ?: $contract->title,
            'contract_date' => $validated['contract_date'],
            'start_date' => $validated['start_date'] ?? null,
            'end_date' => $validated['end_date'] ?? null,
            'client_name' => $validated['client_name'],
            'client_address' => $validated['client_address'] ?? null,
            'project_address' => $validated['project_address'] ?? null,
            'scope_of_work' => $validated['scope_of_work'],
            'payment_terms' => $this->recalculatePaymentTerms(
                $contract->payment_terms,
                $newTotal
            ),
            'total_contract_price' => $newTotal,
            'special_terms' => $validated['special_terms'] ?? null,
            'revision_no' => max(1, (int) $contract->revision_no) + 1,
            'status' => $requiresReacceptance
                ? 'generated'
                : $oldStatus,
            'sent_at' => $requiresReacceptance
                ? null
                : $contract->sent_at,
            'client_accepted_at' => $requiresReacceptance
                ? null
                : $contract->client_accepted_at,
        ]);

        $message = $requiresReacceptance
            ? 'Contract revised successfully. The previous client acceptance was cleared and the revised contract must be accepted again.'
            : 'Contract revised successfully.';

        return redirect()
            ->route('hr.contracts.show', $contract)
            ->with('success', $message);
    }

    public function finalize(Contract $contract)
    {
        if ($contract->status !== 'accepted') {
            return back()->withErrors([
                'contract' => 'Only accepted contracts can be finalized.',
            ]);
        }

        $contract->update([
            'status' => 'finalized',
            'finalized_at' => now(),
        ]);

        return redirect()
            ->route('hr.contracts.show', $contract)
            ->with('success', 'Contract finalized successfully.');
    }

    public function void(Request $request, Contract $contract)
    {
        $validated = $request->validate([
            'void_reason' => ['required', 'string', 'max:1000'],
        ]);

        if ($contract->status === 'voided') {
            return redirect()
                ->route('hr.contracts.show', $contract)
                ->with('info', 'This contract is already voided.');
        }

        $contract->update([
            'status' => 'voided',
            'void_reason' => $validated['void_reason'],
            'voided_at' => now(),
            'voided_by' => Auth::id(),
        ]);

        return redirect()
            ->route('hr.contracts.show', $contract)
            ->with(
                'success',
                'Contract voided successfully. The record remains available for audit purposes.'
            );
    }

    protected function quotationIsAccepted(Quotation $quotation): bool
    {
        return $quotation->status === 'accepted'
            || $quotation->client_response === 'accepted';
    }

    protected function quotationScopeOfWork(Quotation $quotation): string
    {
        if (!$quotation->relationLoaded('scopeItems')) {
            $quotation->load('scopeItems');
        }

        return $quotation->scopeItems
            ->pluck('description')
            ->map(fn ($description) => trim((string) $description))
            ->filter()
            ->values()
            ->map(
                fn ($description, $index) =>
                    ($index + 1) . '. ' . $description
            )
            ->implode(PHP_EOL);
    }

    protected function generateContractNumber(): string
    {
        $nextId = (Contract::max('id') ?? 0) + 1;

        return 'CTR-' . now()->format('Y') . '-' . str_pad(
            (string) $nextId,
            4,
            '0',
            STR_PAD_LEFT
        );
    }

    protected function recalculatePaymentTerms(
        ?array $paymentTerms,
        float $total
    ): ?array {
        if (
            empty($paymentTerms) ||
            empty($paymentTerms['phases']) ||
            !is_array($paymentTerms['phases'])
        ) {
            return $paymentTerms;
        }

        $paymentTerms['phases'] = collect($paymentTerms['phases'])
            ->map(function ($phase) use ($total) {
                $percent = (float) ($phase['percent'] ?? 0);

                $phase['amount'] = round(
                    $total * ($percent / 100),
                    2
                );

                return $phase;
            })
            ->values()
            ->all();

        return $paymentTerms;
    }
}
