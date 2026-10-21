<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Services\AlertService;
use App\Services\WorkflowEmailService;
use Illuminate\Support\Facades\Auth;

class ContractController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $contracts = Contract::with(['quotation.request', 'generator'])
            ->whereHas('quotation.request', function ($query) use ($user) {
                $query->where('email', $user->email);
            })
            ->latest()
            ->paginate(10);

        return view('client.contracts.index', compact('contracts'));
    }

    public function show(Contract $contract)
    {
        $user = Auth::user();

        $contract->load(['quotation.request', 'quotation.items', 'generator']);

        abort_if($contract->quotation->request->email !== $user->email, 403);

        return view('client.contracts.show', compact('contract'));
    }

    public function accept(Contract $contract)
    {
        $user = Auth::user();

        $contract->load(['quotation.request']);

        abort_if($contract->quotation->request->email !== $user->email, 403);

        if (in_array($contract->status, ['accepted', 'finalized', 'cancelled', 'voided'], true)) {
            return back()->withErrors([
                'contract' => 'This contract can no longer be accepted.',
            ]);
        }

        $contract->update([
            'status' => 'accepted',
            'client_accepted_at' => now(),
            'sent_at' => $contract->sent_at ?? now(),
        ]);

        $contract->refresh()->load('quotation.request');

        AlertService::sendToRole(
            'hr',
            'Contract accepted',
            "{$user->name} accepted contract {$contract->contract_no}.",
            route('hr.contracts.show', $contract),
            'success'
        );

        AlertService::sendToRole(
            'admin',
            'Contract accepted',
            "{$user->name} accepted contract {$contract->contract_no}.",
            route('admin.quotations.index'),
            'success'
        );

        WorkflowEmailService::contractAccepted($contract);

        return redirect()
            ->route('client.contracts.show', $contract)
            ->with('success', 'Contract accepted successfully.');
    }
}
