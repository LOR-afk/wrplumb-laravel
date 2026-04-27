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
            'email' => ['required', 'email'],
            'phone' => ['required', 'string', 'max:30'],
            'service_category' => ['required', 'string'],
            'service_type' => ['required', 'string'],
            'project_type' => ['required', 'string'],
            'preferred_date' => ['nullable', 'date'],
            'address' => ['required', 'string'],
            'details' => ['required', 'string'],
        ]);

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