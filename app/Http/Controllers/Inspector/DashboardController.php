<?php

namespace App\Http\Controllers\Inspector;

use App\Http\Controllers\Controller;
use App\Models\QuotationRequest;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $userId = Auth::id();

        $baseQuery = QuotationRequest::query()
            ->where('worker_id', $userId);

        $assignedCount = (clone $baseQuery)
            ->where('status', 'assigned')
            ->count();

        $inProgressCount = (clone $baseQuery)
            ->where('status', 'in_progress')
            ->count();

        $completedCount = (clone $baseQuery)
            ->where('status', 'completed')
            ->count();

        $todayVisits = (clone $baseQuery)
            ->whereDate('appointment_date', today())
            ->whereIn('appointment_status', ['approved', 'rescheduled'])
            ->orderBy('appointment_time')
            ->take(6)
            ->get();

        $todayScheduleCount = $todayVisits->count();

        $recentActivities = (clone $baseQuery)
            ->latest('updated_at')
            ->take(5)
            ->get();

        $hasActiveWork = $inProgressCount > 0 || $todayScheduleCount > 0;

        $availabilityStatus = $hasActiveWork
            ? 'On Duty'
            : 'Available';

        $availabilityMessage = $hasActiveWork
            ? 'You currently have scheduled or active field work.'
            : 'You are available for new assignments today.';

        return view('inspector.dashboard', compact(
            'assignedCount',
            'inProgressCount',
            'completedCount',
            'todayVisits',
            'todayScheduleCount',
            'recentActivities',
            'availabilityStatus',
            'availabilityMessage'
        ));
    }
}