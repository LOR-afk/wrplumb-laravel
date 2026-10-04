<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Quotation;
use Illuminate\Http\Request;

class HrQuotationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        if (!$user || $user->role !== 'hr') {
            return response()->json([
                'message' => 'Unauthorized.',
            ], 403);
        }

        $baseQuery = Quotation::query()
            ->whereNull('archived_at')
            ->whereHas('request', function ($query) {
                $query->whereNull('archived_at');
            });

        $query = Quotation::query()
            ->with([
                'request',
                'preparedBy',
                'invoice',
            ])
            ->withCount('items')
            ->whereNull('archived_at')
            ->whereHas('request', function ($query) {
                $query->whereNull('archived_at');
            });

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = trim($request->search);

            $query->where(function ($query) use ($search) {
                $query
                    ->where('quotation_no', 'like', "%{$search}%")
                    ->orWhereHas('request', function ($requestQuery) use ($search) {
                        $requestQuery
                            ->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%")
                            ->orWhere('service_type', 'like', "%{$search}%")
                            ->orWhere('service_category', 'like', "%{$search}%");
                    });
            });
        }

        $summary = [
            'total' => (clone $baseQuery)->count(),
            'draft' => (clone $baseQuery)
                ->where('status', 'draft')
                ->count(),
            'sent' => (clone $baseQuery)
                ->where('status', 'sent')
                ->count(),
            'accepted' => (clone $baseQuery)
                ->where('status', 'accepted')
                ->count(),
        ];

        $quotations = $query
            ->latest()
            ->paginate(10);

        return response()->json([
            'summary' => $summary,
            'quotations' => $quotations,
        ]);
    }
}