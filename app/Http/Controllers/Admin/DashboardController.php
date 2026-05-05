<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $range = $request->get('range', 'month');
        $range = in_array($range, ['today', 'week', 'month', 'year'], true) ? $range : 'month';

        [$startDate, $endDate, $rangeLabel] = $this->resolveDateRange($range);

        $clientCount = $this->countUsersByRole('client');
        $workerCount = $this->countUsersByRole('worker');

        $availableInspectorsCount = $this->availableInspectorsCount($workerCount);

        $pendingQuotationsCount = $this->countByStatuses('quotation_requests', 'status', [
            'pending',
            'submitted',
            'new',
            'for_review',
        ]);

        $assignedQuotationsCount = $this->countByStatuses('quotation_requests', 'status', [
            'assigned',
            'in_progress',
            'job_in_progress',
        ]);

        $activeJobOrdersCount = $this->countByStatuses('job_orders', 'status', [
            'assigned',
            'approved',
            'scheduled',
            'in_progress',
            'ongoing',
        ]);

        $completedJobOrdersCount = $this->countByStatuses('job_orders', 'status', [
            'completed',
            'done',
        ]);

        $pendingSupportCount = $this->countSupportPending();
        $openSupportCount = $pendingSupportCount;

        $cancelledRequestsCount = $this->countCancelledRequests();

        $monthlyRevenue = $this->revenueForRange($startDate, $endDate);

        $incomeChart = $this->monthlyIncomeChart();
        $quotationStatusChart = $this->statusChart('quotation_requests', 'status');
        $jobOrderStatusChart = $this->statusChart('job_orders', 'status');
        $supportStatusChart = $this->statusChart('support_conversations', 'current_queue');

        $recentActivities = $this->recentActivities();

        return view('admin.dashboard', compact(
            'range',
            'rangeLabel',
            'clientCount',
            'workerCount',
            'availableInspectorsCount',
            'pendingQuotationsCount',
            'assignedQuotationsCount',
            'activeJobOrdersCount',
            'completedJobOrdersCount',
            'pendingSupportCount',
            'openSupportCount',
            'monthlyRevenue',
            'cancelledRequestsCount',
            'incomeChart',
            'quotationStatusChart',
            'jobOrderStatusChart',
            'supportStatusChart',
            'recentActivities'
        ));
    }

    private function resolveDateRange(string $range): array
    {
        $now = now();

        return match ($range) {
            'today' => [$now->copy()->startOfDay(), $now->copy()->endOfDay(), 'Today'],
            'week' => [$now->copy()->startOfWeek(), $now->copy()->endOfWeek(), 'This Week'],
            'year' => [$now->copy()->startOfYear(), $now->copy()->endOfYear(), 'This Year'],
            default => [$now->copy()->startOfMonth(), $now->copy()->endOfMonth(), 'This Month'],
        };
    }

    private function countUsersByRole(string $role): int
    {
        if (!Schema::hasTable('users') || !Schema::hasColumn('users', 'role')) {
            return 0;
        }

        return DB::table('users')
            ->where('role', $role)
            ->count();
    }

    private function availableInspectorsCount(int $fallbackWorkerCount): int
    {
        if (
            Schema::hasTable('inspector_availabilities') &&
            Schema::hasColumn('inspector_availabilities', 'availability_date') &&
            Schema::hasColumn('inspector_availabilities', 'status') &&
            Schema::hasColumn('inspector_availabilities', 'inspector_id')
        ) {
            return DB::table('inspector_availabilities')
                ->whereDate('availability_date', today())
                ->where('status', 'available')
                ->distinct('inspector_id')
                ->count('inspector_id');
        }

        return $fallbackWorkerCount;
    }

    private function countByStatuses(string $table, string $column, array $statuses): int
    {
        if (!Schema::hasTable($table) || !Schema::hasColumn($table, $column)) {
            return 0;
        }

        return DB::table($table)
            ->whereIn($column, $statuses)
            ->count();
    }

    private function countSupportPending(): int
    {
        if (!Schema::hasTable('support_conversations')) {
            return 0;
        }

        $query = DB::table('support_conversations');

        if (Schema::hasColumn('support_conversations', 'current_queue')) {
            $query->whereIn('current_queue', ['hr', 'admin']);
        }

        if (Schema::hasColumn('support_conversations', 'status')) {
            $query->whereIn('status', ['open', 'routed', 'escalated']);
        }

        return $query->count();
    }

    private function countCancelledRequests(): int
    {
        $total = 0;

        if (Schema::hasTable('quotation_requests') && Schema::hasColumn('quotation_requests', 'status')) {
            $total += DB::table('quotation_requests')
                ->whereIn('status', ['cancelled', 'canceled', 'rejected'])
                ->count();
        }

        if (Schema::hasTable('job_orders') && Schema::hasColumn('job_orders', 'status')) {
            $total += DB::table('job_orders')
                ->whereIn('status', ['cancelled', 'canceled', 'rejected'])
                ->count();
        }

        return $total;
    }

    private function revenueForRange(Carbon $startDate, Carbon $endDate): float
    {
        if (!Schema::hasTable('payments') || !Schema::hasColumn('payments', 'amount')) {
            return 0;
        }

        $query = DB::table('payments');

        if (Schema::hasColumn('payments', 'status')) {
            $query->where('status', 'confirmed');
        }

        if (Schema::hasColumn('payments', 'payment_date')) {
            $query->whereBetween('payment_date', [
                $startDate->toDateString(),
                $endDate->toDateString(),
            ]);
        } elseif (Schema::hasColumn('payments', 'created_at')) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        return (float) $query->sum('amount');
    }

    private function monthlyIncomeChart(): array
    {
        $labels = [];
        $values = [];

        for ($i = 5; $i >= 0; $i--) {
            $month = now()->copy()->subMonths($i);
            $labels[] = $month->format('M Y');

            if (!Schema::hasTable('payments') || !Schema::hasColumn('payments', 'amount')) {
                $values[] = 0;
                continue;
            }

            $query = DB::table('payments');

            if (Schema::hasColumn('payments', 'status')) {
                $query->where('status', 'confirmed');
            }

            if (Schema::hasColumn('payments', 'payment_date')) {
                $query->whereBetween('payment_date', [
                    $month->copy()->startOfMonth()->toDateString(),
                    $month->copy()->endOfMonth()->toDateString(),
                ]);
            } elseif (Schema::hasColumn('payments', 'created_at')) {
                $query->whereBetween('created_at', [
                    $month->copy()->startOfMonth(),
                    $month->copy()->endOfMonth(),
                ]);
            }

            $values[] = round((float) $query->sum('amount'), 2);
        }

        return [
            'labels' => $labels,
            'values' => $values,
        ];
    }

    private function statusChart(string $table, string $statusColumn): array
    {
        if (!Schema::hasTable($table) || !Schema::hasColumn($table, $statusColumn)) {
            return [
                'labels' => ['No Data'],
                'values' => [0],
            ];
        }

        $rows = DB::table($table)
            ->select($statusColumn, DB::raw('COUNT(*) as total'))
            ->groupBy($statusColumn)
            ->orderByDesc('total')
            ->get();

        if ($rows->isEmpty()) {
            return [
                'labels' => ['No Data'],
                'values' => [0],
            ];
        }

        return [
            'labels' => $rows->map(function ($row) use ($statusColumn) {
                return ucwords(str_replace('_', ' ', $row->{$statusColumn} ?? 'Unknown'));
            })->values()->toArray(),
            'values' => $rows->map(fn ($row) => (int) $row->total)->values()->toArray(),
        ];
    }

    private function recentActivities(): array
    {
        $activities = [];

        if (Schema::hasTable('quotation_requests')) {
            $query = DB::table('quotation_requests');

            if (Schema::hasColumn('quotation_requests', 'created_at')) {
                $query->orderByDesc('created_at');
            } else {
                $query->orderByDesc('id');
            }

            foreach ($query->limit(4)->get() as $request) {
                $rawTime = $request->created_at ?? null;

                $activities[] = [
                    'type' => 'quotation',
                    'icon' => 'fa-file-signature',
                    'title' => 'New quotation request submitted',
                    'description' => ($request->full_name ?? $request->email ?? 'Client') . ' submitted a service request.',
                    'time' => $this->formatDateTime($rawTime),
                    'time_raw' => $rawTime,
                ];
            }
        }

        if (Schema::hasTable('job_orders')) {
            $query = DB::table('job_orders');

            if (Schema::hasColumn('job_orders', 'updated_at')) {
                $query->orderByDesc('updated_at');
            } elseif (Schema::hasColumn('job_orders', 'created_at')) {
                $query->orderByDesc('created_at');
            } else {
                $query->orderByDesc('id');
            }

            foreach ($query->limit(4)->get() as $jobOrder) {
                $status = ucwords(str_replace('_', ' ', $jobOrder->status ?? 'updated'));
                $rawTime = $jobOrder->updated_at ?? $jobOrder->created_at ?? null;

                $activities[] = [
                    'type' => 'job',
                    'icon' => ($jobOrder->status ?? '') === 'completed' ? 'fa-circle-check' : 'fa-clipboard-check',
                    'title' => $status === 'Completed' ? 'Job order completed' : 'Job order updated',
                    'description' => ($jobOrder->job_order_no ?? 'A job order') . ' is marked as ' . $status . '.',
                    'time' => $this->formatDateTime($rawTime),
                    'time_raw' => $rawTime,
                ];
            }
        }

        if (Schema::hasTable('payments')) {
            $query = DB::table('payments');

            if (Schema::hasColumn('payments', 'created_at')) {
                $query->orderByDesc('created_at');
            } else {
                $query->orderByDesc('id');
            }

            foreach ($query->limit(4)->get() as $payment) {
                $rawTime = $payment->payment_date ?? $payment->created_at ?? null;

                $activities[] = [
                    'type' => 'payment',
                    'icon' => 'fa-money-bill-wave',
                    'title' => 'Payment recorded',
                    'description' => ($payment->payment_no ?? 'Payment') . ' amounting to PHP ' . number_format((float) ($payment->amount ?? 0), 2) . '.',
                    'time' => $this->formatDateTime($rawTime),
                    'time_raw' => $rawTime,
                ];
            }
        }

        if (Schema::hasTable('support_conversations')) {
            $query = DB::table('support_conversations');

            if (Schema::hasColumn('support_conversations', 'updated_at')) {
                $query->orderByDesc('updated_at');
            } elseif (Schema::hasColumn('support_conversations', 'created_at')) {
                $query->orderByDesc('created_at');
            } else {
                $query->orderByDesc('id');
            }

            foreach ($query->limit(4)->get() as $conversation) {
                $queue = strtoupper($conversation->current_queue ?? $conversation->routed_to ?? 'support');
                $rawTime = $conversation->routed_at ?? $conversation->updated_at ?? $conversation->created_at ?? null;

                $activities[] = [
                    'type' => 'support',
                    'icon' => 'fa-comments',
                    'title' => 'Support concern routed',
                    'description' => 'A client concern was routed to ' . $queue . '.',
                    'time' => $this->formatDateTime($rawTime),
                    'time_raw' => $rawTime,
                ];
            }
        }

        usort($activities, function ($a, $b) {
            return strtotime($b['time_raw'] ?? '1970-01-01') <=> strtotime($a['time_raw'] ?? '1970-01-01');
        });

        return array_slice($activities, 0, 8);
    }

    private function formatDateTime($value): string
    {
        if (!$value) {
            return 'Not available';
        }

        return Carbon::parse($value)->format('M d, Y h:i A');
    }
}
