<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InspectorAvailability;
use App\Models\QuotationRequest;
use App\Models\User;
use App\Services\AlertService;
use App\Services\AuditLogService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QuotationController extends Controller
{
    public function index(Request $request)
    {
        $query = QuotationRequest::query()->with(['worker', 'jobOrder']);

        if ($request->filled('status')) {
            $status = $request->status;

            // The UI displays job-order status first when a job order exists.
            // Therefore request statuses should only show records without job orders,
            // while job_* filters should search inside the related job order.
            if (str_starts_with($status, 'job_')) {
                $jobStatus = str_replace('job_', '', $status);

                $query->whereHas('jobOrder', function ($q) use ($jobStatus) {
                    $q->where('status', $jobStatus);
                });
            } else {
                $query->where('status', $status)
                    ->whereDoesntHave('jobOrder');
            }
        }

        if ($request->filled('service_category')) {
            $query->where('service_category', $request->service_category);
        }

        if ($request->filled('preferred_date')) {
            $query->whereDate('preferred_date', $request->preferred_date);
        }

        if ($request->filled('search')) {
            $search = trim($request->search);

            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('service_type', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%");
            });
        }

        $quotations = $query
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $allActiveWorkers = User::where('role', 'worker')
            ->where('is_active', true)
            ->orderBy('first_name')
            ->get();

        $availableWorkersByQuotation = [];

        foreach ($quotations as $quotation) {
            if ($quotation->preferred_date) {
                $availableWorkers = User::where('role', 'worker')
                    ->where('is_active', true)
                    ->whereHas('inspectorAvailabilities', function ($q) use ($quotation) {
                        $q->whereDate('availability_date', $quotation->preferred_date)
                            ->where('status', 'available');
                    })
                    ->orderBy('first_name')
                    ->get();
            } else {
                $availableWorkers = $allActiveWorkers;
            }

            if ($quotation->worker && !$availableWorkers->contains('id', $quotation->worker_id)) {
                $availableWorkers->prepend($quotation->worker);
            }

            $availableWorkersByQuotation[$quotation->id] = $availableWorkers;
        }

        return view('admin.quotations.index', compact(
            'quotations',
            'availableWorkersByQuotation'
        ));
    }

    protected function inspectorIsAvailableOnDate(int $workerId, string $date): bool
    {
        return InspectorAvailability::where('inspector_id', $workerId)
            ->whereDate('availability_date', $date)
            ->where('status', 'available')
            ->exists();
    }

    protected function hasAppointmentConflict(QuotationRequest $quotation, int $workerId, string $date, string $time): bool
    {
        return QuotationRequest::where('worker_id', $workerId)
            ->whereDate('appointment_date', $date)
            ->where('appointment_time', $time)
            ->whereIn('appointment_status', ['approved', 'rescheduled'])
            ->where('id', '!=', $quotation->id)
            ->exists();
    }

    protected function getClientUser(QuotationRequest $quotation): ?User
    {
        return User::where('role', 'client')
            ->where('email', $quotation->email)
            ->first();
    }

    protected function syncRequestStatusFromFlow(QuotationRequest $quotation): void
    {
        if ($quotation->appointment_status === 'cancelled') {
            $quotation->status = 'pending';
            return;
        }

        if (!empty($quotation->worker_id)) {
            $quotation->status = 'assigned';
            return;
        }

        $quotation->status = 'pending';
    }

    public function assignWorker(Request $request, QuotationRequest $quotation)
    {
        $validated = $request->validate([
            'worker_id' => ['required', 'exists:users,id'],
            'admin_notes' => ['nullable', 'string'],
        ]);

        $worker = User::where('id', $validated['worker_id'])
            ->where('role', 'worker')
            ->firstOrFail();

        if ($quotation->preferred_date) {
            $isAvailable = $this->inspectorIsAvailableOnDate(
                $worker->id,
                date('Y-m-d', strtotime($quotation->preferred_date))
            );

            if (!$isAvailable) {
                return back()->withErrors([
                    'worker_id' => 'Selected inspector is not marked available on the preferred date.'
                ])->withInput();
            }
        }

        if ($quotation->appointment_date && $quotation->appointment_time) {
            $hasConflict = $this->hasAppointmentConflict(
                $quotation,
                $worker->id,
                date('Y-m-d', strtotime($quotation->appointment_date)),
                $quotation->appointment_time
            );

            if ($hasConflict) {
                return back()->withErrors([
                    'worker_id' => 'Selected inspector already has an appointment at this schedule.'
                ])->withInput();
            }
        }

        $oldValues = $quotation->only([
            'worker_id',
            'assigned_by',
            'assigned_at',
            'admin_notes',
            'status',
            'appointment_date',
            'appointment_time',
        ]);

        $quotation->worker_id = $worker->id;
        $quotation->assigned_by = Auth::id();
        $quotation->assigned_at = now();
        $quotation->admin_notes = $validated['admin_notes'] ?? null;

        if (
            $quotation->service_flow === 'direct_service' &&
            empty($quotation->appointment_date) &&
            !empty($quotation->preferred_date)
        ) {
            $quotation->appointment_date = $quotation->preferred_date;
        }

        if (
            $quotation->service_flow === 'direct_service' &&
            empty($quotation->appointment_time) &&
            !empty($quotation->preferred_time)
        ) {
            $quotation->appointment_time = $quotation->preferred_time;
        }

        $this->syncRequestStatusFromFlow($quotation);
        $quotation->save();

        AuditLogService::log(
            $quotation->service_flow === 'direct_service' ? 'Admin Assigned Personnel' : 'Admin Assigned Inspector',
            'Quotation Requests',
            $quotation,
            $oldValues,
            [
                'worker_id' => $quotation->worker_id,
                'worker_name' => $worker->name,
                'assigned_by' => $quotation->assigned_by,
                'assigned_at' => optional($quotation->assigned_at)->toDateTimeString(),
                'admin_notes' => $quotation->admin_notes,
                'status' => $quotation->status,
                'appointment_date' => optional($quotation->appointment_date)->format('Y-m-d'),
                'appointment_time' => $quotation->appointment_time,
                'service_flow' => $quotation->service_flow,
            ],
            "Admin assigned {$worker->name} to quotation request #{$quotation->id}."
        );

        $client = $this->getClientUser($quotation);

        AlertService::send(
            $client,
            'Request assigned',
            "Your request has been assigned to {$worker->name}.",
            route('client.requests.show', $quotation),
            'info'
        );

        AlertService::send(
            $worker,
            'New request assigned',
            'A new service request has been assigned to you.',
            route('inspector.quotations.show', $quotation),
            'info'
        );

        return back()->with('success', $quotation->service_flow === 'direct_service'
            ? 'Personnel assigned successfully. Preferred date/time was applied as service schedule when available.'
            : 'Inspector assigned successfully.'
        );
    }

    protected function normalizeAppointmentTime(?string $time): ?string
    {
        if ($time === null) {
            return null;
        }

        $time = strtoupper(trim($time));

        if ($time === '') {
            return null;
        }

        $formats = [
            'H:i',
            'H:i:s',
            'h:i A',
            'g:i A',
            'h:iA',
            'g:iA',
        ];

        foreach ($formats as $format) {
            try {
                return Carbon::createFromFormat($format, $time)->format('H:i');
            } catch (\Exception $e) {
                // Try the next accepted format.
            }
        }

        try {
            return Carbon::parse($time)->format('H:i');
        } catch (\Exception $e) {
            return $time;
        }
    }

    public function updateAppointment(Request $request, QuotationRequest $quotation)
    {
        if ($request->filled('appointment_time')) {
            $request->merge([
                'appointment_time' => $this->normalizeAppointmentTime($request->appointment_time),
            ]);
        }

        $validated = $request->validate([
            'appointment_status' => ['required', 'in:pending,approved,rescheduled,cancelled'],
            'appointment_date' => ['nullable', 'date'],
            'appointment_time' => ['nullable', 'date_format:H:i'],
            'cancel_reason' => ['nullable', 'string'],
        ]);

        $status = $validated['appointment_status'];

        if (
            $quotation->service_flow === 'direct_service' &&
            in_array($status, ['approved', 'rescheduled']) &&
            empty($quotation->worker_id)
        ) {
            return back()->withErrors([
                'worker_id' => 'Assign personnel first before approving a direct service schedule.',
            ])->withInput();
        }

        if (in_array($status, ['approved', 'rescheduled'])) {
            if (!$quotation->worker_id) {
                return back()->withErrors([
                    'appointment_status' => 'Assign an inspector first before approving or rescheduling.'
                ])->withInput();
            }

            if (empty($validated['appointment_date']) || empty($validated['appointment_time'])) {
                return back()->withErrors([
                    'appointment_date' => 'Appointment date and time are required for approval or reschedule.'
                ])->withInput();
            }

            if (!$this->inspectorIsAvailableOnDate($quotation->worker_id, $validated['appointment_date'])) {
                return back()->withErrors([
                    'appointment_date' => 'The assigned inspector is not marked available on this appointment date.'
                ])->withInput();
            }

            if ($this->hasAppointmentConflict(
                $quotation,
                $quotation->worker_id,
                $validated['appointment_date'],
                $validated['appointment_time']
            )) {
                return back()->withErrors([
                    'appointment_time' => 'Scheduling conflict detected. This inspector already has an appointment at that exact date and time.'
                ])->withInput();
            }
        }

        $oldValues = $quotation->only([
            'appointment_status',
            'appointment_date',
            'appointment_time',
            'approved_at',
            'rescheduled_at',
            'cancelled_at',
            'cancel_reason',
            'status',
        ]);

        $payload = [
            'appointment_status' => $status,
        ];

        if ($status === 'approved') {
            $payload['appointment_date'] = $validated['appointment_date'];
            $payload['appointment_time'] = $validated['appointment_time'];
            $payload['approved_at'] = now();
            $payload['rescheduled_at'] = null;
            $payload['cancelled_at'] = null;
            $payload['cancel_reason'] = null;
        }

        if ($status === 'rescheduled') {
            $payload['appointment_date'] = $validated['appointment_date'];
            $payload['appointment_time'] = $validated['appointment_time'];
            $payload['rescheduled_at'] = now();
            $payload['cancelled_at'] = null;
            $payload['cancel_reason'] = null;
        }

        if ($status === 'cancelled') {
            $payload['cancelled_at'] = now();
            $payload['cancel_reason'] = $validated['cancel_reason'] ?? null;
        }

        if ($status === 'pending') {
            $payload['appointment_date'] = null;
            $payload['appointment_time'] = null;
            $payload['approved_at'] = null;
            $payload['rescheduled_at'] = null;
            $payload['cancelled_at'] = null;
            $payload['cancel_reason'] = null;
        }

        $quotation->fill($payload);
        $this->syncRequestStatusFromFlow($quotation);
        $quotation->save();

        $scheduleLabel = $quotation->service_flow === 'direct_service' ? 'Service' : 'Inspection';

        $action = match ($status) {
            'approved' => "Admin Scheduled {$scheduleLabel}",
            'rescheduled' => "Admin Rescheduled {$scheduleLabel}",
            'cancelled' => "Admin Cancelled {$scheduleLabel}",
            default => "Admin Updated {$scheduleLabel} Schedule",
        };

        AuditLogService::log(
            $action,
            'Quotation Requests',
            $quotation,
            $oldValues,
            [
                'appointment_status' => $quotation->appointment_status,
                'appointment_date' => optional($quotation->appointment_date)->format('Y-m-d'),
                'appointment_time' => $quotation->appointment_time,
                'approved_at' => optional($quotation->approved_at)->toDateTimeString(),
                'rescheduled_at' => optional($quotation->rescheduled_at)->toDateTimeString(),
                'cancelled_at' => optional($quotation->cancelled_at)->toDateTimeString(),
                'cancel_reason' => $quotation->cancel_reason,
                'status' => $quotation->status,
                'service_flow' => $quotation->service_flow,
            ],
            "Admin updated the {$scheduleLabel} schedule for quotation request #{$quotation->id}."
        );

        $client = $this->getClientUser($quotation);
        $inspector = $quotation->worker;

        if ($status === 'approved') {
            AlertService::send(
                $client,
                'Appointment approved',
                'Your appointment has been approved.',
                route('client.requests.show', $quotation),
                'success'
            );

            AlertService::send(
                $inspector,
                'Appointment confirmed',
                'An assigned request now has a confirmed appointment schedule.',
                route('inspector.quotations.show', $quotation),
                'info'
            );
        }

        if ($status === 'rescheduled') {
            AlertService::send(
                $client,
                'Appointment rescheduled',
                'Your appointment schedule has been updated.',
                route('client.requests.show', $quotation),
                'warning'
            );

            AlertService::send(
                $inspector,
                'Appointment rescheduled',
                'An assigned request schedule has been updated.',
                route('inspector.quotations.show', $quotation),
                'warning'
            );
        }

        if ($status === 'cancelled') {
            AlertService::send(
                $client,
                'Appointment cancelled',
                'Your appointment has been cancelled.',
                route('client.requests.show', $quotation),
                'danger'
            );

            AlertService::send(
                $inspector,
                'Appointment cancelled',
                'An assigned request has been cancelled.',
                route('inspector.quotations.show', $quotation),
                'danger'
            );
        }

        return back()->with('success', $quotation->service_flow === 'direct_service'
            ? 'Service schedule updated successfully.'
            : 'Appointment updated successfully.'
        );
    }

    public function reviewClientRequest(Request $request, QuotationRequest $quotation)
    {
        $validated = $request->validate([
            'decision' => ['required', 'in:approved,declined'],
            'client_request_review_notes' => ['nullable', 'string'],
        ]);

        if ($quotation->client_action_status !== 'pending' || !$quotation->client_action_request) {
            return back()->withErrors([
                'decision' => 'There is no pending client request to review.'
            ]);
        }

        $oldValues = $quotation->only([
            'appointment_status',
            'appointment_date',
            'appointment_time',
            'cancelled_at',
            'cancel_reason',
            'client_action_request',
            'client_action_status',
            'client_requested_date',
            'client_requested_time',
            'client_request_reason',
            'client_request_reviewed_at',
            'client_request_review_notes',
        ]);

        $client = $this->getClientUser($quotation);
        $inspector = $quotation->worker;

        if ($validated['decision'] === 'approved') {
            if ($quotation->client_action_request === 'reschedule') {
                if (!$quotation->worker_id) {
                    return back()->withErrors([
                        'decision' => 'Assign an inspector first before approving reschedule.'
                    ]);
                }

                if (!$quotation->client_requested_date || !$quotation->client_requested_time) {
                    return back()->withErrors([
                        'decision' => 'Requested reschedule date/time is missing.'
                    ]);
                }

                $requestedDate = date('Y-m-d', strtotime($quotation->client_requested_date));

                if (!$this->inspectorIsAvailableOnDate($quotation->worker_id, $requestedDate)) {
                    return back()->withErrors([
                        'decision' => 'Assigned inspector is not available on the requested date.'
                    ]);
                }

                if ($this->hasAppointmentConflict(
                    $quotation,
                    $quotation->worker_id,
                    $requestedDate,
                    $quotation->client_requested_time
                )) {
                    return back()->withErrors([
                        'decision' => 'Scheduling conflict detected for the requested date/time.'
                    ]);
                }

                $quotation->update([
                    'appointment_status' => 'rescheduled',
                    'appointment_date' => $quotation->client_requested_date,
                    'appointment_time' => $quotation->client_requested_time,
                    'rescheduled_at' => now(),
                ]);

                AlertService::send(
                    $client,
                    'Reschedule request approved',
                    'Your appointment reschedule request has been approved.',
                    route('client.requests.show', $quotation),
                    'success'
                );

                AlertService::send(
                    $inspector,
                    'Appointment rescheduled',
                    'An assigned appointment was rescheduled after client request.',
                    route('inspector.quotations.show', $quotation),
                    'warning'
                );
            }

            if ($quotation->client_action_request === 'cancel') {
                $quotation->update([
                    'appointment_status' => 'cancelled',
                    'cancelled_at' => now(),
                    'cancel_reason' => $quotation->client_request_reason,
                ]);

                AlertService::send(
                    $client,
                    'Cancellation request approved',
                    'Your cancellation request has been approved.',
                    route('client.requests.show', $quotation),
                    'success'
                );

                AlertService::send(
                    $inspector,
                    'Appointment cancelled',
                    'An assigned appointment was cancelled after client request.',
                    route('inspector.quotations.show', $quotation),
                    'danger'
                );
            }
        }

        if ($validated['decision'] === 'declined') {
            AlertService::send(
                $client,
                'Client request declined',
                'Your reschedule/cancellation request was declined by Admin.',
                route('client.requests.show', $quotation),
                'danger'
            );
        }

        $quotation->update([
            'client_action_status' => $validated['decision'],
            'client_request_reviewed_at' => now(),
            'client_request_review_notes' => $validated['client_request_review_notes'] ?? null,
        ]);

        $quotation->refresh();

        AuditLogService::log(
            'Admin Reviewed Client Request',
            'Quotation Requests',
            $quotation,
            $oldValues,
            [
                'decision' => $validated['decision'],
                'client_action_request' => $quotation->client_action_request,
                'client_action_status' => $quotation->client_action_status,
                'appointment_status' => $quotation->appointment_status,
                'appointment_date' => optional($quotation->appointment_date)->format('Y-m-d'),
                'appointment_time' => $quotation->appointment_time,
                'cancelled_at' => optional($quotation->cancelled_at)->toDateTimeString(),
                'cancel_reason' => $quotation->cancel_reason,
                'client_request_reviewed_at' => optional($quotation->client_request_reviewed_at)->toDateTimeString(),
                'client_request_review_notes' => $quotation->client_request_review_notes,
            ],
            "Admin {$validated['decision']} the client's {$quotation->client_action_request} request for quotation request #{$quotation->id}."
        );

        return back()->with('success', 'Client request reviewed successfully.');
    }

    public function updateFlow(Request $request, QuotationRequest $quotation)
    {
        $validated = $request->validate([
            'service_flow' => ['required', 'in:direct_service,inspection_required'],
            'flow_override_reason' => ['nullable', 'string', 'max:1000'],
        ]);

        $oldValues = $quotation->only([
            'service_flow',
            'visit_purpose',
            'flow_source',
            'flow_override_reason',
        ]);

        $visitPurpose = $validated['service_flow'] === 'direct_service'
            ? 'service'
            : 'inspection';

        $quotation->update([
            'service_flow' => $validated['service_flow'],
            'visit_purpose' => $visitPurpose,
            'flow_source' => 'manual',
            'flow_override_reason' => $validated['flow_override_reason'] ?: null,
        ]);

        $quotation->refresh();

        AuditLogService::log(
            'Admin Changed Service Flow',
            'Quotation Requests',
            $quotation,
            $oldValues,
            [
                'service_flow' => $quotation->service_flow,
                'visit_purpose' => $quotation->visit_purpose,
                'flow_source' => $quotation->flow_source,
                'flow_override_reason' => $quotation->flow_override_reason,
            ],
            "Admin changed the service flow for quotation request #{$quotation->id}."
        );

        return back()->with('success', 'Service flow updated successfully.');
    }
}
