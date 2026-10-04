<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Models\JobOrder;

use Carbon\Carbon;

use Illuminate\Http\Request;

use Illuminate\Pagination\LengthAwarePaginator;

use Illuminate\Support\Collection;

use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Schema;

use PhpOffice\PhpSpreadsheet\Spreadsheet;

use PhpOffice\PhpSpreadsheet\Style\Alignment;

use PhpOffice\PhpSpreadsheet\Style\Border;

use PhpOffice\PhpSpreadsheet\Style\Fill;

use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ReportController extends Controller

{

    private array $reportTypes = [

        'all' => 'All Reports',

        'job_orders' => 'Job Orders',

        'quotations' => 'Quotations',

        'warranty_claims' => 'Warranty Claims',

        'backjobs' => 'Backjobs',

        'inspector_availability' => 'Inspector Availability',

    ];

    public function index(Request $request)

    {

        $year = $request->input('year', now()->format('Y'));

        $month = $request->input('month');

        $reportType = $request->input('report_type', 'all');

        $status = $request->input('status');

        if (!array_key_exists($reportType, $this->reportTypes)) {

            $reportType = 'all';
        }

        $allowedStatuses = ['scheduled', 'in_progress', 'completed', 'cancelled', 'pending', 'approved', 'rejected', 'resolved', 'available', 'on_duty', 'off_duty', 'on_leave'];

        if ($status && !in_array($status, $allowedStatuses, true)) {

            $status = null;
        }

        $summary = $this->jobOrderSummary($year, $month);

        $moduleStats = $this->moduleStats($year, $month);

        $inspectorSummary = $this->inspectorAvailabilitySummary($year, $month);

        $incomeSummary = ['confirmed_income' => $this->incomeForPeriod($year, $month)];

        $monthlyChart = $this->monthlyOperationsChart((int) $year, $month);

        $statusChart = [

            'labels' => ['Scheduled', 'Ongoing', 'Completed', 'Cancelled'],

            'values' => [

                $summary['scheduled'],

                $summary['ongoing'],

                $summary['completed'],

                $summary['cancelled'],

            ],

        ];

        $records = $this->recordsForType($reportType, $year, $month, $status, $request);

        $recordColumns = $this->columnsForType($reportType);

        return view('admin.reports.index', compact(

            'summary',

            'moduleStats',

            'inspectorSummary',

            'incomeSummary',

            'monthlyChart',

            'statusChart',

            'records',

            'recordColumns',

            'year',

            'month',

            'status',

            'reportType'

        ));
    }

    public function exportProjects(Request $request)

    {

        $validated = $request->validate([

            'year' => ['nullable', 'digits:4'],

            'month' => ['nullable', 'integer', 'between:1,12'],

            'status' => ['nullable', 'string', 'max:60'],

            'report_type' => ['nullable', 'string', 'max:60'],

        ]);

        $year = $validated['year'] ?? now()->format('Y');

        $month = isset($validated['month']) && $validated['month'] !== '' ? (string) $validated['month'] : null;

        $status = $validated['status'] ?? null;

        $reportType = $validated['report_type'] ?? 'all';

        if (!array_key_exists($reportType, $this->reportTypes)) {

            $reportType = 'all';
        }

        $spreadsheet = new Spreadsheet();

        $spreadsheet->removeSheetByIndex(0);

        if ($reportType === 'all') {

            $this->addSummarySheet($spreadsheet, $year, $month);

            $this->addDatasetSheet($spreadsheet, 'Job Orders', $this->jobOrderExportRows($year, $month, $status));

            $this->addDatasetSheet($spreadsheet, 'Quotations', $this->quotationExportRows($year, $month, $status));

            $this->addDatasetSheet($spreadsheet, 'Warranty Claims', $this->warrantyExportRows($year, $month, $status));

            $this->addDatasetSheet($spreadsheet, 'Backjobs', $this->backjobExportRows($year, $month, $status));

            $this->addDatasetSheet($spreadsheet, 'Inspector Availability', $this->inspectorAvailabilityExportRows($year, $month, $status));
        } else {

            $this->addDatasetSheet($spreadsheet, $this->reportTypes[$reportType], $this->exportRowsForType($reportType, $year, $month, $status));
        }

        $monthPart = $month ? '-' . str_pad((string) $month, 2, '0', STR_PAD_LEFT) : '';

        $typePart = '-' . str_replace('_', '-', $reportType);

        $filename = 'wrplumb-monthly-operations-report-' . $year . $monthPart . $typePart . '.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {

            $writer = new Xlsx($spreadsheet);

            $writer->save('php://output');
        }, $filename, [

            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',

        ]);
    }

    private function jobOrderSummary(?string $year, ?string $month): array

    {

        if (!Schema::hasTable('job_orders')) {

            return ['scheduled' => 0, 'ongoing' => 0, 'completed' => 0, 'cancelled' => 0];
        }

        $query = JobOrder::query()

            ->when($year, fn($q) => $q->whereYear('created_at', $year))

            ->when($month, fn($q) => $q->whereMonth('created_at', $month));

        return [

            'scheduled' => (clone $query)->where('status', 'scheduled')->count(),

            'ongoing' => (clone $query)->where('status', 'in_progress')->count(),

            'completed' => (clone $query)->where('status', 'completed')->count(),

            'cancelled' => (clone $query)->where('status', 'cancelled')->count(),

        ];
    }

    private function moduleStats(?string $year, ?string $month): array

    {

        return [

            'job_orders' => $this->countTable('job_orders', 'created_at', $year, $month),

            'quotations' => $this->countTable($this->quotationTable(), 'created_at', $year, $month),

            'warranty_claims' => $this->countTable($this->warrantyTable(), 'created_at', $year, $month),

            'backjobs' => $this->countTable($this->backjobTable(), 'created_at', $year, $month),

            'inspector_availability' => $this->countTable('inspector_availabilities', 'availability_date', $year, $month),

        ];
    }

    private function inspectorAvailabilitySummary(?string $year, ?string $month): array

    {

        $table = 'inspector_availabilities';

        if (!Schema::hasTable($table)) {

            return ['available' => 0, 'on_duty' => 0, 'off_duty' => 0, 'on_leave' => 0];
        }

        $query = DB::table($table)

            ->when($year, fn($q) => $q->whereYear('availability_date', $year))

            ->when($month, fn($q) => $q->whereMonth('availability_date', $month));

        return [

            'available' => (clone $query)->where('status', 'available')->count(),

            'on_duty' => (clone $query)->where('status', 'on_duty')->count(),

            'off_duty' => (clone $query)->where('status', 'off_duty')->count(),

            'on_leave' => (clone $query)->where('status', 'on_leave')->count(),

        ];
    }

    private function monthlyOperationsChart(int $year, ?string $month = null): array
    {
        if ($month) {
            $monthNumber = (int) $month;
            return [
                'labels' => [Carbon::create($year, $monthNumber, 1)->format('M')],
                'jobOrders' => [$this->countTable('job_orders', 'created_at', (string) $year, (string) $monthNumber)],
                'quotations' => [$this->countTable($this->quotationTable(), 'created_at', (string) $year, (string) $monthNumber)],
                'warranties' => [$this->countTable($this->warrantyTable(), 'created_at', (string) $year, (string) $monthNumber)],
                'backjobs' => [$this->countTable($this->backjobTable(), 'created_at', (string) $year, (string) $monthNumber)],
                'income' => [$this->incomeForPeriod((string) $year, (string) $monthNumber)],
            ];
        }

        $labels = [];
        $jobOrders = [];
        $quotations = [];
        $warranties = [];
        $backjobs = [];
        $income = [];

        for ($m = 1; $m <= 12; $m++) {
            $labels[] = Carbon::create($year, $m, 1)->format('M');
            $jobOrders[] = $this->countTable('job_orders', 'created_at', (string) $year, (string) $m);
            $quotations[] = $this->countTable($this->quotationTable(), 'created_at', (string) $year, (string) $m);
            $warranties[] = $this->countTable($this->warrantyTable(), 'created_at', (string) $year, (string) $m);
            $backjobs[] = $this->countTable($this->backjobTable(), 'created_at', (string) $year, (string) $m);
            $income[] = $this->incomeForPeriod((string) $year, (string) $m);
        }

        return compact('labels', 'jobOrders', 'quotations', 'warranties', 'backjobs', 'income');
    }

    private function recordsForType(string $type, ?string $year, ?string $month, ?string $status, Request $request): LengthAwarePaginator

    {

        $perPage = 8;

        $page = LengthAwarePaginator::resolveCurrentPage();

        $rows = match ($type) {

            'job_orders' => $this->jobOrderRows($year, $month, $status, true),

            'quotations' => $this->quotationRows($year, $month, $status, true),

            'warranty_claims' => $this->warrantyRows($year, $month, $status, true),

            'backjobs' => $this->backjobRows($year, $month, $status, true),

            'inspector_availability' => $this->inspectorAvailabilityRows($year, $month, $status, true),

            default => $this->allReportRows($year, $month, $status),
        };

        $items = $rows instanceof Collection ? $rows : collect($rows);

        $paginated = new LengthAwarePaginator(

            $items->forPage($page, $perPage)->values(),

            $items->count(),

            $perPage,

            $page,

            ['path' => $request->url(), 'query' => $request->query()]

        );

        return $paginated;
    }

    private function allReportRows(?string $year, ?string $month, ?string $status): Collection

    {

        return collect()

            ->merge($this->jobOrderRows($year, $month, $status)->take(12))

            ->merge($this->quotationRows($year, $month, $status)->take(12))

            ->merge($this->warrantyRows($year, $month, $status)->take(12))

            ->merge($this->backjobRows($year, $month, $status)->take(12))

            ->sortByDesc('created_at')

            ->values();
    }

    private function columnsForType(string $type): array

    {

        return match ($type) {

            'inspector_availability' => ['Inspector', 'Available', 'On Duty', 'Off Duty', 'On Leave', 'No Record / Days Without Entry'],

            default => ['Type', 'Reference No.', 'Client / Related Person', 'Subject / Service', 'Status', 'Date', 'Remarks'],
        };
    }

    private function jobOrderRows(?string $year, ?string $month, ?string $status, bool $allowAnyStatus = false): Collection

    {

        if (!Schema::hasTable('job_orders')) return collect();

        return JobOrder::query()

            ->with(['quotationRequest', 'worker'])

            ->when($year, fn($q) => $q->whereYear('created_at', $year))

            ->when($month, fn($q) => $q->whereMonth('created_at', $month))

            ->when($status && in_array($status, ['scheduled', 'in_progress', 'completed', 'cancelled'], true), fn($q) => $q->where('status', $status))

            ->latest()

            ->get()

            ->map(function ($jobOrder) {

                $client = $jobOrder->quotationRequest?->full_name

                    ?? trim(($jobOrder->quotationRequest?->first_name ?? '') . ' ' . ($jobOrder->quotationRequest?->last_name ?? ''));

                return [

                    'type' => 'Job Order',

                    'reference' => $jobOrder->job_order_no ?? 'JO-' . $jobOrder->id,

                    'client' => $client ?: '—',

                    'subject' => $jobOrder->service_type ?? '—',

                    'status' => $jobOrder->status ?? '—',

                    'date' => optional($jobOrder->scheduled_date)->format('M d, Y') ?? optional($jobOrder->created_at)->format('M d, Y'),

                    'remarks' => 'Worker: ' . ($jobOrder->worker?->name ?? $jobOrder->worker?->first_name ?? 'Not assigned'),

                    'created_at' => $jobOrder->created_at,

                ];
            });
    }

    private function quotationRows(?string $year, ?string $month, ?string $status, bool $allowAnyStatus = false): Collection

    {

        $table = $this->quotationTable();

        if (!$table) return collect();

        $query = DB::table($table)

            ->when($year && Schema::hasColumn($table, 'created_at'), fn($q) => $q->whereYear('created_at', $year))

            ->when($month && Schema::hasColumn($table, 'created_at'), fn($q) => $q->whereMonth('created_at', $month));

        if ($status && Schema::hasColumn($table, 'status')) {

            $query->where('status', $status);
        }

        return $query->orderByDesc(Schema::hasColumn($table, 'created_at') ? 'created_at' : 'id')->get()->map(function ($row) use ($table) {

            $client = $row->full_name ?? trim(($row->first_name ?? '') . ' ' . ($row->last_name ?? '')) ?: '—';

            $reference = $row->quotation_no ?? $row->request_no ?? ('REQ-' . str_pad((string) $row->id, 4, '0', STR_PAD_LEFT));

            $service = $row->service_type ?? $row->service_category ?? '—';

            $date = $row->preferred_date ?? $row->appointment_date ?? $row->created_at ?? null;

            return [

                'type' => 'Quotation',

                'reference' => $reference,

                'client' => $client,

                'subject' => $service,

                'status' => $row->status ?? '—',

                'date' => $date ? Carbon::parse($date)->format('M d, Y') : '—',

                'remarks' => $row->email ?? '',

                'created_at' => isset($row->created_at) ? Carbon::parse($row->created_at) : now(),

            ];
        });
    }

    private function warrantyRows(?string $year, ?string $month, ?string $status, bool $allowAnyStatus = false): Collection

    {

        $table = $this->warrantyTable();

        if (!$table) return collect();

        $query = DB::table($table)

            ->leftJoin('job_orders', $table . '.job_order_id', '=', 'job_orders.id')

            ->when(Schema::hasTable('users') && Schema::hasColumn($table, 'client_id'), fn($q) => $q->leftJoin('users as client', $table . '.client_id', '=', 'client.id'))

            ->when($year && Schema::hasColumn($table, 'created_at'), fn($q) => $q->whereYear($table . '.created_at', $year))

            ->when($month && Schema::hasColumn($table, 'created_at'), fn($q) => $q->whereMonth($table . '.created_at', $month));

        if ($status && Schema::hasColumn($table, 'status')) {

            $query->where($table . '.status', $status);
        }

        $clientNameSelect = (Schema::hasTable('users') && Schema::hasColumn($table, 'client_id'))

            ? DB::raw("COALESCE(client.name, CONCAT(COALESCE(client.first_name,''), ' ', COALESCE(client.last_name,''))) as client_name")

            : DB::raw("'—' as client_name");

        return $query->select($table . '.*', 'job_orders.job_order_no', $clientNameSelect)

            ->orderByDesc($table . '.created_at')

            ->get()

            ->map(function ($row) use ($table) {

                return [

                    'type' => 'Warranty Claim',

                    'reference' => $row->claim_no ?? ('WC-' . str_pad((string) $row->id, 4, '0', STR_PAD_LEFT)),

                    'client' => trim($row->client_name ?? '') ?: '—',

                    'subject' => $row->issue_description ?? ($row->job_order_no ?? '—'),

                    'status' => $row->status ?? '—',

                    'date' => isset($row->created_at) ? Carbon::parse($row->created_at)->format('M d, Y') : '—',

                    'remarks' => $row->job_order_no ? 'Related JO: ' . $row->job_order_no : '',

                    'created_at' => isset($row->created_at) ? Carbon::parse($row->created_at) : now(),

                ];
            });
    }

    private function backjobRows(?string $year, ?string $month, ?string $status, bool $allowAnyStatus = false): Collection

    {

        $table = $this->backjobTable();

        if (!$table) return collect();

        $jobOrderForeignKey = match (true) {

            Schema::hasColumn($table, 'original_job_order_id') => 'original_job_order_id',

            Schema::hasColumn($table, 'job_order_id') => 'job_order_id',

            default => null,
        };

        $query = DB::table($table)

            ->when(

                $jobOrderForeignKey && Schema::hasTable('job_orders'),

                fn($q) => $q->leftJoin('job_orders', $table . '.' . $jobOrderForeignKey, '=', 'job_orders.id')

            )

            ->when(

                Schema::hasTable('quotation_requests') && Schema::hasColumn($table, 'quotation_request_id'),

                fn($q) => $q->leftJoin('quotation_requests', $table . '.quotation_request_id', '=', 'quotation_requests.id')

            )

            ->when(

                Schema::hasTable('users') && Schema::hasColumn($table, 'worker_id'),

                fn($q) => $q->leftJoin('users as worker', $table . '.worker_id', '=', 'worker.id')

            )

            ->when($year && Schema::hasColumn($table, 'created_at'), fn($q) => $q->whereYear($table . '.created_at', $year))

            ->when($month && Schema::hasColumn($table, 'created_at'), fn($q) => $q->whereMonth($table . '.created_at', $month));

        if ($status && Schema::hasColumn($table, 'status')) {

            $query->where($table . '.status', $status);
        }

        $jobOrderNoSelect = ($jobOrderForeignKey && Schema::hasTable('job_orders'))

            ? 'job_orders.job_order_no'

            : DB::raw("'—' as job_order_no");

        $clientNameSelect = (Schema::hasTable('quotation_requests') && Schema::hasColumn($table, 'quotation_request_id'))

            ? DB::raw("TRIM(CONCAT(COALESCE(quotation_requests.first_name,''), ' ', COALESCE(quotation_requests.last_name,''))) as client_name")

            : DB::raw("'—' as client_name");

        $workerNameSelect = (Schema::hasTable('users') && Schema::hasColumn($table, 'worker_id'))

            ? DB::raw("COALESCE(worker.name, CONCAT(COALESCE(worker.first_name,''), ' ', COALESCE(worker.last_name,''))) as worker_name")

            : DB::raw("'Not assigned' as worker_name");

        return $query->select($table . '.*', $jobOrderNoSelect, $clientNameSelect, $workerNameSelect)

            ->orderByDesc($table . '.created_at')

            ->get()

            ->map(function ($row) {

                $date = $row->scheduled_date ?? $row->created_at ?? null;

                $client = trim($row->client_name ?? '');

                $worker = trim($row->worker_name ?? '');

                return [

                    'type' => 'Backjob',

                    'reference' => $row->backjob_no ?? ('BJ-' . str_pad((string) $row->id, 4, '0', STR_PAD_LEFT)),

                    'client' => $client && $client !== '—' ? $client : 'Worker: ' . ($worker ?: 'Not assigned'),

                    'subject' => $row->reason ?? ($row->job_order_no ?? '—'),

                    'status' => $row->status ?? '—',

                    'date' => $date ? Carbon::parse($date)->format('M d, Y') : '—',

                    'remarks' => trim(($row->job_order_no ? 'Related JO: ' . $row->job_order_no : '') . ($worker ? ' • Worker: ' . $worker : '')),

                    'created_at' => isset($row->created_at) ? Carbon::parse($row->created_at) : now(),

                ];
            });
    }

    private function inspectorAvailabilityRows(?string $year, ?string $month, ?string $status, bool $allowAnyStatus = false): Collection

    {

        $table = 'inspector_availabilities';

        if (!Schema::hasTable($table)) return collect();

        $query = DB::table($table)

            ->leftJoin('users', $table . '.inspector_id', '=', 'users.id')

            ->when($year, fn($q) => $q->whereYear($table . '.availability_date', $year))

            ->when($month, fn($q) => $q->whereMonth($table . '.availability_date', $month));

        if ($status && in_array($status, ['available', 'on_duty', 'off_duty', 'on_leave'], true)) {

            $query->where($table . '.status', $status);
        }

        return $query->select(

            'users.id as inspector_id',

            DB::raw("COALESCE(users.name, CONCAT(COALESCE(users.first_name,''), ' ', COALESCE(users.last_name,'')), 'Unassigned Inspector') as inspector_name"),

            DB::raw("SUM(CASE WHEN {$table}.status = 'available' THEN 1 ELSE 0 END) as available_count"),

            DB::raw("SUM(CASE WHEN {$table}.status = 'on_duty' THEN 1 ELSE 0 END) as on_duty_count"),

            DB::raw("SUM(CASE WHEN {$table}.status = 'off_duty' THEN 1 ELSE 0 END) as off_duty_count"),

            DB::raw("SUM(CASE WHEN {$table}.status = 'on_leave' THEN 1 ELSE 0 END) as on_leave_count"),

            DB::raw("COUNT({$table}.id) as record_count"),

            DB::raw("MAX({$table}.availability_date) as latest_date")

        )

            ->groupBy('users.id', 'users.name', 'users.first_name', 'users.last_name')

            ->orderBy('inspector_name')

            ->get()

            ->map(function ($row) use ($year, $month) {

                $daysInPeriod = $month ? Carbon::create((int) $year, (int) $month, 1)->daysInMonth : 365;

                $noRecord = max(0, $daysInPeriod - (int) $row->record_count);

                return [

                    'inspector' => trim($row->inspector_name) ?: 'Unassigned Inspector',

                    'available' => (int) $row->available_count,

                    'on_duty' => (int) $row->on_duty_count,

                    'off_duty' => (int) $row->off_duty_count,

                    'on_leave' => (int) $row->on_leave_count,

                    'no_record' => $noRecord,

                    'latest_date' => $row->latest_date,

                    'created_at' => $row->latest_date ? Carbon::parse($row->latest_date) : now(),

                ];
            });
    }

    private function exportRowsForType(string $type, ?string $year, ?string $month, ?string $status): array

    {

        return match ($type) {

            'job_orders' => $this->jobOrderExportRows($year, $month, $status),

            'quotations' => $this->quotationExportRows($year, $month, $status),

            'warranty_claims' => $this->warrantyExportRows($year, $month, $status),

            'backjobs' => $this->backjobExportRows($year, $month, $status),

            'inspector_availability' => $this->inspectorAvailabilityExportRows($year, $month, $status),

            default => [],
        };
    }

    private function jobOrderExportRows(?string $year, ?string $month, ?string $status): array

    {

        $rows = $this->jobOrderRows($year, $month, $status)->map(fn($row) => [

            $row['reference'],
            $row['client'],
            $row['subject'],
            $row['status'],
            $row['date'],
            $row['remarks'],

        ])->toArray();

        return array_merge([['Reference No.', 'Client', 'Service', 'Status', 'Schedule / Date', 'Remarks']], $rows);
    }

    private function quotationExportRows(?string $year, ?string $month, ?string $status): array

    {

        $rows = $this->quotationRows($year, $month, $status)->map(fn($row) => [

            $row['reference'],
            $row['client'],
            $row['subject'],
            $row['status'],
            $row['date'],
            $row['remarks'],

        ])->toArray();

        return array_merge([['Reference No.', 'Client', 'Service', 'Status', 'Date', 'Email / Remarks']], $rows);
    }

    private function warrantyExportRows(?string $year, ?string $month, ?string $status): array

    {

        $rows = $this->warrantyRows($year, $month, $status)->map(fn($row) => [

            $row['reference'],
            $row['client'],
            $row['subject'],
            $row['status'],
            $row['date'],
            $row['remarks'],

        ])->toArray();

        return array_merge([['Claim No.', 'Client', 'Issue / Subject', 'Status', 'Date', 'Related Record']], $rows);
    }

    private function backjobExportRows(?string $year, ?string $month, ?string $status): array

    {

        $rows = $this->backjobRows($year, $month, $status)->map(fn($row) => [

            $row['reference'],
            $row['client'],
            $row['subject'],
            $row['status'],
            $row['date'],
            $row['remarks'],

        ])->toArray();

        return array_merge([['Backjob No.', 'Worker', 'Reason / Subject', 'Status', 'Date', 'Related Record']], $rows);
    }

    private function inspectorAvailabilityExportRows(?string $year, ?string $month, ?string $status): array

    {

        $rows = $this->inspectorAvailabilityRows($year, $month, $status)->map(fn($row) => [

            $row['inspector'],
            $row['available'],
            $row['on_duty'],
            $row['off_duty'],
            $row['on_leave'],
            $row['no_record'],
            $row['latest_date'],

        ])->toArray();

        return array_merge([['Inspector', 'Available Days', 'On Duty Days', 'Off Duty Days', 'On Leave Days', 'No Record Days', 'Latest Record']], $rows);
    }

    private function addSummarySheet(Spreadsheet $spreadsheet, ?string $year, ?string $month): void

    {

        $summary = $this->jobOrderSummary($year, $month);

        $modules = $this->moduleStats($year, $month);

        $inspector = $this->inspectorAvailabilitySummary($year, $month);

        $income = $this->incomeForPeriod($year, $month);

        $rows = [

            ['WRPlumb Monthly Operations Report'],

            ['Period', $month ? Carbon::create((int) $year, (int) $month, 1)->format('F Y') : $year],

            ['Generated At', now()->format('Y-m-d H:i')],

            [],

            ['Metric', 'Value'],

            ['Job Orders', $modules['job_orders']],

            ['Quotations', $modules['quotations']],

            ['Warranty Claims', $modules['warranty_claims']],

            ['Backjobs', $modules['backjobs']],

            ['Inspector Availability Records', $modules['inspector_availability']],

            ['Completed Jobs', $summary['completed']],

            ['Scheduled Jobs', $summary['scheduled']],

            ['Ongoing Jobs', $summary['ongoing']],

            ['Cancelled Jobs', $summary['cancelled']],

            ['Available Days', $inspector['available']],

            ['On Duty Days', $inspector['on_duty']],

            ['Off Duty Days', $inspector['off_duty']],

            ['On Leave Days', $inspector['on_leave']],

            ['Confirmed Income', $income],

        ];

        $this->addDatasetSheet($spreadsheet, 'Summary', $rows);
    }

    private function addDatasetSheet(Spreadsheet $spreadsheet, string $title, array $rows): void

    {

        $sheet = $spreadsheet->createSheet();

        $sheet->setTitle(substr($title, 0, 31));

        if (empty($rows)) {

            $rows = [['No records found']];
        }

        $sheet->fromArray($rows, null, 'A1');

        $lastColumn = $sheet->getHighestColumn();

        $lastRow = max(1, $sheet->getHighestRow());

        $sheet->getStyle('A1:' . $lastColumn . '1')->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');

        $sheet->getStyle('A1:' . $lastColumn . '1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('0F4C81');

        $sheet->getStyle('A1:' . $lastColumn . $lastRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        $sheet->getStyle('A1:' . $lastColumn . $lastRow)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

        foreach (range('A', $lastColumn) as $column) {

            $sheet->getColumnDimension($column)->setAutoSize(true);
        }
    }

    private function countTable(?string $table, string $dateColumn, ?string $year, ?string $month): int

    {

        if (!$table || !Schema::hasTable($table)) return 0;

        $query = DB::table($table);

        if (Schema::hasColumn($table, $dateColumn)) {

            $query->when($year, fn($q) => $q->whereYear($dateColumn, $year))

                ->when($month, fn($q) => $q->whereMonth($dateColumn, $month));
        }

        return (int) $query->count();
    }

    private function incomeForPeriod(?string $year, ?string $month): float

    {

        if (!Schema::hasTable('payments') || !Schema::hasColumn('payments', 'amount')) return 0;

        $query = DB::table('payments');

        if (Schema::hasColumn('payments', 'status')) {

            $query->where('status', 'confirmed');
        }

        $dateColumn = Schema::hasColumn('payments', 'payment_date') ? 'payment_date' : (Schema::hasColumn('payments', 'created_at') ? 'created_at' : null);

        if ($dateColumn) {

            $query->when($year, fn($q) => $q->whereYear($dateColumn, $year))

                ->when($month, fn($q) => $q->whereMonth($dateColumn, $month));
        }

        return round((float) $query->sum('amount'), 2);
    }

    private function quotationTable(): ?string

    {

        return Schema::hasTable('quotation_requests') ? 'quotation_requests' : (Schema::hasTable('quotations') ? 'quotations' : null);
    }

    private function warrantyTable(): ?string

    {

        return Schema::hasTable('warranty_claims') ? 'warranty_claims' : null;
    }

    private function backjobTable(): ?string

    {

        return Schema::hasTable('back_jobs') ? 'back_jobs' : (Schema::hasTable('backjobs') ? 'backjobs' : null);
    }
}
