<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use App\Models\InspectorAvailability;
use App\Models\JobOrder;
use App\Models\LeaveRequest;
use App\Models\QuotationRequest;
use App\Services\AlertService;
use App\Services\WorkflowEmailService;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LeaveRequestController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->input('status', 'pending');

        if (!in_array($status, ['all', 'pending', 'approved', 'declined'], true)) {
            $status = 'pending';
        }

        $query = LeaveRequest::query()
            ->with(['inspector', 'reviewedBy'])
            ->latest();

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $leaveRequests = $query
            ->paginate(12)
            ->withQueryString();

        $summary = [
            'pending' => LeaveRequest::where('status', 'pending')->count(),
            'approved' => LeaveRequest::where('status', 'approved')->count(),
            'declined' => LeaveRequest::where('status', 'declined')->count(),
        ];

        return view('hr.leave-requests.index', compact(
            'leaveRequests',
            'summary',
            'status'
        ));
    }

    public function review(Request $request, LeaveRequest $leaveRequest)
    {
        $validated = $request->validate([
            'decision' => ['required', 'in:approved,declined'],
            'review_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($leaveRequest, $validated) {
            $lockedRequest = LeaveRequest::query()
                ->lockForUpdate()
                ->findOrFail($leaveRequest->id);

            if ($lockedRequest->status !== 'pending') {
                throw ValidationException::withMessages([
                    'decision' => 'This leave request has already been reviewed.',
                ]);
            }

            if ($validated['decision'] === 'approved') {
                $this->ensureNoWorkConflict($lockedRequest);

                foreach (
                    CarbonPeriod::create(
                        $lockedRequest->start_date,
                        $lockedRequest->end_date
                    ) as $date
                ) {
                    InspectorAvailability::updateOrCreate(
                        [
                            'inspector_id' => $lockedRequest->inspector_id,
                            'availability_date' => $date->format('Y-m-d'),
                        ],
                        [
                            'start_time' => null,
                            'end_time' => null,
                            'status' => 'on_leave',
                            'notes' => "HR-approved leave request #{$lockedRequest->id}",
                        ]
                    );
                }
            }

            $lockedRequest->update([
                'status' => $validated['decision'],
                'reviewed_by' => Auth::id(),
                'reviewed_at' => now(),
                'review_notes' => $validated['review_notes'] ?? null,
            ]);
        });

        $leaveRequest->refresh()->load('inspector');

        $title = $leaveRequest->status === 'approved'
            ? 'Leave request approved'
            : 'Leave request declined';

        $message = $leaveRequest->status === 'approved'
            ? 'HR approved your leave request. Your approved leave dates are now marked On Leave.'
            : 'HR declined your leave request. Check the review notes for details.';

        AlertService::send(
            $leaveRequest->inspector,
            $title,
            $message,
            route('inspector.availability.index'),
            $leaveRequest->status === 'approved' ? 'success' : 'warning'
        );

        WorkflowEmailService::leaveReviewed($leaveRequest);

        return back()->with(
            'success',
            'Leave request reviewed successfully.'
        );
    }

    private function ensureNoWorkConflict(LeaveRequest $leaveRequest): void
    {
        $jobConflict = JobOrder::query()
            ->where('worker_id', $leaveRequest->inspector_id)
            ->whereIn('status', ['scheduled', 'in_progress'])
            ->whereBetween('scheduled_date', [
                $leaveRequest->start_date->toDateString(),
                $leaveRequest->end_date->toDateString(),
            ])
            ->exists();

        $appointmentConflict = QuotationRequest::query()
            ->where('worker_id', $leaveRequest->inspector_id)
            ->whereIn('appointment_status', ['approved', 'rescheduled'])
            ->whereBetween('appointment_date', [
                $leaveRequest->start_date->toDateString(),
                $leaveRequest->end_date->toDateString(),
            ])
            ->exists();

        if ($jobConflict || $appointmentConflict) {
            throw ValidationException::withMessages([
                'decision' => 'This inspector still has scheduled or active work within the requested leave dates. Reassign or reschedule those records before approving the leave.',
            ]);
        }
    }
}
