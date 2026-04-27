<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\QuotationRequest;
use App\Models\SupportConversation;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        return view('admin.dashboard', [
            'clientCount' => User::where('role', 'client')->count(),
            'workerCount' => User::where('role', 'worker')->count(),
            'pendingQuotationsCount' => QuotationRequest::where('status', 'pending')->count(),
            'assignedQuotationsCount' => QuotationRequest::where('status', 'assigned')->count(),
            'openSupportCount' => SupportConversation::whereIn('status', ['open', 'routed'])->count(),
        ]);
    }
}