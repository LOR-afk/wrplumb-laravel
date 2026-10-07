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



use Illuminate\Support\Facades\Schema;



use Illuminate\Support\Facades\Route as RouteFacade;



use Illuminate\Support\Facades\DB;



use Illuminate\Pagination\LengthAwarePaginator;







class QuotationController extends Controller



{



    public function index(Request $request)
    {
        $query = QuotationRequest::query()
            ->with([
                'worker',
                'inspectionJobOrder.worker',
                'serviceJobOrder.worker',
                'inspectionReport',
                'quotation',
            ])
            ->whereNull('archived_at');

        if ($request->filled('status')) {
            $status = $request->status;

            if (str_starts_with($status, 'job_')) {
                $jobStatus = str_replace('job_', '', $status);

                $query->whereHas('jobOrders', function ($q) use ($jobStatus) {
                    $q->where('status', $jobStatus);
                });
            } else {
                $query->where('status', $status)
                    ->whereDoesntHave('jobOrders');
            }
        }

        if ($request->filled('record_type')) {
            $recordType = $request->record_type;

            if ($recordType === 'inspection') {
                $query->where('service_flow', 'inspection_required')
                    ->whereDoesntHave('serviceJobOrder');
            }

            if ($recordType === 'service') {
                $query->where(function ($q) {
                    $q->where('service_flow', 'direct_service')
                        ->orWhereHas('serviceJobOrder');
                });
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
                    ->orWhere('address', 'like', "%{$search}%")
                    ->orWhereHas('jobOrders', function ($jobQuery) use ($search) {
                        $jobQuery->where('job_order_no', 'like', "%{$search}%");
                    });
            });
        }

        $sortOptions = [
            'preferred_date_desc' => ['preferred_date', 'desc'],
            'preferred_date_asc' => ['preferred_date', 'asc'],
            'client_asc' => ['client', 'asc'],
            'client_desc' => ['client', 'desc'],
            'created_at_desc' => ['created_at', 'desc'],
            'created_at_asc' => ['created_at', 'asc'],
        ];

        $sort = $request->input('sort');

        if (!$sort || !array_key_exists($sort, $sortOptions)) {
            $legacySortBy = $request->input('sort_by', 'preferred_date');
            $legacySortDir = $request->input('sort_dir', 'desc');
            $legacyKey = $legacySortBy . '_' . $legacySortDir;

            $sort = array_key_exists($legacyKey, $sortOptions)
                ? $legacyKey
                : 'preferred_date_desc';
        }

        [$sortBy, $sortDir] = $sortOptions[$sort];

        $filteredRequests = $query->get();

        $sortValue = function (QuotationRequest $quotation) use ($sortBy) {
            $clientName = trim(
                ($quotation->first_name ?? '') . ' ' .
                    ($quotation->last_name ?? '')
            );

            $activeStatus = $quotation->serviceJobOrder?->status
                ?? $quotation->inspectionJobOrder?->status
                ?? $quotation->status
                ?? '';

            return match ($sortBy) {
                'client' => strtolower($clientName ?: ($quotation->email ?? '')),
                'status' => strtolower((string) $activeStatus),
                'service_type' => strtolower((string) ($quotation->service_type ?? '')),
                'created_at' => optional($quotation->created_at)->timestamp ?? 0,
                default => optional($quotation->preferred_date)->timestamp ?? 0,
            };
        };

        $filteredRequests = $sortDir === 'asc'
            ? $filteredRequests->sortBy($sortValue, SORT_REGULAR)
            : $filteredRequests->sortByDesc($sortValue, SORT_REGULAR);

        $filteredRequests = $filteredRequests->values();
        $requestTotalCount = $filteredRequests->count();

        $groupedClients = $filteredRequests
            ->groupBy(function (QuotationRequest $quotation) {
                $email = strtolower(trim((string) $quotation->email));

                if ($email !== '') {
                    return 'email:' . $email;
                }

                $name = strtolower(trim(
                    ($quotation->first_name ?? '') . ' ' .
                        ($quotation->last_name ?? '')
                ));

                $phone = preg_replace('/\\D+/', '', (string) $quotation->phone);

                return 'fallback:' . $name . '|' . $phone;
            })
            ->map(function ($requests, $clientKey) {
                $first = $requests->first();

                $clientName = $first->full_name
                    ?? trim(
                        ($first->first_name ?? '') . ' ' .
                            ($first->last_name ?? '')
                    )
                    ?: 'Unnamed Client';

                $latestRequest = $requests
                    ->sortByDesc(fn($item) => optional($item->preferred_date)->timestamp ?? 0)
                    ->first();

                return [
                    'key' => $clientKey,
                    'client_name' => $clientName,
                    'email' => $first->email,
                    'phone' => $first->phone,
                    'requests' => $requests->values(),
                    'record_count' => $requests->count(),
                    'latest_request_date' => $latestRequest?->preferred_date,
                ];
            })
            ->values();

        $page = LengthAwarePaginator::resolveCurrentPage('page');
        $clientsPerPage = 6;

