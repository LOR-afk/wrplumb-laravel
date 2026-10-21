<?php

namespace App\Http\Controllers\Inspector;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use App\Services\AlertService;
use App\Services\WorkflowEmailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LeaveRequestController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'reason' => ['required', 'string', 'min:5', 'max:1000'],
        ]);

        $overlapExists = LeaveRequest::query()
            ->where('inspector_id', Auth::id())
            ->whereIn('status', ['pending', 'approved'])
            ->whereDate('start_date', '<=', $validated['end_date'])
            ->whereDate('end_date', '>=', $validated['start_date'])
            ->exists();

        if ($overlapExists) {
            throw ValidationException::withMessages([
                'start_date' => 'You already have a pending or approved leave request that overlaps these dates.',
            ]);
        }

        $leaveRequest = LeaveRequest::create([
            'inspector_id' => Auth::id(),
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'reason' => trim($validated['reason']),
            'status' => 'pending',
        ]);

        AlertService::sendToRole(
            'hr',
            'New leave request',
            Auth::user()->name . ' submitted a leave request.',
            route('hr.leave-requests.index'),
            'warning'
        );

        WorkflowEmailService::leaveSubmitted($leaveRequest);

        return back()->with('success', 'Leave request submitted successfully.');
    }
}
