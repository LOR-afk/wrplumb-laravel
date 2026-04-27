<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InspectorAvailability;
use Illuminate\Http\Request;

class InspectorAvailabilityController extends Controller
{
    public function index(Request $request)
    {
        $query = InspectorAvailability::with('inspector')
            ->whereHas('inspector', function ($q) {
                $q->where('role', 'worker');
            });

        if ($request->filled('availability_date')) {
            $query->whereDate('availability_date', $request->availability_date);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $availabilities = $query
            ->orderBy('availability_date')
            ->paginate(15)
            ->withQueryString();

        return view('admin.inspectors.availability', compact('availabilities'));
    }
}