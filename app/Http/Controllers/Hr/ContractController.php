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
        $quotation->load(['request', 'items', 'contract']);

        if ($quotation->contract) {
            return redirect()
                ->route('hr.contracts.show', $quotation->contract)
                ->with('info', 'A contract already exists for this quotation.');
        }

        return view('hr.contracts.create', compact('quotation'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'quotation_id' => ['required', 'exists:quotations,id'],
            'title' => ['nullable', 'string', 'max:255'],
            'contract_date' => ['required', 'date'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'scope_of_work' => ['nullable', 'string'],
            'special_terms' => ['nullable', 'string'],
        ]);

        $quotation = Quotation::with(['request', 'items', 'contract'])->findOrFail($validated['quotation_id']);

        if ($quotation->contract) {
            return back()->withErrors([
                'quotation_id' => 'This quotation already has a contract.'
            ])->withInput();
        }

        $contract = Contract::create([
            'quotation_id' => $quotation->id,
            'contract_no' => $this->generateContractNumber(),
            'title' => $validated['title'] ?: ('Service Contract for ' . ($quotation->request->service_type ?? 'Project')),
            'contract_date' => $validated['contract_date'],
            'start_date' => $validated['start_date'] ?? null,
            'end_date' => $validated['end_date'] ?? null,
            'status' => 'generated',
            'client_name' => trim(($quotation->request->first_name ?? '') . ' ' . ($quotation->request->middle_initial ? $quotation->request->middle_initial . '. ' : '') . ($quotation->request->last_name ?? '')),
            'client_address' => $quotation->request->address,
            'project_address' => $quotation->request->address,
            'scope_of_work' => $validated['scope_of_work'] ?: $quotation->request->details,
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
        $contract->load(['quotation.request', 'quotation.items', 'generator']);

        return view('hr.contracts.show', compact('contract'));
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

    protected function generateContractNumber(): string
    {
        $nextId = (Contract::max('id') ?? 0) + 1;

        return 'CTR-' . now()->format('Y') . '-' . str_pad((string) $nextId, 4, '0', STR_PAD_LEFT);
    }
}