        $clientGroups = new LengthAwarePaginator(
            $groupedClients->forPage($page, $clientsPerPage)->values(),
            $groupedClients->count(),
            $clientsPerPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
                'pageName' => 'page',
            ]
        );

        $quotations = $clientGroups
            ->getCollection()
            ->flatMap(fn($group) => $group['requests'])
            ->values();

        $allActiveWorkers = User::whereIn('role', ['inspector', 'worker'])
            ->where('is_active', true)
            ->orderBy('first_name')
            ->get();

        $availableWorkersByQuotation = [];

        foreach ($quotations as $quotation) {
            if ($quotation->preferred_date) {
                $availableWorkers = User::whereIn('role', ['inspector', 'worker'])
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
            'clientGroups',
            'quotations',
            'requestTotalCount',
            'availableWorkersByQuotation',
            'sort',
            'sortBy',
            'sortDir'
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



            ->where('role', ['inspector', 'worker'])



            ->firstOrFail();







        if ($quotation->preferred_date) {



            $isAvailable = $this->inspectorIsAvailableOnDate(



                $worker->id,



                date('Y-m-d', strtotime($quotation->preferred_date))



            );







            if (!$isAvailable) {



                return $this->workflowError($request, [



                    'worker_id' => 'Selected inspector is not marked available on the preferred date.'



                ], 'Selected inspector is not marked available on the preferred date.');
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



                return $this->workflowError($request, [



                    'worker_id' => 'Selected inspector already has an appointment at this schedule.'



                ], 'Selected inspector already has an appointment at this schedule.');
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







        $message = $quotation->service_flow === 'direct_service'



            ? 'Personnel assigned successfully. Please confirm the service schedule next.'



            : 'Inspector assigned successfully.';







        return $this->workflowSuccess($request, $message, [



            'request_id' => $quotation->id,



            'next_step' => 3,



            'status' => $quotation->status,



            'worker_name' => $worker->name,



        ]);
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



            return $this->workflowError($request, [



                'worker_id' => 'Assign personnel first before approving a direct service schedule.',



            ], 'Assign personnel first before confirming a direct service schedule.');
        }







        if (in_array($status, ['approved', 'rescheduled'])) {



            if (!$quotation->worker_id) {



                return $this->workflowError($request, [



                    'appointment_status' => 'Assign an inspector first before approving or rescheduling.'



                ], 'Assign an inspector first before confirming the schedule.');
            }







            if (empty($validated['appointment_date']) || empty($validated['appointment_time'])) {



                return $this->workflowError($request, [



                    'appointment_date' => 'Appointment date and time are required for approval or reschedule.'



                ], 'Appointment date and time are required.');
            }







            if (!$this->inspectorIsAvailableOnDate($quotation->worker_id, $validated['appointment_date'])) {



                return $this->workflowError($request, [



                    'appointment_date' => 'The assigned inspector is not marked available on this appointment date.'



                ], 'The assigned inspector is not marked available on this appointment date.');
            }







            if ($this->hasAppointmentConflict(



                $quotation,



                $quotation->worker_id,



                $validated['appointment_date'],



                $validated['appointment_time']



            )) {



                return $this->workflowError($request, [



                    'appointment_time' => 'Scheduling conflict detected. This inspector already has an appointment at that exact date and time.'



                ], 'Scheduling conflict detected for this date and time.');
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







        $message = $quotation->service_flow === 'direct_service'



            ? 'Service schedule confirmed successfully.'



            : 'Inspection schedule confirmed successfully.';







        return $this->workflowSuccess($request, $message, [



            'request_id' => $quotation->id,



            'next_step' => 4,



            'status' => $quotation->status,



            'appointment_status' => $quotation->appointment_status,



            'appointment_date' => optional($quotation->appointment_date)->format('Y-m-d'),



            'appointment_time' => $quotation->appointment_time,



            'appointment_time_display' => $quotation->appointment_time



                ? \Carbon\Carbon::parse($quotation->appointment_time)->format('h:i A')



                : null,



        ]);
    }







    public function reviewClientRequest(Request $request, QuotationRequest $quotation)



    {



        $validated = $request->validate([



            'decision' => ['required', 'in:approved,declined'],



            'client_request_review_notes' => ['nullable', 'string'],



        ]);







        if ($quotation->client_action_status !== 'pending' || !$quotation->client_action_request) {



            return $this->workflowError($request, [



                'decision' => 'There is no pending client request to review.'



            ], 'There is no pending client request to review.');
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



                    return $this->workflowError($request, [



                        'decision' => 'Assign an inspector first before approving reschedule.'



                    ], 'Assign an inspector first before approving reschedule.');
                }







                if (!$quotation->client_requested_date || !$quotation->client_requested_time) {



                    return $this->workflowError($request, [



                        'decision' => 'Requested reschedule date/time is missing.'



                    ], 'Requested reschedule date/time is missing.');
                }







                $requestedDate = date('Y-m-d', strtotime($quotation->client_requested_date));







                if (!$this->inspectorIsAvailableOnDate($quotation->worker_id, $requestedDate)) {



                    return $this->workflowError($request, [



                        'decision' => 'Assigned inspector is not available on the requested date.'



                    ], 'Assigned inspector is not available on the requested date.');
                }







                if ($this->hasAppointmentConflict(



                    $quotation,



                    $quotation->worker_id,



                    $requestedDate,



                    $quotation->client_requested_time



                )) {



                    return $this->workflowError($request, [



                        'decision' => 'Scheduling conflict detected for the requested date/time.'



                    ], 'Scheduling conflict detected for the requested date/time.');
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







        return $this->workflowSuccess($request, 'Client request reviewed successfully.', [



            'request_id' => $quotation->id,



            'decision' => $validated['decision'],



            'client_action_status' => $quotation->client_action_status,



        ]);
    }











    public function archived(Request $request)



    {



        $recordTypes = $this->archiveRecordTypes();







        $allRecords = $this->collectArchivedRecords();



        $records = $this->applyArchivedRecordFilters($allRecords, $request)



            ->sortByDesc('archived_at_sort')



            ->values();







        $page = LengthAwarePaginator::resolveCurrentPage();



        $perPage = 10;







        $archives = new LengthAwarePaginator(



            $records->forPage($page, $perPage)->values(),



            $records->count(),



            $perPage,



            $page,



            [



                'path' => $request->url(),



                'query' => $request->query(),



            ]



        );







        $summary = [



            'total_records' => $allRecords->count(),



            'filtered_records' => $records->count(),



            'latest_archived' => $allRecords->max('archived_at_sort'),



            'by_type' => $allRecords->groupBy('type')->map->count()->toArray(),



        ];







        $archiveCandidates = $this->collectArchiveCandidates();







        return view('admin.quotations.archived', compact('archives', 'recordTypes', 'summary', 'archiveCandidates'));
    }







    protected function archiveRecordTypes(): array



    {



        return [



            'all' => 'All Records',



            'request' => 'Requests',



            'quotation' => 'Quotations',



            'job_order' => 'Job Orders',



            'warranty_claim' => 'Warranty Claims',



            'back_job' => 'Back Jobs',



        ];
    }







    protected function archiveTypeTables(): array



    {



        return [



            'request' => 'quotation_requests',



            'quotation' => 'quotations',



            'job_order' => 'job_orders',



            'warranty_claim' => 'warranty_claims',



            'back_job' => 'back_jobs',



        ];
    }







    protected function archiveTypeLabels(): array



    {



        return [



            'request' => 'Request',



            'quotation' => 'Quotation',



            'job_order' => 'Job Order',



            'warranty_claim' => 'Warranty Claim',



            'back_job' => 'Back Job',



        ];
    }







    protected function collectArchivedRecords(): \Illuminate\Support\Collection



    {



        return collect($this->archiveTypeTables())



            ->flatMap(function (string $table, string $type) {



                if (!Schema::hasTable($table) || !Schema::hasColumn($table, 'archived_at')) {



                    return collect();
                }







                return DB::table($table)



                    ->whereNotNull('archived_at')



                    ->get()



                    ->map(fn($row) => $this->makeArchivedRecord($type, $table, $row));
            })



            ->values();
    }







    protected function collectArchiveCandidates(): array



    {



        return collect($this->archiveTypeTables())



            ->mapWithKeys(function (string $table, string $type) {



                if (!Schema::hasTable($table) || !Schema::hasColumn($table, 'archived_at')) {



                    return [$type => []];
                }







                $query = DB::table($table)



                    ->whereNull('archived_at');







                if (Schema::hasColumn($table, 'id')) {



                    $query->orderByDesc('id');
                }







                $candidates = $query



                    ->limit(100)



                    ->get()



                    ->map(fn($row) => $this->makeArchiveCandidate($type, $row))



                    ->values()



                    ->toArray();







                return [$type => $candidates];
            })



            ->toArray();
    }







    protected function makeArchiveCandidate(string $type, object $row): array



    {



        $labels = $this->archiveTypeLabels();



        $relatedRequest = $this->findRelatedRequest($row);



        $reference = $this->archiveReference($type, $row);



        $client = $this->archiveClientName($row, $relatedRequest);



        $service = $this->archiveService($type, $row, $relatedRequest);



        $status = $this->archiveStatus($row);



        $category = $this->archiveCategory($row, $relatedRequest, $labels[$type] ?? 'Record');







        return [



            'type' => $type,



            'id' => (int) $this->archiveValue($row, ['id'], 0),



            'reference' => $reference,



            'client' => $client,



            'service' => $service,



            'status' => $status,



            'category' => $category,



            'meta' => ($labels[$type] ?? 'Record') . ' • ' . $status,



            'label' => $reference . ' — ' . $client . ' — ' . $service,



        ];
    }







    protected function applyArchivedRecordFilters(\Illuminate\Support\Collection $records, Request $request): \Illuminate\Support\Collection



    {



        $recordType = $request->input('record_type', 'all');



        $status = $request->input('status');



        $source = $request->input('archive_source');



        $search = trim((string) $request->input('search', ''));



        $from = $request->input('archived_from');



        $to = $request->input('archived_to');







        return $records->filter(function (array $record) use ($recordType, $status, $source, $search, $from, $to) {



            if ($recordType !== 'all' && $record['type'] !== $recordType) {



                return false;
            }







            if ($status && $record['status_key'] !== $this->normalizeArchiveKey($status)) {



                return false;
            }







            if ($source && $record['source_key'] !== $this->normalizeArchiveKey($source)) {



                return false;
            }







            if ($from && $record['archived_at_sort'] < Carbon::parse($from)->startOfDay()->timestamp) {



                return false;
            }







            if ($to && $record['archived_at_sort'] > Carbon::parse($to)->endOfDay()->timestamp) {



                return false;
            }







            if ($search !== '') {



                $haystack = strtolower(implode(' ', [



                    $record['type_label'],



                    $record['reference'],



                    $record['client'],



                    $record['contact'],



                    $record['service'],



                    $record['category'],



                    $record['status'],



                    $record['reason'],



                    $record['source'],



                ]));







                if (!str_contains($haystack, strtolower($search))) {



                    return false;
                }
            }







            return true;
        })->values();
    }







    protected function makeArchivedRecord(string $type, string $table, object $row): array



    {



        $labels = $this->archiveTypeLabels();



        $relatedRequest = $this->findRelatedRequest($row);



        $reference = $this->archiveReference($type, $row);



        $client = $this->archiveClientName($row, $relatedRequest);



        $contact = $this->archiveContact($row, $relatedRequest);



        $service = $this->archiveService($type, $row, $relatedRequest);



        $category = $this->archiveCategory($row, $relatedRequest, $labels[$type] ?? 'Record');



        $status = $this->archiveStatus($row);



        $archivedAt = $this->archiveValue($row, ['archived_at'], null);



        $reason = $this->archiveValue($row, ['archive_reason', 'closed_reason', 'remarks'], '—');



        $source = $this->archiveSource($row, $reason);



        $archivedAtCarbon = $archivedAt ? Carbon::parse($archivedAt) : null;







        return [



            'type' => $type,



            'type_label' => $labels[$type] ?? ucfirst(str_replace('_', ' ', $type)),



            'id' => (int) $this->archiveValue($row, ['id'], 0),



            'reference' => $reference,



            'client' => $client,



            'contact' => $contact,



            'service' => $service,



            'category' => $category,



            'status' => $status,



            'status_key' => $this->normalizeArchiveKey($status),



            'archived_at' => $archivedAtCarbon,



            'archived_at_display' => $archivedAtCarbon ? $archivedAtCarbon->format('M d, Y h:i A') : '—',



            'archived_at_sort' => $archivedAtCarbon ? $archivedAtCarbon->timestamp : 0,



            'reason' => $reason ?: '—',



            'source' => $source,



            'source_key' => $this->normalizeArchiveKey($source),



            'restore_url' => route('admin.archives.restore', ['type' => $type, 'id' => (int) $this->archiveValue($row, ['id'], 0)]),



            'view_url' => $this->archiveViewUrl($type, $row),



        ];
    }







    protected function archiveValue(object $row, array $candidates, mixed $default = null): mixed



    {



        foreach ($candidates as $candidate) {



            if (property_exists($row, $candidate) && $row->{$candidate} !== null && $row->{$candidate} !== '') {



                return $row->{$candidate};
            }
        }







        return $default;
    }







    protected function archiveReference(string $type, object $row): string



    {



        $id = (int) $this->archiveValue($row, ['id'], 0);







        return match ($type) {



            'request' => $this->archiveValue($row, ['request_no', 'quotation_request_no', 'reference_no'], 'REQ-' . str_pad((string) $id, 4, '0', STR_PAD_LEFT)),



            'quotation' => $this->archiveValue($row, ['quotation_no', 'reference_no'], 'QT-' . str_pad((string) $id, 4, '0', STR_PAD_LEFT)),



            'job_order' => $this->archiveValue($row, ['job_order_no', 'job_no', 'reference_no'], 'JO-' . str_pad((string) $id, 4, '0', STR_PAD_LEFT)),



            'warranty_claim' => $this->archiveValue($row, ['warranty_claim_no', 'claim_no', 'reference_no'], 'WC-' . str_pad((string) $id, 4, '0', STR_PAD_LEFT)),



            'back_job' => $this->archiveValue($row, ['back_job_no', 'backjob_no', 'reference_no'], 'BJ-' . str_pad((string) $id, 4, '0', STR_PAD_LEFT)),



            default => 'REC-' . str_pad((string) $id, 4, '0', STR_PAD_LEFT),
        };
    }







    protected function archiveClientName(object $row, ?QuotationRequest $request): string



    {



        if ($request) {



            return $request->full_name ?: trim(($request->first_name ?? '') . ' ' . ($request->last_name ?? '')) ?: 'Unnamed Client';
        }







        $name = $this->archiveValue($row, ['full_name', 'client_name', 'customer_name', 'name'], null);







        if ($name) {



            return $name;
        }







        $first = $this->archiveValue($row, ['first_name'], '');



        $last = $this->archiveValue($row, ['last_name'], '');







        return trim("{$first} {$last}") ?: 'Unnamed Client';
    }







    protected function archiveContact(object $row, ?QuotationRequest $request): string



    {



        $email = $request?->email ?: $this->archiveValue($row, ['email', 'client_email'], '');



        $phone = $request?->phone ?: $this->archiveValue($row, ['phone', 'client_phone', 'contact_no'], '');







        return trim($email . ($email && $phone ? ' • ' : '') . $phone) ?: '—';
    }







    protected function archiveService(string $type, object $row, ?QuotationRequest $request): string



    {



        if ($request && in_array($type, ['quotation', 'job_order', 'warranty_claim', 'back_job'], true)) {



            return $request->service_type ?: '—';
        }







        return $this->archiveValue($row, [



            'service_type',



            'subject',



            'title',



            'issue_type',



            'claim_type',



            'backjob_type',



            'description',



        ], '—');
    }







    protected function archiveCategory(object $row, ?QuotationRequest $request, string $fallback): string



    {



        return $request?->service_category



            ?: $this->archiveValue($row, ['service_category', 'category', 'type'], $fallback);
    }







    protected function archiveStatus(object $row): string



    {



        return ucfirst(str_replace('_', ' ', (string) $this->archiveValue($row, ['status', 'state'], 'Archived')));
    }







    protected function archiveSource(object $row, ?string $reason): string



    {



        $source = $this->archiveValue($row, ['archive_source', 'closed_source'], null);







        if ($source) {



            return ucfirst(str_replace('_', ' ', $source));
        }







        $reason = strtolower((string) $reason);







        if (str_contains($reason, 'system') || str_contains($reason, 'older than') || str_contains($reason, 'automatic')) {



            return 'System';
        }







        return 'Manual';
    }







    protected function archiveViewUrl(string $type, object $row): ?string



    {



        $id = (int) $this->archiveValue($row, ['id'], 0);







        return match ($type) {



            'job_order' => RouteFacade::has('admin.job-orders.show') ? route('admin.job-orders.show', $id) : null,



            'warranty_claim' => RouteFacade::has('admin.warranty-claims.show') ? route('admin.warranty-claims.show', $id) : null,



            'back_job' => RouteFacade::has('admin.backjobs.show') ? route('admin.backjobs.show', $id) : null,



            default => null,
        };
    }







    protected function findRelatedRequest(object $row): ?QuotationRequest



    {



        $requestId = $this->archiveValue($row, ['quotation_request_id', 'request_id'], null);







        if ($requestId) {



            return QuotationRequest::find($requestId);
        }







        $quotationId = $this->archiveValue($row, ['quotation_id'], null);







        if ($quotationId && Schema::hasTable('quotations')) {



            $quotation = DB::table('quotations')->where('id', $quotationId)->first();



            $requestId = $quotation ? $this->archiveValue($quotation, ['quotation_request_id', 'request_id'], null) : null;







            if ($requestId) {



                return QuotationRequest::find($requestId);
            }
        }







        $jobOrderId = $this->archiveValue($row, ['job_order_id'], null);







        if ($jobOrderId && Schema::hasTable('job_orders')) {



            $jobOrder = DB::table('job_orders')->where('id', $jobOrderId)->first();







            if ($jobOrder) {



                return $this->findRelatedRequest($jobOrder);
            }
        }







        $warrantyClaimId = $this->archiveValue($row, ['warranty_claim_id'], null);







        if ($warrantyClaimId && Schema::hasTable('warranty_claims')) {



            $warrantyClaim = DB::table('warranty_claims')->where('id', $warrantyClaimId)->first();







            if ($warrantyClaim) {



                return $this->findRelatedRequest($warrantyClaim);
            }
        }







        return null;
    }







    protected function normalizeArchiveKey(string $value): string



    {



        $value = strtolower(trim($value));



        $value = preg_replace('/[^a-z0-9]+/', '_', $value);







        return trim($value ?? '', '_');
    }











    protected function archiveLifecycleColumns(string $type): array



    {



        return match ($type) {



            'request' => ['completed_at', 'cancelled_at'],



            'quotation' => ['accepted_at', 'declined_at', 'cancelled_at', 'expired_at'],



            'job_order' => ['completed_at', 'cancelled_at'],



            'warranty_claim' => ['resolved_at', 'closed_at', 'denied_at', 'cancelled_at'],



            'back_job' => ['resolved_at', 'completed_at', 'closed_at', 'cancelled_at'],



            default => ['completed_at', 'cancelled_at', 'resolved_at', 'closed_at'],
        };
    }







    protected function archiveFallbackAgeColumns(string $type): array



    {



        return match ($type) {



            'quotation' => ['sent_at', 'updated_at', 'created_at'],



            default => ['updated_at', 'created_at'],
        };
    }







    protected function archiveEligibleStatuses(string $type): array



    {



        return match ($type) {



            'request' => ['completed', 'cancelled'],



            'quotation' => ['accepted', 'rejected', 'declined', 'expired', 'cancelled', 'converted'],



            'job_order' => ['completed', 'cancelled'],



            'warranty_claim' => ['resolved', 'closed', 'denied', 'rejected', 'cancelled'],



            'back_job' => ['resolved', 'completed', 'closed', 'cancelled'],



            default => ['completed', 'cancelled', 'resolved', 'closed'],
        };
    }







    protected function archiveExistingColumns(string $table, array $columns): array



    {



        return array_values(array_filter($columns, fn($column) => Schema::hasColumn($table, $column)));
    }







    protected function archiveEligibilityDateColumns(string $type, string $table): array



    {



        $lifecycle = $this->archiveExistingColumns($table, $this->archiveLifecycleColumns($type));







        if (!empty($lifecycle)) {



            return $lifecycle;
        }







        return $this->archiveExistingColumns($table, $this->archiveFallbackAgeColumns($type));
    }







    protected function archiveEligibilityDateExpression(array $columns): string



    {



        $quoted = collect($columns)



            ->map(fn($column) => '\\`' . str_replace('\\`', '\\`\\`', $column) . '\\`')



            ->implode(', ');







        return count($columns) > 1 ? "COALESCE({$quoted})" : $quoted;
    }







    protected function shouldApplyArchiveStatusFilter(string $type, string $table, array $dateColumns): bool



    {



        if (!Schema::hasColumn($table, 'status')) {



            return false;
        }







        // Requests, job orders, warranty claims, and back jobs with lifecycle dates are already safe.



        // Quotations still need status filtering because sent/updated dates may exist while a quotation remains active.



        if ($type !== 'quotation' && !empty($this->archiveExistingColumns($table, $this->archiveLifecycleColumns($type)))) {



            return false;
        }







        return !empty($this->archiveEligibleStatuses($type));
    }







    protected function eligibleArchiveRecordsForType(string $type, int $ageDays, int $limit = 200): \Illuminate\Support\Collection



    {



        $tables = $this->archiveTypeTables();







        if (!isset($tables[$type])) {



            return collect();
        }







        $table = $tables[$type];







        if (!Schema::hasTable($table) || !Schema::hasColumn($table, 'archived_at')) {



            return collect();
        }







        $dateColumns = $this->archiveEligibilityDateColumns($type, $table);







        if (empty($dateColumns)) {



            return collect();
        }







        $dateExpression = $this->archiveEligibilityDateExpression($dateColumns);



        $cutoff = now()->subDays($ageDays)->toDateString();







        $query = DB::table($table)



            ->select('*')



            ->selectRaw("{$dateExpression} as archive_basis_date")



            ->whereNull('archived_at')



            ->whereRaw("{$dateExpression} IS NOT NULL")



            ->whereRaw("DATE({$dateExpression}) <= ?", [$cutoff]);







        if ($this->shouldApplyArchiveStatusFilter($type, $table, $dateColumns)) {



            $query->whereIn('status', $this->archiveEligibleStatuses($type));
        }







        return $query



            ->orderByRaw("{$dateExpression} asc")



            ->limit($limit)



            ->get()



            ->map(fn($row) => $this->makeEligibleArchiveRecord($type, $row, $ageDays))



            ->values();
    }







    protected function makeEligibleArchiveRecord(string $type, object $row, int $ageDays): array



    {



        $candidate = $this->makeArchiveCandidate($type, $row);



        $basisDate = $this->archiveValue($row, ['archive_basis_date', 'completed_at', 'cancelled_at', 'resolved_at', 'closed_at', 'updated_at', 'created_at'], null);



        $basisDateCarbon = $basisDate ? Carbon::parse($basisDate) : null;



        $daysOld = $basisDateCarbon ? $basisDateCarbon->diffInDays(now()) : null;







        return array_merge($candidate, [



            'basis_date' => $basisDateCarbon?->toDateString(),



            'basis_date_display' => $basisDateCarbon ? $basisDateCarbon->format('M d, Y') : '—',



            'days_old' => $daysOld,



            'age_label' => $daysOld !== null ? $daysOld . ' day(s) old' : 'Older than ' . $ageDays . ' days',



        ]);
    }







    public function eligibleArchiveRecords(Request $request)



    {



        $validated = $request->validate([



            'record_type' => ['required', 'string'],



            'age_days' => ['nullable', 'integer', 'min:1', 'max:3650'],



        ]);







        $type = $validated['record_type'];



        $ageDays = (int) ($validated['age_days'] ?? 30);







        abort_unless(isset($this->archiveTypeTables()[$type]), 404);







        $records = $this->eligibleArchiveRecordsForType($type, $ageDays);







        return response()->json([



            'record_type' => $type,



            'age_days' => $ageDays,



            'count' => $records->count(),



            'records' => $records,



        ]);
    }







    public function bulkArchiveRecords(Request $request)



    {



        $validated = $request->validate([



            'record_type' => ['required', 'string'],



            'age_days' => ['nullable', 'integer', 'min:1', 'max:3650'],



            'record_ids' => ['required', 'array', 'min:1'],



            'record_ids.*' => ['integer'],



            'archive_reason' => ['required', 'string', 'max:1000'],



        ]);







        $tables = $this->archiveTypeTables();



        $labels = $this->archiveTypeLabels();



        $type = $validated['record_type'];



        $ageDays = (int) ($validated['age_days'] ?? 30);







        abort_unless(isset($tables[$type]), 404);







        $table = $tables[$type];







        abort_unless(Schema::hasTable($table) && Schema::hasColumn($table, 'archived_at'), 404);







        $requestedIds = collect($validated['record_ids'])



            ->map(fn($id) => (int) $id)



            ->filter()



            ->unique()



            ->values();







        $eligibleIds = $this->eligibleArchiveRecordsForType($type, $ageDays, 1000)



            ->pluck('id')



            ->map(fn($id) => (int) $id);







        $ids = $requestedIds->intersect($eligibleIds)->values();







        if ($ids->isEmpty()) {



            return redirect()



                ->route('admin.archives.index', ['record_type' => $type])



                ->with('info', 'No eligible selected records were archived. Active or ineligible records were protected.');
        }







        $payload = [



            'archived_at' => now(),



        ];







        if (Schema::hasColumn($table, 'archive_reason')) {



            $payload['archive_reason'] = $validated['archive_reason'];
        }







        if (Schema::hasColumn($table, 'archive_source')) {



            $payload['archive_source'] = 'manual';
        }







        if (Schema::hasColumn($table, 'archived_by')) {



            $payload['archived_by'] = Auth::id();
        }







        if (Schema::hasColumn($table, 'updated_at')) {



            $payload['updated_at'] = now();
        }







        $updated = DB::table($table)



            ->whereIn('id', $ids->all())



            ->whereNull('archived_at')



            ->update($payload);







        try {



            AuditLogService::log(



                'Admin Bulk Archived Records',



                $labels[$type] ?? 'Archived Records',



                null,



                ['record_ids' => $ids->all()],



                array_merge($payload, ['count' => $updated]),



                "Admin bulk archived {$updated} {$type} record(s)."



            );
        } catch (\Throwable $e) {



            // Bulk archive should not fail because generic audit logging is unavailable.



        }







        return redirect()



            ->route('admin.archives.index', ['record_type' => $type])



            ->with('success', "Archived {$updated} " . strtolower($labels[$type] ?? 'record') . " record(s) successfully.");
    }







    public function archiveRecordManually(Request $request)



    {



        $validated = $request->validate([



            'record_type' => ['required', 'string'],



            'record_id' => ['required', 'integer'],



            'archive_reason' => ['required', 'string', 'max:1000'],



        ]);







        $tables = $this->archiveTypeTables();



        $labels = $this->archiveTypeLabels();



        $type = $validated['record_type'];



        $id = (int) $validated['record_id'];







        abort_unless(isset($tables[$type]), 404);







        $table = $tables[$type];







        abort_unless(Schema::hasTable($table) && Schema::hasColumn($table, 'archived_at'), 404);







        $row = DB::table($table)->where('id', $id)->first();







        abort_unless($row, 404);







        if ($this->archiveValue($row, ['archived_at'], null)) {



            return redirect()



                ->route('admin.archives.index', ['record_type' => $type])



                ->with('info', ($labels[$type] ?? 'Record') . ' is already archived.');
        }







        $oldValues = [



            'archived_at' => null,



            'archive_reason' => $this->archiveValue($row, ['archive_reason'], null),



        ];







        $payload = [



            'archived_at' => now(),



        ];







        if (Schema::hasColumn($table, 'archive_reason')) {



            $payload['archive_reason'] = $validated['archive_reason'];
        }







        if (Schema::hasColumn($table, 'archive_source')) {



            $payload['archive_source'] = 'manual';
        }







        if (Schema::hasColumn($table, 'archived_by')) {



            $payload['archived_by'] = Auth::id();
        }







        if (Schema::hasColumn($table, 'updated_at')) {



            $payload['updated_at'] = now();
        }







        DB::table($table)->where('id', $id)->update($payload);







        try {



            $model = $type === 'request' ? QuotationRequest::find($id) : null;







            AuditLogService::log(



                'Admin Manually Archived Record',



                $labels[$type] ?? 'Archived Records',



                $model,



                $oldValues,



                $payload,



                "Admin manually archived {$type} record #{$id}."



            );
        } catch (\Throwable $e) {



            // Manual archive must not fail just because generic audit logging is unavailable.



        }







        return redirect()



            ->route('admin.archives.index', ['record_type' => $type])



            ->with('success', ($labels[$type] ?? 'Record') . ' added to archived records successfully.');
    }







    public function archive(Request $request, QuotationRequest $quotation)



    {



        $oldValues = $quotation->only(['archived_at', 'archive_reason']);







        $quotation->forceFill([



            'archived_at' => now(),



            'archive_reason' => $request->archive_reason ?: 'Manually archived by admin',



        ])->save();







        AuditLogService::log(



            'Admin Archived Request',



            'Quotation Requests',



            $quotation,



            $oldValues,



            $quotation->only(['archived_at', 'archive_reason']),



            "Admin archived quotation request #{$quotation->id}."



        );







        return redirect()



            ->route('admin.quotations.index')



            ->with('success', 'Request archived successfully.');
    }







    public function restore(QuotationRequest $quotation)



    {



        $oldValues = $quotation->only(['archived_at', 'archive_reason']);







        $quotation->forceFill([



            'archived_at' => null,



            'archive_reason' => null,



        ])->save();







        AuditLogService::log(



            'Admin Restored Request',



            'Quotation Requests',



            $quotation,



            $oldValues,



            $quotation->only(['archived_at', 'archive_reason']),



            "Admin restored quotation request #{$quotation->id}."



        );







        return redirect()



            ->route('admin.archives.index', ['record_type' => 'request'])



            ->with('success', 'Request restored successfully.');
    }







    public function restoreArchivedRecord(Request $request, string $type, int $id)



    {



        $tables = $this->archiveTypeTables();



        $labels = $this->archiveTypeLabels();







        abort_unless(isset($tables[$type]), 404);







        $table = $tables[$type];







        abort_unless(Schema::hasTable($table) && Schema::hasColumn($table, 'archived_at'), 404);







        $row = DB::table($table)->where('id', $id)->first();







        abort_unless($row, 404);







        $oldValues = [



            'archived_at' => $this->archiveValue($row, ['archived_at'], null),



            'archive_reason' => $this->archiveValue($row, ['archive_reason'], null),



        ];







        $payload = [



            'archived_at' => null,



        ];







        foreach (['archive_reason', 'archive_source', 'archived_by'] as $column) {



            if (Schema::hasColumn($table, $column)) {



                $payload[$column] = null;
            }
        }







        if (Schema::hasColumn($table, 'updated_at')) {



            $payload['updated_at'] = now();
        }







        DB::table($table)->where('id', $id)->update($payload);







        try {



            $model = $type === 'request' ? QuotationRequest::find($id) : null;







            AuditLogService::log(



                'Admin Restored Archived Record',



                $labels[$type] ?? 'Archived Records',



                $model,



                $oldValues,



                $payload,



                "Admin restored {$type} archived record #{$id}."



            );
        } catch (\Throwable $e) {



            // Restore must not fail just because audit logging is unavailable for a generic archived record.



        }







        return redirect()



            ->route('admin.archives.index', ['record_type' => $type])



            ->with('success', ($labels[$type] ?? 'Record') . ' restored successfully.');
    }







    public function sendToHr(Request $request, QuotationRequest $quotation)

    {

        $validated = $request->validate([

            'quotation_handoff_notes' => ['nullable', 'string', 'max:1000'],

        ]);



        $quotation->load([

            'worker',

            'inspectionJobOrder',

            'inspectionReport',

            'quotation',

        ]);



        if ($quotation->quotation) {

            return $this->workflowError(

                $request,

                ['quotation' => 'A quotation already exists for this request.'],

                'A quotation has already been created for this request.'

            );
        }



        if ($quotation->ready_for_quotation_at) {

            return $this->workflowError(

                $request,

                ['quotation' => 'This request has already been sent to HR.'],

                'This request has already been sent to HR.'

            );
        }



        if (!$quotation->worker_id) {

            return $this->workflowError(

                $request,

                ['worker_id' => 'Assign an inspector or personnel first.'],

                'Assign an inspector or personnel before sending this request to HR.'

            );
        }



        if (

            !$quotation->appointment_date ||

            !$quotation->appointment_time ||

            !in_array($quotation->appointment_status, ['approved', 'rescheduled'], true)

        ) {

            return $this->workflowError(

                $request,

                ['appointment' => 'A confirmed schedule is required.'],

                'Confirm the inspection or service schedule first.'

            );
        }



        if ($quotation->service_flow === 'inspection_required') {

            if (!$quotation->inspectionJobOrder) {

                return $this->workflowError(

                    $request,

                    ['job_order' => 'An inspection job order is required.'],

                    'Create the inspection job order first.'

                );
            }



            if ($quotation->inspectionJobOrder->status !== 'completed') {

                return $this->workflowError(

                    $request,

                    ['job_order' => 'The assigned inspector must complete the inspection first.'],

                    'Wait for the inspector to complete the inspection.'

                );
            }



            if (!$quotation->inspectionReport || $quotation->inspectionReport->status !== 'submitted') {

                return $this->workflowError(

                    $request,

                    ['inspection_report' => 'The final inspection report must be submitted first.'],

                    'Wait for the inspector to submit the final inspection report.'

                );
            }
        }



        $oldValues = $quotation->only([

            'status',

            'ready_for_quotation_at',

            'forwarded_to_hr_by',

            'quotation_handoff_notes',

        ]);



        $quotation->update([

            'status' => 'ready_for_quotation',

            'ready_for_quotation_at' => now(),

            'forwarded_to_hr_by' => Auth::id(),

            'quotation_handoff_notes' => $validated['quotation_handoff_notes'] ?? null,

        ]);



        $clientName = $quotation->full_name ?: 'Client';



        AlertService::sendToRole(

            'hr',

            'New quotation request ready',

            "{$clientName}'s {$quotation->service_type} request is ready for quotation preparation.",

            route('hr.quotations.create', $quotation),

            'success'

        );



        AuditLogService::log(

            'Admin Sent Request to HR',

            'Quotation Requests',

            $quotation,

            $oldValues,

            [

                'status' => $quotation->status,

                'ready_for_quotation_at' => optional($quotation->ready_for_quotation_at)->toDateTimeString(),

                'forwarded_to_hr_by' => $quotation->forwarded_to_hr_by,

                'quotation_handoff_notes' => $quotation->quotation_handoff_notes,

            ],

            "Admin sent quotation request #{$quotation->id} to HR for quotation preparation."

        );



        return $this->workflowSuccess(

            $request,

            'Request successfully sent to HR for quotation preparation.',

            [

                'request_id' => $quotation->id,

                'status' => $quotation->status,

                'ready_for_quotation_at' => optional($quotation->ready_for_quotation_at)->format('M d, Y h:i A'),

                'sent_to_hr' => true,

            ]

        );
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







        return $this->workflowSuccess($request, 'System decision updated successfully.', [



            'request_id' => $quotation->id,



            'service_flow' => $quotation->service_flow,



            'visit_purpose' => $quotation->visit_purpose,



        ]);
    }







    private function workflowSuccess(Request $request, string $message, array $data = [])



    {



        if ($request->expectsJson()) {



            return response()->json(array_merge([



                'message' => $message,



            ], $data));
        }







        return back()->with('success', $message);
    }







    private function workflowError(Request $request, array $errors, string $message, int $status = 422)



    {



        if ($request->expectsJson()) {



            return response()->json([



                'message' => $message,



                'errors' => $errors,



            ], $status);
        }







        return back()->withErrors($errors)->withInput();
    }
}
