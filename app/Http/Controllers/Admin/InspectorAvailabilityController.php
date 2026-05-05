<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InspectorAvailability;
use Carbon\Carbon;
use Illuminate\Http\Request;

class InspectorAvailabilityController extends Controller
{
    public function index(Request $request)
    {
        $calendarMonth = $request->input('month');

        if (!$calendarMonth && $request->filled('availability_date')) {
            $calendarMonth = Carbon::parse($request->availability_date)->format('Y-m');
        }

        if (!$calendarMonth) {
            $calendarMonth = now()->format('Y-m');
        }

        try {
            $monthStart = Carbon::createFromFormat('Y-m', $calendarMonth)->startOfMonth();
        } catch (\Exception $exception) {
            $monthStart = now()->startOfMonth();
            $calendarMonth = $monthStart->format('Y-m');
        }

        $monthEnd = $monthStart->copy()->endOfMonth();

        $query = InspectorAvailability::with('inspector')
            ->whereHas('inspector', function ($q) {
                $q->where('role', 'worker');
            })
            ->whereBetween('availability_date', [
                $monthStart->toDateString(),
                $monthEnd->toDateString(),
            ]);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $availabilities = $query
            ->orderBy('availability_date')
            ->orderBy('start_time')
            ->get();

        $inspectorGroups = $availabilities
            ->groupBy('inspector_id')
            ->map(function ($records) {
                $firstRecord = $records->first();
                $inspector = $firstRecord->inspector;

                $sortedRecords = $records->sortBy('availability_date')->values();

                return [
                    'id' => $inspector->id,
                    'name' => $inspector->name ?? trim(($inspector->first_name ?? '') . ' ' . ($inspector->last_name ?? '')) ?: 'Unnamed Inspector',
                    'email' => $inspector->email ?? null,
                    'available_count' => $records->where('status', 'available')->count(),
                    'on_duty_count' => $records->where('status', 'on_duty')->count(),
                    'off_duty_count' => $records->where('status', 'off_duty')->count(),
                    'on_leave_count' => $records->where('status', 'on_leave')->count(),
                    'record_count' => $records->count(),
                    'latest_date' => optional($records->sortByDesc('availability_date')->first()->availability_date)->format('Y-m-d'),
                    'records' => $sortedRecords->map(function ($record) {
                        return [
                            'id' => $record->id,
                            'date' => optional($record->availability_date)->format('Y-m-d'),
                            'status' => $record->status,
                            'start_time' => $record->start_time,
                            'end_time' => $record->end_time,
                            'notes' => $record->notes,
                        ];
                    })->values(),
                ];
            })
            ->sortBy('name')
            ->values();

        return view('admin.inspectors.availability', compact(
            'availabilities',
            'inspectorGroups',
            'calendarMonth',
            'monthStart',
            'monthEnd'
        ));
    }
}
