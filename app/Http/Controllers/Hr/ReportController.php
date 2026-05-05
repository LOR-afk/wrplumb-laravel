<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Receipt;
use Illuminate\Http\Request;
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

        $paymentsQuery = Payment::query();

        if ($year) {
            $paymentsQuery->whereYear('payment_date', $year);
        }

        if ($month) {
            $paymentsQuery->whereMonth('payment_date', $month);
        }

        $summary = [
            'total_income' => (clone $paymentsQuery)->where('status', 'confirmed')->sum('amount'),
            'confirmed_count' => (clone $paymentsQuery)->where('status', 'confirmed')->count(),
            'rejected_count' => (clone $paymentsQuery)->where('status', 'rejected')->count(),
            'pending_count' => (clone $paymentsQuery)->whereIn('status', ['pending', 'pending_verification'])->count(),
            'invoice_count' => Invoice::query()
                ->when($year, fn ($q) => $q->whereYear('invoice_date', $year))
                ->when($month, fn ($q) => $q->whereMonth('invoice_date', $month))
                ->count(),
            'receipt_count' => Receipt::query()
                ->when($year, fn ($q) => $q->whereYear('receipt_date', $year))
                ->when($month, fn ($q) => $q->whereMonth('receipt_date', $month))
                ->count(),
        ];

        return view('hr.reports.index', compact('summary', 'year', 'month'));
    }

    public function exportIncome(Request $request)
    {
        $validated = $request->validate([
            'year' => ['nullable', 'digits:4'],
            'month' => ['nullable', 'integer', 'between:1,12'],
            'status' => ['nullable', 'string'],
            'method' => ['nullable', 'string'],
        ]);

        $year = $validated['year'] ?? now()->format('Y');
        $month = $validated['month'] ?? null;
        $status = $validated['status'] ?? null;
        $method = $validated['method'] ?? null;

        $payments = Payment::query()
            ->with([
                'invoice.quotation.request',
                'paymentSchedule',
                'receipt',
            ])
            ->when($year, fn ($q) => $q->whereYear('payment_date', $year))
            ->when($month, fn ($q) => $q->whereMonth('payment_date', $month))
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($method, fn ($q) => $q->where('payment_method', $method))
            ->orderByDesc('payment_date')
            ->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Income Report');

        $headers = [
            'Payment No.',
            'Invoice No.',
            'Client',
            'Payment Schedule',
            'Amount',
            'Method',
            'Reference No.',
            'Status',
            'Payment Date',
            'Receipt No.',
        ];

        $sheet->fromArray($headers, null, 'A1');

        $row = 2;
        foreach ($payments as $payment) {
            $client = $payment->invoice?->quotation?->request?->full_name
                ?? $payment->invoice?->quotation?->request?->email
                ?? '—';

            $sheet->fromArray([
                $payment->payment_no,
                $payment->invoice?->invoice_no ?? '—',
                $client,
                $payment->paymentSchedule?->label ?? '—',
                (float) $payment->amount,
                $payment->payment_method ?? '—',
                $payment->reference_number ?? '—',
                strtoupper(str_replace('_', ' ', $payment->status)),
                optional($payment->payment_date)->format('Y-m-d'),
                $payment->receipt?->receipt_no ?? '—',
            ], null, 'A' . $row);

            $row++;
        }

        $lastRow = max($row - 1, 1);

        $sheet->getStyle('A1:J1')->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $sheet->getStyle('A1:J1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('0F4C81');
        $sheet->getStyle('A1:J' . $lastRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('A1:J' . $lastRow)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('E2:E' . $lastRow)->getNumberFormat()->setFormatCode('#,##0.00');

        foreach (range('A', 'J') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        $monthPart = $month ? '-' . str_pad((string) $month, 2, '0', STR_PAD_LEFT) : '';
        $filename = 'hr-income-report-' . $year . $monthPart . '.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
