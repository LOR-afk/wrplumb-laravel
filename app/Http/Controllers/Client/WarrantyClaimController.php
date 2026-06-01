<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\JobOrder;
use App\Models\WarrantyClaim;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WarrantyClaimController extends Controller
{
    public function store(Request $request, JobOrder $jobOrder)
    {
        $jobOrder->load(['quotationRequest', 'warrantyClaims']);

        $user = Auth::user();

        if (!$user || !$jobOrder->quotationRequest) {
            abort(403);
        }

        if ($jobOrder->quotationRequest->email !== $user->email) {
            abort(403);
        }

        if ($jobOrder->status !== 'completed' || empty($jobOrder->completed_at)) {
            return back()->withErrors([
                'warranty' => 'Warranty claims can only be submitted for completed job orders.',
            ]);
        }

        $warrantyExpiresAt = $jobOrder->completed_at->copy()->addDays(30);

        if (now()->greaterThan($warrantyExpiresAt)) {
            return back()->withErrors([
                'warranty' => 'This job order is no longer within the 30-day warranty period.',
            ]);
        }

        $hasActiveClaim = WarrantyClaim::where('job_order_id', $jobOrder->id)
            ->whereIn('status', ['pending', 'approved'])
            ->exists();

        if ($hasActiveClaim) {
            return back()->withErrors([
                'warranty' => 'A pending or approved warranty claim already exists for this job order.',
            ]);
        }

        $validated = $request->validate([
            'issue_description' => ['required', 'string', 'min:10'],
            'preferred_date' => ['nullable', 'date', 'after_or_equal:today'],
            'preferred_time' => ['nullable', 'string', 'max:20'],
        ]);

        $claim = WarrantyClaim::create([
            'job_order_id' => $jobOrder->id,
            'client_id' => $user->id,
            'claim_no' => $this->generateClaimNumber(),
            'issue_description' => $validated['issue_description'],
            'preferred_date' => $validated['preferred_date'] ?? null,
            'preferred_time' => $validated['preferred_time'] ?? null,
            'status' => 'pending',
            'eligibility_status' => 'eligible',
            'warranty_expires_at' => $warrantyExpiresAt,
        ]);

        $claim->load(['jobOrder', 'client']);

        AuditLogService::log(
            'Warranty Claim Submitted',
            'Warranty Claims',
            $claim,
            null,
            [
                'claim_no' => $claim->claim_no,
                'job_order_id' => $claim->job_order_id,
                'job_order_no' => $jobOrder->job_order_no,
                'client_id' => $claim->client_id,
                'client_name' => $user->name,
                'issue_description' => $claim->issue_description,
                'preferred_date' => optional($claim->preferred_date)->format('Y-m-d'),
                'preferred_time' => $claim->preferred_time,
                'status' => $claim->status,
                'eligibility_status' => $claim->eligibility_status,
                'warranty_expires_at' => optional($claim->warranty_expires_at)->toDateTimeString(),
            ],
            "Client submitted warranty claim {$claim->claim_no} for job order {$jobOrder->job_order_no}."
        );

        return back()->with('success', 'Warranty claim submitted successfully. Please wait for admin review.');
    }

    protected function generateClaimNumber(): string
    {
        $nextId = (WarrantyClaim::max('id') ?? 0) + 1;

        return 'WC-' . now()->format('Y') . '-' . str_pad((string) $nextId, 4, '0', STR_PAD_LEFT);
    }
}