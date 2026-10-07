<?php



namespace App\Http\Controllers\Inspector;



use App\Http\Controllers\Controller;

use App\Models\InspectionReport;

use App\Models\InspectionStatusLog;

use App\Models\QuotationRequest;

use App\Models\User;

use App\Services\AlertService;

use Illuminate\Http\Request;

use App\Models\InspectionPhoto;

use App\Models\InspectionChecklistItem;

use App\Models\InspectionMaterialItem;

use Illuminate\Support\Facades\Storage;

use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\DB;

use Illuminate\Support\Str;



class QuotationController extends Controller

{

    public function index()

    {

        $quotations = QuotationRequest::query()

            ->where('worker_id', Auth::id())

            ->with(['inspectionReport', 'inspectionJobOrder'])

            ->latest()

            ->paginate(10);



        return view('inspector.quotations.index', compact('quotations'));

    }



public function show(QuotationRequest $quotation)

{

    abort_if($quotation->worker_id !== Auth::id(), 403);



    $quotation->load([

        'inspectionReport.photos.uploadedBy',

        'inspectionReport.checklistItems.completedBy',

        'inspectionReport.materialItems',

        'inspectionStatusLogs.updatedBy',
        'inspectionJobOrder',
        'serviceJobOrder',

    ]);



    if ($quotation->inspectionReport) {

        $this->initializeChecklist(

            $quotation->inspectionReport

        );



        $quotation->inspectionReport->load(

            'checklistItems.completedBy'

        );

    }



    return view('inspector.quotations.show', compact('quotation'));

}



private function initializeChecklist(

    InspectionReport $inspectionReport

): void {

    $items = [

        'site_inspected' => 'Site inspected',

        'leak_test_completed' => 'Leak test completed',

        'fixtures_tested' => 'Fixtures tested',

        'work_area_cleaned' => 'Work area cleaned',

        'client_informed' => 'Client informed',

        'photos_uploaded' => 'Required photos uploaded',

        'report_completed' => 'Inspection report completed',

    ];



    foreach ($items as $key => $label) {

        InspectionChecklistItem::firstOrCreate(

            [

                'inspection_report_id' => $inspectionReport->id,

                'item_key' => $key,

            ],

            [

                'label' => $label,

                'is_required' => true,

                'is_completed' => false,

            ]

        );

    }

}



public function updateChecklist(

    Request $request,

    QuotationRequest $quotation

) {

    abort_if($quotation->worker_id !== Auth::id(), 403);



    $inspectionReport = $quotation->inspectionReport;



    abort_if(!$inspectionReport, 422, 'Create the inspection report first.');



    abort_if(

        $inspectionReport->status === 'submitted',

        422,

        'Submitted reports can no longer be modified.'

    );



    $validated = $request->validate([

        'items' => ['nullable', 'array'],

        'items.*' => ['nullable', 'integer'],

    ]);



    $selectedIds = collect($validated['items'] ?? [])

        ->map(fn ($id) => (int) $id);



    DB::transaction(function () use (

        $inspectionReport,

        $selectedIds

    ) {

        foreach ($inspectionReport->checklistItems as $item) {

            $completed = $selectedIds->contains($item->id);



            $item->update([

                'is_completed' => $completed,

                'completed_by' => $completed

                    ? Auth::id()

                    : null,

                'completed_at' => $completed

                    ? now()

                    : null,

            ]);

        }

    });



    return back()->with(

        'success',

        'Completion checklist updated successfully.'

    );

}



