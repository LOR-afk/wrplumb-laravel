<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use App\Models\InspectionReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InspectionReportController extends Controller
{
    public function index(Request $request)
    {
        $query = InspectionReport::query()
            ->with([
                'quotationRequest.quotation',
                'inspector',
                'reviewer',
            ])
            ->where('status', 'submitted');

        if ($request->filled('review')) {
            if ($request->review === 'reviewed') {
                $query->whereNotNull('reviewed_at');
            }

            if ($request->review === 'pending') {
                $query->whereNull('reviewed_at');
            }
        }

        if ($request->filled('search')) {
            $search = trim($request->search);

            $query->where(function ($q) use ($search) {
                $q->where('report_no', 'like', "%{$search}%")
                    ->orWhereHas('quotationRequest', function ($requestQuery) use ($search) {
                        $requestQuery
                            ->where('full_name', 'like', "%{$search}%")
                            ->orWhere('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('service_type', 'like', "%{$search}%")
                            ->orWhere('address', 'like', "%{$search}%");
                    })
                    ->orWhereHas('inspector', function ($inspectorQuery) use ($search) {
                        $inspectorQuery
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%");
                    });
            });
        }

        $summary = [
            'total' => InspectionReport::where('status', 'submitted')->count(),
            'pending_review' => InspectionReport::where('status', 'submitted')
                ->whereNull('reviewed_at')
                ->count(),
            'reviewed' => InspectionReport::where('status', 'submitted')
                ->whereNotNull('reviewed_at')
                ->count(),
            'ready_for_quotation' => InspectionReport::where('status', 'submitted')
                ->whereHas('quotationRequest', function ($q) {
                    $q->whereDoesntHave('quotation');
                })
                ->count(),
        ];

        $reports = $query
            ->latest('submitted_at')
            ->paginate(10)
            ->withQueryString();

        return view('hr.inspection-reports.index', compact('reports', 'summary'));
    }

    public function show(InspectionReport $inspectionReport)
    {
        abort_unless($inspectionReport->status === 'submitted', 404);

        $inspectionReport->load([
            'quotationRequest.quotation',
            'inspector',
            'reviewer',
            'photos.uploadedBy',
            'checklistItems.completedBy',
            'materialItems',
            'statusLogs.updatedBy',
        ]);

        return view('hr.inspection-reports.show', compact('inspectionReport'));
    }

    public function markReviewed(Request $request, InspectionReport $inspectionReport)
    {
        abort_unless($inspectionReport->status === 'submitted', 422);

        $validated = $request->validate([
            'review_notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $inspectionReport->update([
            'reviewed_at' => now(),
            'reviewed_by' => Auth::id(),
            'review_notes' => $validated['review_notes'] ?? null,
        ]);

        return back()->with('success', 'Inspection report marked as reviewed.');
    }
}