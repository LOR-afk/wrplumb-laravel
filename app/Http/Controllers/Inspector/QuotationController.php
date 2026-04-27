<?php

namespace App\Http\Controllers\Inspector;

use App\Http\Controllers\Controller;
use App\Models\QuotationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Services\AlertService;

class QuotationController extends Controller
{
    public function index()
    {
        $quotations = QuotationRequest::where('worker_id', Auth::id())
            ->latest()
            ->paginate(10);

        return view('inspector.quotations.index', compact('quotations'));
    }

    public function show(QuotationRequest $quotation)
    {
        abort_if($quotation->worker_id !== Auth::id(), 403);

        return view('inspector.quotations.show', compact('quotation'));
    }

    public function update(Request $request, QuotationRequest $quotation)
    {
        abort_if($quotation->worker_id !== Auth::id(), 403);

        $validated = $request->validate([
            'status' => ['required', 'in:assigned,in_progress,completed'],
            'inspector_notes' => ['nullable', 'string'],
        ]);

        $data = [
            'status' => $validated['status'],
            'inspector_notes' => $validated['inspector_notes'] ?? null,
        ];

        if ($validated['status'] === 'in_progress' && !$quotation->inspected_at) {
            $data['inspected_at'] = now();
        }

        if ($validated['status'] === 'completed' && !$quotation->completed_at) {
            $data['completed_at'] = now();
        }

        $quotation->update($data);

                $client = User::where('role', 'client')
                ->where('email', $quotation->email)
                ->first();

            if ($validated['status'] === 'in_progress') {
                if (!$quotation->inspected_at) {
                    $quotation->update(['inspected_at' => now()]);
                }

                AlertService::send(
                    $client,
                    'Request in progress',
                    'Your service request is now in progress.',
                    route('client.requests.show', $quotation),
                    'info'
                );
            }

            if ($validated['status'] === 'completed') {
                if (!$quotation->completed_at) {
                    $quotation->update(['completed_at' => now()]);
                }
                AlertService::sendToRole(
                'admin',
                'Request completed',
               "{$quotation->full_name} service request was marked completed by " . Auth::user()->name . ".",
                route('admin.quotations.index'),
                'success'
                );

                AlertService::send(
                    $client,
                    'Request completed',
                    'Your service request has been completed.',
                    route('client.requests.show', $quotation),
                    'success'
                );
            }

        return back()->with('success', 'Request updated successfully.');
    }
}