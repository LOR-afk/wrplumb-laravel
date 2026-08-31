<?php

namespace App\Http\Controllers;

use App\Models\QuotationRequest;
use Illuminate\Http\Request;
use App\Services\AlertService;

class PublicQuotationController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'middle_initial' => ['nullable', 'string', 'size:1'],
            'last_name' => ['required', 'string', 'max:100'],

            // ✅ STRICT EMAIL VALIDATION
            'email' => ['required', 'email:rfc,dns', 'max:255'],

            // ✅ PH PHONE VALIDATION (11 digits starting 09)
            'phone' => [
                'required',
                'regex:/^09[0-9]{9}$/',
                'max:11'
            ],

            'service_category' => ['required', 'string', 'max:100'],
            'service_type' => ['required', 'string', 'max:100'],
            'project_type' => ['required', 'string', 'max:100'],

            'preferred_date' => ['nullable', 'date'],
            'address' => ['required', 'string', 'max:255'],
            'details' => ['required', 'string', 'max:2000'],
        ]);

        // 🔥 Normalize input (important for clean DB)
        $validated['email'] = strtolower(trim($validated['email']));
        $validated['phone'] = preg_replace('/\s+/', '', $validated['phone']);

        $quotation = QuotationRequest::create($validated);

        AlertService::sendToRole(
            'admin',
            'New quotation request',
            "{$quotation->full_name} submitted a new quotation request.",
            route('admin.quotations.index'),
            'info'
        );

        return back()->with('success', 'Free quotation request submitted successfully.');
    }
}