    public function update(Request $request, QuotationRequest $quotation)
    {
        abort_if($quotation->worker_id !== Auth::id(), 403);

        $validated = $request->validate([
            'status' => ['required', 'in:assigned,in_progress,completed'],
            'inspector_notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $quotation->loadMissing([
            'inspectionReport',
            'inspectionJobOrder',
            'serviceJobOrder',
        ]);

        DB::transaction(function () use ($quotation, $validated) {
            $oldStatus = $quotation->status;
            $requestedStatus = $validated['status'];
            $effectiveStatus = $requestedStatus;

            if (
                $quotation->service_flow === 'inspection_required' &&
                $requestedStatus === 'completed'
            ) {
                $effectiveStatus = 'in_progress';

                if ($quotation->inspectionJobOrder) {
                    $quotation->inspectionJobOrder->update([
                        'status' => 'completed',
                        'completed_at' => $quotation->inspectionJobOrder->completed_at ?? now(),
                        'completion_notes' => $validated['inspector_notes'] ?? $quotation->inspectionJobOrder->completion_notes,
                    ]);
                }
            }

            $data = [
                'status' => $effectiveStatus,
                'inspector_notes' => $validated['inspector_notes'] ?? null,
            ];

            if ($effectiveStatus === 'in_progress' && !$quotation->inspected_at) {
                $data['inspected_at'] = now();
            }

            if ($quotation->service_flow === 'inspection_required') {
                $data['completed_at'] = null;
            } elseif ($requestedStatus === 'completed' && !$quotation->completed_at) {
                $data['completed_at'] = now();
            }

            if ($quotation->service_flow === 'direct_service' && $quotation->serviceJobOrder) {
                if ($requestedStatus === 'in_progress') {
                    $quotation->serviceJobOrder->update([
                        'status' => 'in_progress',
                        'started_at' => $quotation->serviceJobOrder->started_at ?? now(),
                    ]);
                }

                if ($requestedStatus === 'completed') {
                    $quotation->serviceJobOrder->update([
                        'status' => 'completed',
                        'completed_at' => $quotation->serviceJobOrder->completed_at ?? now(),
                        'completion_notes' => $validated['inspector_notes'] ?? $quotation->serviceJobOrder->completion_notes,
                    ]);
                }
            }

            $quotation->update($data);

            if ($oldStatus !== $effectiveStatus || $requestedStatus === 'completed') {
                InspectionStatusLog::create([
                    'quotation_request_id' => $quotation->id,
                    'inspection_report_id' => $quotation->inspectionReport?->id,
                    'updated_by' => Auth::id(),
                    'status' => $quotation->service_flow === 'inspection_required' && $requestedStatus === 'completed'
                        ? 'inspection_completed'
                        : $effectiveStatus,
                    'notes' => $validated['inspector_notes'] ?? null,
                    'recorded_at' => now(),
                ]);
            }

            if (!($quotation->service_flow === 'inspection_required' && $requestedStatus === 'completed')) {
                $this->sendStatusAlerts($quotation, $requestedStatus);
            }
        });

        $message = $quotation->service_flow === 'inspection_required' && $validated['status'] === 'completed'
            ? 'Inspection completed. Submit the final inspection report for admin handoff to HR.'
            : 'Request updated successfully.';

        return back()->with('success', $message);
    }

