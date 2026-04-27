<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\JobOrder;
use Illuminate\Support\Facades\Auth;

class JobOrderController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $jobOrders = JobOrder::with(['quotationRequest', 'worker', 'creator'])
            ->whereHas('quotationRequest', function ($query) use ($user) {
                $query->where('email', $user->email);
            })
            ->latest()
            ->paginate(10);

        return view('client.job-orders.index', compact('jobOrders'));
    }

    public function show(JobOrder $jobOrder)
    {
        $user = Auth::user();

        $jobOrder->load(['quotationRequest', 'worker', 'creator']);

        abort_if($jobOrder->quotationRequest->email !== $user->email, 403);

        return view('client.job-orders.show', compact('jobOrder'));
    }
}