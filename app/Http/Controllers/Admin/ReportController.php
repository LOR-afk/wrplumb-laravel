<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JobOrder;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $year = $request->input('year', now()->format('Y'));
        $month = $request->input('month');
        $status = $request->input('status');

        if ($status && !in_array($status, ['scheduled', 'in_progress', 'completed', 'cancelled'], true)) {
            $status = null;
        }

        $summaryQuery = JobOrder::query()
            ->when($year, fn ($q) => $q->whereYear('created_at', $year))
            ->when($month, fn ($q) => $q->whereMonth('created_at', $month));

        $summary = [
            'scheduled' => (clone $summaryQuery)->where('status', 'scheduled')->count(),
            'ongoing' => (clone $summaryQuery)->where('status', 'in_progress')->count(),
            'completed' => (clone $summaryQuery)->where('status', 'completed')->count(),
            'cancelled' => (clone $summaryQuery)->where('status', 'cancelled')->count(),
        ];

        $incomeSummary = [
            'confirmed_income' => $this->incomeForPeriod($year, $month),
        ];

        $monthlyChart = $this->monthlyChart((int) $year);
        $statusChart = [
            'labels' => ['Scheduled', 'Ongoing', 'Completed', 'Cancelled'],
            'values' => [
                $summary['scheduled'],
                $summary['ongoing'],
                $summary['completed'],
                $summary['cancelled'],
            ],
        ];

        $jobOrders = JobOrder::query()
            ->with(['quotationRequest', 'worker'])
            ->when($year, fn ($q) => $q->whereYear('created_at', $year))
            ->when($month, fn ($q) => $q->whereMonth('created_at', $month))
            ->when($status, fn ($q) => $q->where('status', $status))
            ->orderByDesc('created_at')
            ->paginate(8)
            ->withQueryString();

        return view('admin.reports.index', compact(
            'summary',
            'incomeSummary',
            'monthlyChart',
            'statusChart',
            'jobOrders',
            'year',
            'month',
            'status'
        ));
    }

    public function exportProjects(Request $request)
    {
        $validated = $request->validate([
            'year' => ['nullable', 'digits:4'],
            'month' => ['nullable', 'integer', 'between:1,12'],
            'status' => ['nullable', 'in:scheduled,in_progress,completed,cancelled'],
        ]);

        $year = $validated['year'] ?? now()->format('Y');
        $month = $validated['month'] ?? null;
        $status = $validated['status'] ?? null;

        $jobOrders = JobOrder::query()
            ->with(['quotationRequest', 'worker'])
            ->when($year, fn ($q) => $q->whereYear('created_at', $year))
            ->when($month, fn ($q) => $q->whereMonth('created_at', $month))
            ->when($status, fn ($q) => $q->where('status', $status))
            ->orderByDesc('created_at')
            ->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Project Report');

        $headers = [
            'Job Order No.',
            'Client',
            'Service Type',
            'Service Flow',
            'Assigned Personnel / Inspector',
            'Status',
            'Scheduled Date',
            'Scheduled Time',
            'Started At',
            'Completed At',
            'Cancelled At',
            'Created At',
        ];

        $sheet->fromArray($headers, null, 'A1');

        $row = 2;
        foreach ($jobOrders as $jobOrder) {
            $client = $jobOrder->quotationRequest?->full_name
                ?? trim(($jobOrder->quotationRequest?->first_name ?? '') . ' ' . ($jobOrder->quotationRequest?->last_name ?? ''));

            $sheet->fromArray([
                $jobOrder->job_order_no,
                $client ?: '—',
                $jobOrder->service_type ?? '—',
                strtoupper(str_replace('_', ' ', $jobOrder->service_flow ?? '—')),
                $jobOrder->worker?->name ?? $jobOrder->worker?->first_name ?? 'Not assigned',
                strtoupper(str_replace('_', ' ', $jobOrder->status)),
                optional($jobOrder->scheduled_date)->format('Y-m-d'),
                $jobOrder->scheduled_time ?? '—',
                optional($jobOrder->started_at)->format('Y-m-d H:i'),
                optional($jobOrder->completed_at)->format('Y-m-d H:i'),
                optional($jobOrder->cancelled_at)->format('Y-m-d H:i'),
                optional($jobOrder->created_at)->format('Y-m-d H:i'),
            ], null, 'A' . $row);

            $row++;
        }

        $lastRow = max($row - 1, 1);

        $sheet->getStyle('A1:L1')->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $sheet->getStyle('A1:L1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('0F4C81');
        $sheet->getStyle('A1:L' . $lastRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('A1:L' . $lastRow)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

        foreach (range('A', 'L') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        $monthPart = $month ? '-' . str_pad((string) $month, 2, '0', STR_PAD_LEFT) : '';
        $statusPart = $status ? '-' . str_replace('_', '-', $status) : '-all';
        $filename = 'admin-project-report-' . $year . $monthPart . $statusPart . '.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    private function monthlyChart(int $year): array
    {
        $labels = [];
        $projects = [];
        $income = [];

        for ($m = 1; $m <= 12; $m++) {
            $month = Carbon::create($year, $m, 1);
            $labels[] = $month->format('M');

            $projects[] = JobOrder::query()
                ->whereYear('created_at', $year)
                ->whereMonth('created_at', $m)
                ->count();

            $income[] = $this->incomeForPeriod((string) $year, (string) $m);
        }

        return [
            'labels' => $labels,
            'projects' => $projects,
            'income' => $income,
        ];
    }

    private function incomeForPeriod(?string $year, ?string $month): float
    {
        if (!Schema::hasTable('payments') || !Schema::hasColumn('payments', 'amount')) {
            return 0;
        }

        $query = DB::table('payments');

        if (Schema::hasColumn('payments', 'status')) {
            $query->where('status', 'confirmed');
        }

        if (Schema::hasColumn('payments', 'payment_date')) {
            if ($year) {
                $query->whereYear('payment_date', $year);
            }

            if ($month) {
                $query->whereMonth('payment_date', $month);
            }
        } elseif (Schema::hasColumn('payments', 'created_at')) {
            if ($year) {
                $query->whereYear('created_at', $year);
            }

            if ($month) {
                $query->whereMonth('created_at', $month);
            }
        }

        return round((float) $query->sum('amount'), 2);
    }
}
