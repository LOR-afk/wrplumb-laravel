<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use App\Models\SupportConversation;

class DashboardController extends Controller
{
    public function index()
    {
        return view('hr.dashboard', [
            'openCount' => SupportConversation::where('current_queue', 'hr')->count(),
            'routedCount' => SupportConversation::where('current_queue', 'hr')
                ->where('status', 'routed')
                ->count(),
            'resolvedCount' => SupportConversation::where('status', 'resolved')->count(),
        ]);
    }
}