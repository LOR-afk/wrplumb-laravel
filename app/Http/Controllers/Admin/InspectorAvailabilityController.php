<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InspectorAvailability;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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
        $statusFilter = $request->input('status');

$inspectors = User::query()
    ->whereIn('role', ['inspector', 'worker'])
    ->where('is_active', 1)
    ->orderByRaw("COALESCE(NULLIF(name, ''), CONCAT(COALESCE(first_name, ''), ' ', COALESCE(last_name, '')))")
    ->get();

        $query = InspectorAvailability::with('inspector')
            ->whereIn('inspector_id', $inspectors->pluck('id'))
            ->whereBetween('availability_date', [
                $monthStart->toDateString(),
                $monthEnd->toDateString(),
            ]);

        if ($statusFilter) {
            $query->where('status', $statusFilter);
        }

        $availabilities = $query
            ->orderBy('availability_date')
            ->orderBy('start_time')
            ->get();

        $recordsByInspector = $availabilities->groupBy('inspector_id');

        $inspectorGroups = $inspectors
            ->map(function ($inspector) use ($recordsByInspector) {
                $records = ($recordsByInspector[$inspector->id] ?? collect())
                    ->sortBy('availability_date')
                    ->values();

                $name = $inspector->name
                    ?? trim(($inspector->first_name ?? '') . ' ' . ($inspector->last_name ?? ''))
                    ?: 'Unnamed Inspector';

                return [
                    'id' => $inspector->id,
                    'name' => $name,
                    'email' => $inspector->email ?? null,
                    'available_count' => $records->where('status', 'available')->count(),
                    'on_duty_count' => $records->where('status', 'on_duty')->count(),
                    'off_duty_count' => $records->where('status', 'off_duty')->count(),
                    'on_leave_count' => $records->where('status', 'on_leave')->count(),
                    'off_leave_count' => $records->whereIn('status', ['off_duty', 'on_leave'])->count(),
                    'record_count' => $records->count(),
                    'latest_date' => optional($records->sortByDesc('availability_date')->first()?->availability_date)->format('Y-m-d'),
                    'records' => $records->map(fn ($record) => $this->availabilityPayload($record))->values(),
                ];
            })
            ->filter(fn ($inspector) => !$statusFilter || $inspector['record_count'] > 0)
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

    public function storeOrUpdateDay(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'inspector_id' => ['required', 'integer', 'exists:users,id'],
            'availability_date' => ['required', 'date'],
            'status' => ['required', Rule::in(['available', 'on_duty', 'off_duty', 'on_leave'])],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $availability = InspectorAvailability::updateOrCreate(
            [
                'inspector_id' => $validated['inspector_id'],
                'availability_date' => Carbon::parse($validated['availability_date'])->toDateString(),
            ],
            [
                'status' => $validated['status'],
                'start_time' => $validated['start_time'] ?: null,
                'end_time' => $validated['end_time'] ?: null,
                'notes' => $validated['notes'] ?: null,
            ]
        );

        $availability->load('inspector');

        return response()->json([
            'message' => 'Availability record saved successfully.',
            'record' => $this->availabilityPayload($availability),
        ]);
    }

    protected function availabilityPayload(InspectorAvailability $record): array
    {
        return [
            'id' => $record->id,
            'date' => $record->availability_date ? Carbon::parse($record->availability_date)->format('Y-m-d') : null,
            'status' => $record->status,
            'start_time' => $this->timeForInput($record->start_time),
            'end_time' => $this->timeForInput($record->end_time),
            'start_time_display' => $this->timeForDisplay($record->start_time),
            'end_time_display' => $this->timeForDisplay($record->end_time),
            'notes' => $record->notes,
            'updated_at' => optional($record->updated_at)->format('Y-m-d h:i A'),
        ];
    }

    protected function timeForInput($time): ?string
    {
        if (!$time) {
            return null;
        }

        try {
            return Carbon::parse($time)->format('H:i');
        } catch (\Exception $exception) {
            return (string) $time;
        }
    }

    protected function timeForDisplay($time): ?string
    {
        if (!$time) {
            return null;
        }

        try {
            return Carbon::parse($time)->format('h:i A');
        } catch (\Exception $exception) {
            return (string) $time;
        }
    }
}
