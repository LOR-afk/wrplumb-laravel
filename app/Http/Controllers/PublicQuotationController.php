<?php
namespace App\Http\Controllers;
use App\Models\QuotationRequest;
use App\Models\InspectorAvailability;
use App\Services\AlertService;
use App\Services\WorkflowEmailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;
class PublicQuotationController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'middle_initial' => ['nullable', 'string', 'size:1'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email:rfc,dns', 'max:255'],
            'phone' => ['required', 'regex:/^09[0-9]{9}$/', 'max:11'],
            'service_category' => ['required', 'string', 'max:100'],
            'service_type' => ['required', 'string', 'max:100'],
            'project_type' => ['required', 'string', 'max:100'],
            'preferred_date' => ['nullable', 'date', 'after_or_equal:today'],
            'address' => ['required', 'string', 'max:255'],
            'address_lat' => ['nullable', 'numeric', 'between:-90,90'],
            'address_lon' => ['nullable', 'numeric', 'between:-180,180'],
            'details' => ['required', 'string', 'min:20', 'max:2000'],
            'consent' => ['accepted'],
        ], [
            'preferred_date.after_or_equal' => 'Preferred service date cannot be in the past.',
            'details.min' => 'Please provide at least 20 characters describing the problem or work needed.',
            'consent.accepted' => 'Please accept the service terms and privacy policy before submitting.',
        ]);
        $validated['email'] = strtolower(trim($validated['email']));
        $validated['phone'] = preg_replace('/\s+/', '', $validated['phone']);
        $validated['first_name'] = trim($validated['first_name']);
        $validated['last_name'] = trim($validated['last_name']);
        $validated['address'] = trim($validated['address']);
        $validated['details'] = trim($validated['details']);
        $duplicateExists = QuotationRequest::query()
            ->where('email', $validated['email'])
            ->where('phone', $validated['phone'])
            ->where('service_type', $validated['service_type'])
            ->where('address', $validated['address'])
            ->where('created_at', '>=', now()->subMinutes(2))
            ->exists();
        if ($duplicateExists) {
            return back()
                ->withInput()
                ->withErrors([
                    'request' => 'This service request appears to have already been submitted. Please wait before sending it again.',
                ]);
        }
        unset($validated['consent']);
        $quotation = DB::transaction(function () use ($validated) {
            if (!empty($validated['preferred_date'])) {
                $date = Carbon::parse($validated['preferred_date'])->toDateString();
                InspectorAvailability::query()
                    ->whereDate('availability_date', $date)
                    ->lockForUpdate()
                    ->get(['id']);
                $capacity = $this->bookingCapacityForDate($date);
                if ($capacity['remaining_slots'] < 1) {
                    throw ValidationException::withMessages([
                        'preferred_date' => 'The selected date is already full. Please choose another available date.',
                    ]);
                }
            }
            return QuotationRequest::create($validated);
        });
        AlertService::sendToRole(
            'admin',
            'New service request',
            "{$quotation->full_name} submitted a new service request.",
            route('admin.quotations.index'),
            'info'
        );
        WorkflowEmailService::newServiceRequest($quotation);
        return back()->with('quotation_success', 'Service request submitted successfully.');
    }
    public function availabilityCalendar(Request $request)
    {
        $validated = $request->validate([
            'month' => ['required', 'date_format:Y-m'],
        ]);
        $monthStart = Carbon::createFromFormat('Y-m', $validated['month'])->startOfMonth();
        $monthEnd = $monthStart->copy()->endOfMonth();
        $datesWithAvailability = InspectorAvailability::query()
            ->whereBetween('availability_date', [
                $monthStart->toDateString(),
                $monthEnd->toDateString(),
            ])
            ->distinct()
            ->orderBy('availability_date')
            ->pluck('availability_date')
            ->map(fn ($date) => Carbon::parse($date)->toDateString())
            ->unique()
            ->values();
        $availability = $datesWithAvailability->mapWithKeys(function ($date) {
            return [$date => $this->bookingCapacityForDate($date)];
        });
        return response()->json([
            'success' => true,
            'month' => $validated['month'],
            'reserve_target' => $this->reserveInspectorTarget(),
            'availability' => $availability,
        ]);
    }
    private function bookingCapacityForDate(string $date): array
    {
        $availableInspectors = InspectorAvailability::query()
            ->join('users', 'users.id', '=', 'inspector_availabilities.inspector_id')
            ->whereDate('inspector_availabilities.availability_date', $date)
            ->where('inspector_availabilities.status', 'available')
            ->whereIn('users.role', ['inspector', 'worker'])
            ->where('users.is_active', true)
            ->distinct('inspector_availabilities.inspector_id')
            ->count('inspector_availabilities.inspector_id');
        $reserveTarget = $this->reserveInspectorTarget();
        $reservedInspectors = min($reserveTarget, max($availableInspectors - 1, 0));
        $bookingCapacity = max($availableInspectors - $reservedInspectors, 0);
        $bookedRequests = QuotationRequest::query()
            ->whereNull('archived_at')
            ->where(function ($query) use ($date) {
                $query
                    ->where(function ($pending) use ($date) {
                        $pending->whereDate('preferred_date', $date)
                            ->where(function ($status) {
                                $status->whereNull('appointment_status')
                                    ->orWhere('appointment_status', 'pending');
                            });
                    })
                    ->orWhere(function ($scheduled) use ($date) {
                        $scheduled->whereDate('appointment_date', $date)
                            ->whereIn('appointment_status', ['approved', 'rescheduled']);
                    });
            })
            ->count();
        $remainingSlots = max($bookingCapacity - $bookedRequests, 0);
        return [
            'available_inspectors' => $availableInspectors,
            'reserved_inspectors' => $reservedInspectors,
            'booking_capacity' => $bookingCapacity,
            'booked_requests' => $bookedRequests,
            'remaining_slots' => $remainingSlots,
            'full' => $bookingCapacity < 1 || $remainingSlots < 1,
        ];
    }
    private function reserveInspectorTarget(): int
    {
        return max((int) config('wrplumb.booking.reserve_inspectors', 5), 0);
    }
}
