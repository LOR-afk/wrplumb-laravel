<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\QuotationRequest;
use App\Services\AlertService;
use Illuminate\Http\Request;
use App\Models\InspectorAvailability;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class MyRequestController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $requests = QuotationRequest::with('worker', 'jobOrder')
            ->where('email', $user->email)
            ->latest()
            ->paginate(10);

        $requests->getCollection()->transform(function ($quotation) {
            return $this->attachDisplayStatus($quotation);
        });

        return view('client.requests.index', compact('requests'));
    }

    public function show(QuotationRequest $quotation)
    {
        $user = Auth::user();

        abort_if($quotation->email !== $user->email, 403);

        $quotation->load('worker', 'jobOrder');

        $quotation = $this->attachDisplayStatus($quotation);

        return view('client.requests.show', compact('quotation'));
    }

    protected function attachDisplayStatus(QuotationRequest $quotation): QuotationRequest
    {
        $hasJobOrder = $quotation->jobOrder !== null;

        $displayStatus = $hasJobOrder
            ? ($quotation->jobOrder->status ?? 'pending')
            : ($quotation->status ?? 'pending');

        $quotation->setAttribute('has_job_order', $hasJobOrder);
        $quotation->setAttribute('display_status_type', $hasJobOrder ? 'job_order' : 'request');
        $quotation->setAttribute('display_status_label', $hasJobOrder ? 'Job Order Status' : 'Request Status');
        $quotation->setAttribute('display_status', $displayStatus);
        $quotation->setAttribute('display_status_badge', $this->resolveStatusBadge($displayStatus));

        return $quotation;
    }

    protected function resolveStatusBadge(?string $status): string
    {
        return match (strtolower((string) $status)) {
            'pending' => 'warning',
            'approved', 'accepted', 'assigned' => 'primary',
            'ongoing', 'in_progress', 'in-progress' => 'info',
            'completed', 'done' => 'success',
            'cancelled', 'canceled', 'rejected', 'declined' => 'danger',
            default => 'secondary',
        };
    }

    protected function ownsQuotation(QuotationRequest $quotation): bool
    {
        return Auth::check() && Auth::user()->email === $quotation->email;
    }

    public function requestReschedule(Request $request, QuotationRequest $quotation)
    {
        abort_unless($this->ownsQuotation($quotation), 403);

        if (!in_array($quotation->appointment_status, ['approved', 'rescheduled'])) {
            return back()->withErrors([
                'client_reschedule' => 'Only scheduled appointments can be requested for reschedule.'
            ]);
        }

        if ($quotation->client_action_status === 'pending') {
            return back()->withErrors([
                'client_reschedule' => 'You already have a pending client request for this appointment.'
            ]);
        }

        $validated = $request->validate([
            'client_requested_date' => ['required', 'date'],
            'client_requested_time' => ['required', 'date_format:H:i'],
            'client_request_reason' => ['required', 'string', 'max:1000'],
        ]);

        $quotation->update([
            'client_action_request' => 'reschedule',
            'client_action_status' => 'pending',
            'client_requested_date' => $validated['client_requested_date'],
            'client_requested_time' => $validated['client_requested_time'],
            'client_request_reason' => $validated['client_request_reason'],
            'client_requested_at' => now(),
            'client_request_reviewed_at' => null,
            'client_request_review_notes' => null,
        ]);

        AlertService::sendToRole(
            'admin',
            'Client requested reschedule',
            "{$quotation->full_name} requested to reschedule to {$validated['client_requested_date']} at {$validated['client_requested_time']}.",
            route('admin.quotations.index'),
            'warning'
        );

        return back()->with('success', 'Reschedule request sent successfully.');
    }

    public function requestCancel(Request $request, QuotationRequest $quotation)
    {
        abort_unless($this->ownsQuotation($quotation), 403);

        if ($quotation->status === 'completed') {
            return back()->withErrors([
                'client_cancel' => 'Completed requests can no longer be cancelled.'
            ]);
        }

        if ($quotation->client_action_status === 'pending') {
            return back()->withErrors([
                'client_cancel' => 'You already have a pending client request for this appointment.'
            ]);
        }

        $validated = $request->validate([
            'client_request_reason' => ['required', 'string', 'max:1000'],
        ]);

        $quotation->update([
            'client_action_request' => 'cancel',
            'client_action_status' => 'pending',
            'client_requested_date' => null,
            'client_requested_time' => null,
            'client_request_reason' => $validated['client_request_reason'],
            'client_requested_at' => now(),
            'client_request_reviewed_at' => null,
            'client_request_review_notes' => null,
        ]);

        AlertService::sendToRole(
            'admin',
            'Client requested cancellation',
            "{$quotation->full_name} requested cancellation of an appointment.",
            route('admin.quotations.index'),
            'warning'
        );

        return back()->with('success', 'Cancellation request sent successfully.');
    }

    public function calendarAvailability(Request $request)
    {
        $validated = $request->validate([
            'month' => ['required', 'date_format:Y-m'],
        ]);

        $month = Carbon::createFromFormat('Y-m', $validated['month'])->startOfMonth();
        $start = $month->copy()->startOfMonth()->toDateString();
        $end = $month->copy()->endOfMonth()->toDateString();

        $days = InspectorAvailability::selectRaw('DATE(availability_date) as day, COUNT(DISTINCT inspector_id) as total')
            ->whereBetween('availability_date', [$start, $end])
            ->where('status', 'available')
            ->groupBy('day')
            ->pluck('total', 'day');

        return response()->json([
            'month' => $validated['month'],
            'days' => $days,
        ]);
    }

    public function create()
    {
        return view('client.requests.create');
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'service_category' => ['required', 'string', 'max:100'],
            'service_type' => ['required', 'string', 'max:100'],
            'project_type' => ['required', 'string', 'max:100'],
            'preferred_date' => ['nullable', 'date'],
            'preferred_time' => ['nullable', 'date_format:H:i'],
            'address' => ['required', 'string', 'max:255'],
            'details' => ['required', 'string', 'max:2000'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ]);

        $flow = $this->resolveServiceFlow($validated['service_type']);

        $fullName = trim($user->name ?? 'Client User');
        $nameParts = preg_split('/\s+/', $fullName);
        $firstName = $user->first_name ?? ($nameParts[0] ?? 'Client');
        $lastName = $user->last_name ?? (count($nameParts) > 1 ? end($nameParts) : 'User');

        $quotation = QuotationRequest::create([
            'first_name' => $firstName,
            'middle_initial' => null,
            'last_name' => $lastName,
            'email' => $user->email,
            'phone' => $user->phone ?? 'N/A',
            'service_category' => $validated['service_category'],
            'service_type' => $validated['service_type'],
            'service_flow' => $flow['service_flow'],
            'visit_purpose' => $flow['visit_purpose'],
            'flow_source' => $flow['flow_source'],
            'flow_override_reason' => null,
            'project_type' => $validated['project_type'],
            'preferred_date' => $validated['preferred_date'] ?? null,
            'preferred_time' => $validated['preferred_time'] ?? null,
            'address' => $validated['address'],
            'details' => $validated['details'],
            'status' => 'pending',
            'appointment_status' => 'pending',
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
        ]);

        AlertService::sendToRole(
            'admin',
            'New service request',
            "{$quotation->full_name} submitted a new service request.",
            route('admin.quotations.index'),
            'info'
        );

        return redirect()
            ->route('client.requests.index')
            ->with('success', 'Your service request has been submitted successfully.');
    }

    protected function resolveServiceFlow(string $serviceType): array
    {
        $normalized = strtolower(trim($serviceType));

        $directServiceTypes = collect(config('service_flow.direct_service_types', []))
            ->map(fn ($item) => strtolower(trim($item)))
            ->all();

        $isDirectService = in_array($normalized, $directServiceTypes, true);

        return [
            'service_flow' => $isDirectService ? 'direct_service' : 'inspection_required',
            'visit_purpose' => $isDirectService ? 'service' : 'inspection',
            'flow_source' => 'system',
        ];
    }
}