<?php

namespace App\Http\Controllers\Inspector;

use App\Http\Controllers\Controller;
use App\Models\InspectorAvailability;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AvailabilityController extends Controller
{
    public function index()
    {
        $availabilities = InspectorAvailability::where('inspector_id', Auth::id())
            ->orderByDesc('availability_date')
            ->paginate(10);

        return view('inspector.availability.index', compact('availabilities'));
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

        InspectorAvailability::create([
            'inspector_id' => Auth::id(),
            'availability_date' => $validated['availability_date'],
            'start_time' => $validated['start_time'] ?? null,
            'end_time' => $validated['end_time'] ?? null,
            'status' => $validated['status'],
            'notes' => $validated['notes'] ?? null,
        ]);

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
}