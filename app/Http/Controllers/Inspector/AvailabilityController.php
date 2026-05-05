<?php

namespace App\Http\Controllers\Inspector;

use App\Http\Controllers\Controller;
use App\Models\InspectorAvailability;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AvailabilityController extends Controller
{
    public function index(Request $request)
    {
        $selectedMonth = $request->input('month', now()->format('Y-m'));

        try {
            $monthStart = Carbon::createFromFormat('Y-m-d', $selectedMonth . '-01')->startOfMonth();
        } catch (\Throwable $exception) {
            $monthStart = now()->startOfMonth();
            $selectedMonth = $monthStart->format('Y-m');
        }

        $monthEnd = $monthStart->copy()->endOfMonth();

        $monthlyAvailabilities = InspectorAvailability::where('inspector_id', Auth::id())
            ->whereBetween('availability_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->orderBy('availability_date')
            ->get();

        $recordsByDate = $monthlyAvailabilities->keyBy(function ($availability) {
            return $availability->availability_date->format('Y-m-d');
        });

        $calendarDays = [];
        $calendarCursor = $monthStart->copy()->startOfWeek(Carbon::SUNDAY);
        $calendarEnd = $monthEnd->copy()->endOfWeek(Carbon::SATURDAY);

        while ($calendarCursor->lte($calendarEnd)) {
            $dateKey = $calendarCursor->format('Y-m-d');
            $record = $recordsByDate->get($dateKey);

            $calendarDays[] = [
                'date_key' => $dateKey,
                'day' => $calendarCursor->day,
                'is_current_month' => $calendarCursor->month === $monthStart->month,
                'is_today' => $calendarCursor->isToday(),
                'record' => $record ? [
                    'status' => $record->status,
                    'status_label' => $this->formatStatusLabel($record->status),
                    'start_time' => $record->start_time,
                    'end_time' => $record->end_time,
                    'time_display' => $this->formatTimeRange($record->start_time, $record->end_time),
                    'notes' => $record->notes,
                ] : null,
            ];

            $calendarCursor->addDay();
        }

        $calendarRecords = $recordsByDate->map(function ($availability) {
            return [
                'date' => $availability->availability_date->format('M d, Y'),
                'status' => $availability->status,
                'status_label' => $this->formatStatusLabel($availability->status),
                'time_display' => $this->formatTimeRange($availability->start_time, $availability->end_time),
                'notes' => $availability->notes ?: 'No notes provided.',
            ];
        });

        $monthlySummary = [
            'available' => $monthlyAvailabilities->where('status', 'available')->count(),
            'on_duty' => $monthlyAvailabilities->where('status', 'on_duty')->count(),
            'off_duty' => $monthlyAvailabilities->where('status', 'off_duty')->count(),
            'on_leave' => $monthlyAvailabilities->where('status', 'on_leave')->count(),
            'total' => $monthlyAvailabilities->count(),
        ];

        $previousMonth = $monthStart->copy()->subMonth()->format('Y-m');
        $nextMonth = $monthStart->copy()->addMonth()->format('Y-m');

        $availabilities = InspectorAvailability::where('inspector_id', Auth::id())
            ->orderByDesc('availability_date')
            ->paginate(10)
            ->withQueryString();

        return view('inspector.availability.index', compact(
            'availabilities',
            'calendarDays',
            'calendarRecords',
            'monthlySummary',
            'selectedMonth',
            'monthStart',
            'previousMonth',
            'nextMonth'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'availability_date' => ['required', 'date'],
            'start_time' => ['nullable'],
            'end_time' => ['nullable'],
            'status' => ['required', 'in:available,on_duty,off_duty,on_leave'],
            'notes' => ['nullable', 'string'],
        ]);

        InspectorAvailability::updateOrCreate(
            [
                'inspector_id' => Auth::id(),
                'availability_date' => $validated['availability_date'],
            ],
            [
                'start_time' => $validated['start_time'] ?? null,
                'end_time' => $validated['end_time'] ?? null,
                'status' => $validated['status'],
                'notes' => $validated['notes'] ?? null,
            ]
        );

        return back()->with('success', 'Availability schedule saved successfully.');
    }

    public function update(Request $request, InspectorAvailability $availability)
    {
        abort_if($availability->inspector_id !== Auth::id(), 403);

        $validated = $request->validate([
            'availability_date' => ['required', 'date'],
            'start_time' => ['nullable'],
            'end_time' => ['nullable'],
            'status' => ['required', 'in:available,on_duty,off_duty,on_leave'],
            'notes' => ['nullable', 'string'],
        ]);

        $availability->update($validated);

        return back()->with('success', 'Availability updated successfully.');
    }

    public function destroy(InspectorAvailability $availability)
    {
        abort_if($availability->inspector_id !== Auth::id(), 403);

        $availability->delete();

        return back()->with('success', 'Availability deleted successfully.');
    }

    private function formatStatusLabel(?string $status): string
    {
        return match ($status) {
            'available' => 'Available',
            'on_duty' => 'On Duty / Assigned',
            'off_duty' => 'Off Duty',
            'on_leave' => 'On Leave',
            default => 'No Record',
        };
    }

    private function formatTimeRange(?string $startTime, ?string $endTime): string
    {
        if (!$startTime && !$endTime) {
            return 'Time not specified';
        }

        return ($startTime ?: '—') . ' - ' . ($endTime ?: '—');
    }
}