    public function uploadInspectionPhotos(

    Request $request,

    QuotationRequest $quotation

) {

    abort_if($quotation->worker_id !== Auth::id(), 403);



    $validated = $request->validate([

        'category' => [

            'required',

            'in:before,during,after',

        ],

        'photos' => [

            'required',

            'array',

            'min:1',

            'max:8',

        ],

        'photos.*' => [

            'required',

            'image',

            'mimes:jpg,jpeg,png,webp',

            'max:5120',

        ],

        'caption' => [

            'nullable',

            'string',

            'max:500',

        ],

    ]);



    $inspectionReport = InspectionReport::firstOrCreate(

        [

            'quotation_request_id' => $quotation->id,

            'inspector_id' => Auth::id(),

        ],

        [

            'report_no' => 'IR-TMP-' . Str::uuid(),

            'findings' => '',

            'status' => 'draft',

        ]

    );



    if (str_starts_with($inspectionReport->report_no, 'IR-TMP-')) {

        $inspectionReport->update([

            'report_no' => 'IR-' .

                now()->format('Ymd') .

                '-' .

                str_pad(

                    $inspectionReport->id,

                    6,

                    '0',

                    STR_PAD_LEFT

                ),

        ]);

    }



    foreach ($request->file('photos') as $photo) {

        $path = $photo->store(

            'inspection-photos/' . $inspectionReport->id,

            'public'

        );



        InspectionPhoto::create([

            'inspection_report_id' => $inspectionReport->id,

            'uploaded_by' => Auth::id(),

            'category' => $validated['category'],

            'file_path' => $path,

            'original_name' => $photo->getClientOriginalName(),

            'mime_type' => $photo->getMimeType(),

            'file_size' => $photo->getSize(),

            'caption' => $validated['caption'] ?? null,

        ]);

    }



    return back()->with(

        'success',

        'Inspection photos uploaded successfully.'

    );

}



public function deleteInspectionPhoto(

    QuotationRequest $quotation,

    InspectionPhoto $inspectionPhoto

) {

    abort_if($quotation->worker_id !== Auth::id(), 403);



    abort_if(

        $inspectionPhoto->inspectionReport

            ->quotation_request_id !== $quotation->id,

        403

    );



    abort_if(

        $inspectionPhoto->inspectionReport->status === 'submitted',

        422,

        'Submitted inspection photos cannot be deleted.'

    );



    Storage::disk('public')->delete(

        $inspectionPhoto->file_path

    );



    $inspectionPhoto->delete();



    return back()->with(

        'success',

        'Inspection photo deleted successfully.'

    );

}



