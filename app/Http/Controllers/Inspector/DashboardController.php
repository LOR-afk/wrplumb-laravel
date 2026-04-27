<?php

namespace App\Http\Controllers\Inspector;

use App\Http\Controllers\Controller;
use App\Models\QuotationRequest;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $userId = Auth::id();

        return view('inspector.dashboard', [
            'assignedCount' => QuotationRequest::where('worker_id', $userId)->where('status', 'assigned')->count(),
            'inProgressCount' => QuotationRequest::where('worker_id', $userId)->where('status', 'in_progress')->count(),
            'completedCount' => QuotationRequest::where('worker_id', $userId)->where('status', 'completed')->count(),
        ]);
    }
}