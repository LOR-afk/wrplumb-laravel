<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\JobOrder;
use App\Models\QuotationRequest;
use App\Models\SupportConversation;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
/** @var \App\Models\User $user */
        $user = Auth::user();
        $conversation = SupportConversation::where('client_id', $user->id)
            ->whereIn('status', ['open', 'routed', 'escalated'])
            ->latest()
            ->first();

        if (!$conversation) {
            $conversation = SupportConversation::create([
                'client_id' => $user->id,
                'status' => 'open',
                'current_queue' => 'bot',
            ]);
        }

        $conversation->load('messages');

        $clientRequestsQuery = QuotationRequest::query()
            ->where('email', $user->email);

        $activeRequests = (clone $clientRequestsQuery)
            ->whereNotIn('status', ['completed', 'cancelled', 'rejected', 'declined'])
            ->count();

        $ongoingRequests = (clone $clientRequestsQuery)
            ->whereNotIn('status', ['completed', 'cancelled', 'rejected', 'declined'])
            ->latest()
            ->take(3)
            ->get();

        $upcomingAppointments = (clone $clientRequestsQuery)
            ->whereIn('appointment_status', ['approved', 'rescheduled'])
            ->whereNotNull('appointment_date')
            ->whereDate('appointment_date', '>=', today())
            ->count();

        $completedJobs = JobOrder::query()
            ->whereHas('quotationRequest', function ($query) use ($user) {
                $query->where('email', $user->email);
            })
            ->where('status', 'completed')
            ->count();

        $invoiceQuery = Invoice::query()
            ->whereHas('quotation.request', function ($query) use ($user) {
                $query->where('email', $user->email);
            });

        $openInvoices = (clone $invoiceQuery)
            ->whereNotIn('status', ['paid'])
            ->count();

        $recentInvoices = (clone $invoiceQuery)
            ->with('quotation.request')
            ->latest('invoice_date')
            ->take(4)
            ->get();

        $dueInvoice = (clone $invoiceQuery)
            ->whereNotIn('status', ['paid'])
            ->whereNotNull('due_date')
            ->whereDate('due_date', '>=', today())
            ->orderBy('due_date')
            ->first();

        $recentActivities = $user->alerts()
            ->latest()
            ->take(5)
            ->get();

        return view('client.dashboard', [
            'user' => $user,
            'conversation' => $conversation,
            'activeRequests' => $activeRequests,
            'upcomingAppointments' => $upcomingAppointments,
            'openInvoices' => $openInvoices,
            'completedJobs' => $completedJobs,
            'ongoingRequests' => $ongoingRequests,
            'recentInvoices' => $recentInvoices,
            'recentActivities' => $recentActivities,
            'dueInvoice' => $dueInvoice,
        ]);
    }
}