    public function updateInspectionStatus(
        Request $request,
        QuotationRequest $quotation
    ) {
        abort_if($quotation->worker_id !== Auth::id(), 403);

        $validated = $request->validate([
            'inspection_status' => [
                'required',
                'in:on_the_way,arrived_on_site,inspection_started,inspection_completed',
            ],
            'status_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $quotation->loadMissing('inspectionJobOrder');

        if (!$quotation->inspectionJobOrder) {
            return back()->withErrors([
                'inspection' => 'The inspection job order has not been created yet.',
            ]);
        }

        DB::transaction(function () use ($quotation, $validated) {
            $inspectionReport = InspectionReport::firstOrCreate(
                [
                    'quotation_request_id' => $quotation->id,
                    'inspector_id' => Auth::id(),
                ],
                [
                    'report_no' => 'IR-TMP-' . Str::uuid(),
                    'findings' => '',
                    'status' => 'draft',
                ]
            );

            if (str_starts_with($inspectionReport->report_no, 'IR-TMP-')) {
                $inspectionReport->update([
                    'report_no' => 'IR-' .
                        now()->format('Ymd') .
                        '-' .
                        str_pad($inspectionReport->id, 6, '0', STR_PAD_LEFT),
                ]);
            }

            $quotationData = [];
            $inspectionJobOrder = $quotation->inspectionJobOrder;

            if ($validated['inspection_status'] === 'inspection_started') {
                $quotationData['status'] = 'in_progress';

                if (!$quotation->inspected_at) {
                    $quotationData['inspected_at'] = now();
                }

                if (!$inspectionReport->inspection_started_at) {
                    $inspectionReport->update([
                        'inspection_started_at' => now(),
                    ]);
                }

                if ($inspectionJobOrder->status !== 'completed') {
                    $inspectionJobOrder->update([
                        'status' => 'in_progress',
                        'started_at' => $inspectionJobOrder->started_at ?? now(),
                    ]);
                }
            }

            if ($validated['inspection_status'] === 'inspection_completed') {
                $quotationData['status'] = 'in_progress';
                $quotationData['completed_at'] = null;

                $inspectionJobOrder->update([
                    'status' => 'completed',
                    'completed_at' => $inspectionJobOrder->completed_at ?? now(),
                    'completion_notes' => $validated['status_notes'] ?? $inspectionJobOrder->completion_notes,
                ]);
            }

            if (!empty($quotationData)) {
                $quotation->update($quotationData);
            }

            InspectionStatusLog::create([
                'quotation_request_id' => $quotation->id,
                'inspection_report_id' => $inspectionReport->id,
                'updated_by' => Auth::id(),
                'status' => $validated['inspection_status'],
                'notes' => $validated['status_notes'] ?? null,
                'recorded_at' => now(),
            ]);
        });

        $message = $validated['inspection_status'] === 'inspection_completed'
            ? 'Inspection completed. The inspection job order was completed automatically. Submit the final report next.'
            : 'Inspection status updated successfully.';

        return back()->with('success', $message);
    }

    public function saveInspectionReport(

        Request $request,

        QuotationRequest $quotation

    ) {

        abort_if($quotation->worker_id !== Auth::id(), 403);



        $validated = $request->validate([

            'findings' => ['required', 'string', 'max:10000'],

            'recommendations' => ['nullable', 'string', 'max:10000'],

            'client_visible_notes' => ['nullable', 'string', 'max:5000'],

            'internal_notes' => ['nullable', 'string', 'max:5000'],

            'materials' => ['nullable', 'array'],

            'materials.*.item_name' => ['required_with:materials', 'string', 'max:255'],

            'materials.*.quantity' => ['required_with:materials', 'numeric', 'min:0.01'],

            'materials.*.unit' => ['required_with:materials', 'string', 'max:50'],

            'materials.*.unit_cost' => ['required_with:materials', 'numeric', 'min:0'],

            'estimated_labor_cost' => ['nullable', 'numeric', 'min:0'],

            'estimated_miscellaneous_cost' => ['nullable', 'numeric', 'min:0'],

            'action' => ['required', 'in:draft,submit'],

        ]);



        if ($validated['action'] === 'submit') {

    $inspectionReport = InspectionReport::query()

        ->where('quotation_request_id', $quotation->id)

        ->where('inspector_id', Auth::id())

        ->first();



    if (!$inspectionReport) {

        return back()

            ->withInput()

            ->withErrors([

                'checklist' => 'Save the inspection report as draft first.',

            ]);

    }



    $this->initializeChecklist($inspectionReport);



    $hasIncompleteRequiredItems = $inspectionReport

        ->checklistItems()

        ->where('is_required', true)

        ->where('is_completed', false)

        ->exists();



    if ($hasIncompleteRequiredItems) {

        return back()

            ->withInput()

            ->withErrors([

                'checklist' => 'Complete all required checklist items before submitting the report.',

            ]);

    }

}



        DB::transaction(function () use ($quotation, $validated) {

            $materialItems = collect($validated['materials'] ?? []);



            $materialCost = $materialItems->sum(function ($item) {

                return round(

                    (float) $item['quantity'] * (float) $item['unit_cost'],

                    2

                );

            });

            $laborCost = (float) ($validated['estimated_labor_cost'] ?? 0);

            $miscellaneousCost = (float) ($validated['estimated_miscellaneous_cost'] ?? 0);



            $inspectionReport = InspectionReport::firstOrNew([

                'quotation_request_id' => $quotation->id,

                'inspector_id' => Auth::id(),

            ]);



            if (!$inspectionReport->exists) {

                $inspectionReport->report_no = 'IR-TMP-' . Str::uuid();

            }



            $inspectionReport->fill([

                'findings' => $validated['findings'],

                'recommendations' => $validated['recommendations'] ?? null,

                'client_visible_notes' => $validated['client_visible_notes'] ?? null,

                'internal_notes' => $validated['internal_notes'] ?? null,

                'estimated_material_cost' => $materialCost,

                'estimated_labor_cost' => $laborCost,

                'estimated_miscellaneous_cost' => $miscellaneousCost,

                'estimated_total_cost' => $materialCost +

                    $laborCost +

                    $miscellaneousCost,

                'status' => $validated['action'] === 'submit'

                    ? 'submitted'

                    : 'draft',

                'submitted_at' => $validated['action'] === 'submit'

                    ? now()

                    : null,

            ]);



            $inspectionReport->save();



            $inspectionReport->materialItems()->delete();



            foreach ($materialItems as $item) {

                $quantity = (float) $item['quantity'];

                $unitCost = (float) $item['unit_cost'];



                $inspectionReport->materialItems()->create([

                    'item_name' => $item['item_name'],

                    'quantity' => $quantity,

                    'unit' => $item['unit'],

                    'unit_cost' => $unitCost,

                    'subtotal' => round($quantity * $unitCost, 2),

                ]);

            }



            if (str_starts_with($inspectionReport->report_no, 'IR-TMP-')) {

                $inspectionReport->update([

                    'report_no' => 'IR-' .

                        now()->format('Ymd') .

                        '-' .

                        str_pad($inspectionReport->id, 6, '0', STR_PAD_LEFT),

                ]);

            }



            if ($validated['action'] === 'submit') {
                $quotation->loadMissing('inspectionJobOrder');

                $quotation->update([
                    'status' => 'in_progress',
                    'completed_at' => null,
                    'inspector_notes' => $validated['internal_notes'] ?? null,
                ]);

                if ($quotation->inspectionJobOrder) {
                    $quotation->inspectionJobOrder->update([
                        'status' => 'completed',
                        'completed_at' => $quotation->inspectionJobOrder->completed_at ?? now(),
                        'completion_notes' => $validated['internal_notes'] ?? $quotation->inspectionJobOrder->completion_notes,
                    ]);
                }



                InspectionStatusLog::create([

                    'quotation_request_id' => $quotation->id,

                    'inspection_report_id' => $inspectionReport->id,

                    'updated_by' => Auth::id(),

                    'status' => 'report_submitted',

                    'notes' => 'Inspection report submitted for admin review.',

                    'recorded_at' => now(),

                ]);



                AlertService::sendToRole(

                    'hr',

                    'Inspection report submitted',

                    Auth::user()->name .

                        ' submitted inspection report ' .

                        $inspectionReport->report_no .

                        ' for ' .

                        $quotation->full_name .

                        '.',

                    route('hr.inspection-reports.show', $inspectionReport),

                    'info'

                );



                AlertService::sendToRole(

                    'admin',

                    'Inspection report submitted - ready for handoff',

                    Auth::user()->name .

                        ' submitted inspection report ' .

                        $inspectionReport->report_no .

                        ' for ' .

                        $quotation->full_name .

                        '.',

                    route('admin.quotations.index'),

                    'info'

                );



                $client = User::query()

                    ->where('role', 'client')

                    ->where('email', $quotation->email)

                    ->first();



                if ($client) {

                    AlertService::send(

                        $client,

                        'Site inspection completed',

                        'The site inspection for your request has been completed.',

                        route('client.requests.show', $quotation),

                        'success'

                    );

                }

            }

        });



        $message = $validated['action'] === 'submit'

            ? 'Inspection report submitted successfully. Admin can now send the request to HR for quotation.'

            : 'Inspection report saved as draft.';



        return back()->with('success', $message);

    }



    private function sendStatusAlerts(

        QuotationRequest $quotation,

        string $status

    ): void {

        $client = User::query()

            ->where('role', 'client')

            ->where('email', $quotation->email)

            ->first();



        if ($status === 'in_progress' && $client) {

            AlertService::send(

                $client,

                'Request in progress',

                'Your service request is now in progress.',

                route('client.requests.show', $quotation),

                'info'

            );

        }



        if ($status === 'completed') {

            AlertService::sendToRole(

                'admin',

                'Request completed',

                $quotation->full_name .

                    ' service request was marked completed by ' .

                    Auth::user()->name .

                    '.',

                route('admin.quotations.index'),

                'success'

            );



            if ($client) {

                AlertService::send(

                    $client,

                    'Request completed',

                    'Your service request has been completed.',

                    route('client.requests.show', $quotation),

                    'success'

                );

            }

        }

    }

}