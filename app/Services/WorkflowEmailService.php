<?php

namespace App\Services;

use App\Models\Contract;
use App\Models\InspectionReport;
use App\Models\Invoice;
use App\Models\LeaveRequest;
use App\Models\Payment;
use App\Models\Quotation;
use App\Models\QuotationRequest;
use App\Models\Receipt;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use Throwable;

class WorkflowEmailService
{
    public static function newServiceRequest(QuotationRequest $quotation): void
    {
        self::sendToRole(
            'admin',
            'New Service Request - WRPlumb',
            'New Service Request',
            "{$quotation->full_name} submitted a new service request.",
            self::routeIfExists('admin.quotations.index'),
            'Review Request',
            [
                'Client' => $quotation->full_name ?: 'Client',
                'Service' => $quotation->service_type ?: 'Not specified',
                'Project Type' => $quotation->project_type ?: 'Not specified',
                'Preferred Date' => self::dateValue($quotation->preferred_date),
                'Address' => $quotation->address ?: 'Not specified',
            ]
        );
    }

    public static function assignment(User $worker, QuotationRequest $quotation): void
    {
        $isDirect = $quotation->service_flow === 'direct_service';
        $type = $isDirect ? 'Service' : 'Inspection';

        self::sendToUser(
            $worker,
            "New {$type} Assignment - WRPlumb",
            "New {$type} Assignment",
            'A new service request has been assigned to you.',
            self::routeIfExists('inspector.quotations.show', $quotation),
            'View Assignment',
            [
                'Client' => $quotation->full_name ?: 'Client',
                'Service' => $quotation->service_type ?: 'Not specified',
                'Project Type' => $quotation->project_type ?: 'Not specified',
                'Preferred Date' => self::dateValue($quotation->preferred_date),
                'Preferred Time' => self::timeValue($quotation->preferred_time),
                'Address' => $quotation->address ?: 'Not specified',
            ]
        );
    }

    public static function scheduleUpdated(QuotationRequest $quotation, string $status): void
    {
        if (!in_array($status, ['approved', 'rescheduled', 'cancelled'], true)) {
            return;
        }

        $quotation->loadMissing('worker');

        $type = $quotation->service_flow === 'direct_service' ? 'Service' : 'Inspection';
        $label = match ($status) {
            'approved' => 'Confirmed',
            'rescheduled' => 'Rescheduled',
            'cancelled' => 'Cancelled',
        };

        $client = self::clientUser($quotation->email);
        $clientMessage = match ($status) {
            'approved' => "Your {$type} schedule has been confirmed.",
            'rescheduled' => "Your {$type} schedule has been updated.",
            'cancelled' => "Your {$type} schedule has been cancelled.",
        };

        self::sendToAddress(
            $quotation->email,
            $quotation->full_name ?: 'Client',
            "{$type} Schedule {$label} - WRPlumb",
            "{$type} Schedule {$label}",
            $clientMessage,
            $client ? self::routeIfExists('client.requests.show', $quotation) : null,
            $client ? 'View Request' : null,
            [
                'Service' => $quotation->service_type ?: 'Not specified',
                'Schedule Date' => self::dateValue($quotation->appointment_date),
                'Schedule Time' => self::timeValue($quotation->appointment_time),
                'Assigned Personnel' => $quotation->worker?->name ?: 'Not specified',
                'Address' => $quotation->address ?: 'Not specified',
                'Cancellation Reason' => $status === 'cancelled'
                    ? ($quotation->cancel_reason ?: 'Not specified')
                    : 'N/A',
            ]
        );

        if ($quotation->worker) {
            self::sendToUser(
                $quotation->worker,
                "{$type} Schedule {$label} - WRPlumb",
                "{$type} Schedule {$label}",
                match ($status) {
                    'approved' => "An assigned {$type} schedule has been confirmed.",
                    'rescheduled' => "An assigned {$type} schedule has been updated.",
                    'cancelled' => "An assigned {$type} schedule has been cancelled.",
                },
                self::routeIfExists('inspector.quotations.show', $quotation),
                'View Assignment',
                [
                    'Client' => $quotation->full_name ?: 'Client',
                    'Service' => $quotation->service_type ?: 'Not specified',
                    'Schedule Date' => self::dateValue($quotation->appointment_date),
                    'Schedule Time' => self::timeValue($quotation->appointment_time),
                    'Address' => $quotation->address ?: 'Not specified',
                ]
            );
        }
    }

    public static function clientActionRequested(QuotationRequest $quotation): void
    {
        $action = $quotation->client_action_request === 'cancel'
            ? 'Cancellation'
            : 'Reschedule';

        self::sendToRole(
            'admin',
            "Client {$action} Request - WRPlumb",
            "Client {$action} Request",
            "{$quotation->full_name} submitted a {$action} request.",
            self::routeIfExists('admin.quotations.index'),
            'Review Request',
            [
                'Client' => $quotation->full_name ?: 'Client',
                'Service' => $quotation->service_type ?: 'Not specified',
                'Current Date' => self::dateValue($quotation->appointment_date),
                'Current Time' => self::timeValue($quotation->appointment_time),
                'Requested Date' => $action === 'Reschedule'
                    ? self::dateValue($quotation->client_requested_date)
                    : 'N/A',
                'Requested Time' => $action === 'Reschedule'
                    ? self::timeValue($quotation->client_requested_time)
                    : 'N/A',
                'Reason' => $quotation->client_request_reason ?: 'Not specified',
            ]
        );
    }

    public static function clientRequestDeclined(QuotationRequest $quotation): void
    {
        $action = $quotation->client_action_request === 'cancel'
            ? 'cancellation'
            : 'reschedule';

        $client = self::clientUser($quotation->email);

        self::sendToAddress(
            $quotation->email,
            $quotation->full_name ?: 'Client',
            'Request Declined - WRPlumb',
            'Request Declined',
            "Your {$action} request was declined by Admin.",
            $client ? self::routeIfExists('client.requests.show', $quotation) : null,
            $client ? 'View Request' : null,
            [
                'Service' => $quotation->service_type ?: 'Not specified',
                'Review Notes' => $quotation->client_request_review_notes ?: 'No review notes provided',
            ]
        );
    }

    public static function quotationReadyForHr(QuotationRequest $quotation): void
    {
        self::sendToRole(
            'hr',
            'Request Ready for Quotation - WRPlumb',
            'Request Ready for Quotation',
            "{$quotation->full_name}'s request is ready for quotation preparation.",
            self::routeIfExists('hr.quotations.create', $quotation),
            'Prepare Quotation',
            [
                'Client' => $quotation->full_name ?: 'Client',
                'Service' => $quotation->service_type ?: 'Not specified',
                'Project Type' => $quotation->project_type ?: 'Not specified',
                'Handoff Notes' => $quotation->quotation_handoff_notes ?: 'None',
            ]
        );
    }

    public static function quotationResponded(Quotation $quotation, string $decision): void
    {
        $quotation->loadMissing('request');
        $accepted = $decision === 'accepted';
        $label = $accepted ? 'Accepted' : 'Declined';
        $message = "{$quotation->request?->full_name} {$decision} quotation {$quotation->quotation_no}.";

        foreach (['hr', 'admin'] as $role) {
            self::sendToRole(
                $role,
                "Quotation {$label} - WRPlumb",
                "Quotation {$label}",
                $message,
                $role === 'hr'
                    ? self::routeIfExists('hr.quotations.show', $quotation)
                    : self::routeIfExists('admin.quotations.index'),
                $role === 'hr' ? 'View Quotation' : 'View Requests',
                [
                    'Client' => $quotation->request?->full_name ?: 'Client',
                    'Quotation' => $quotation->quotation_no ?: "#{$quotation->id}",
                    'Service' => $quotation->request?->service_type ?: 'Not specified',
                    'Total' => self::money($quotation->grand_total),
                    'Payment Plan' => $accepted
                        ? ($quotation->payment_plan ?: 'Not specified')
                        : 'N/A',
                ]
            );
        }
    }

    public static function inspectionReportSubmitted(
        QuotationRequest $quotation,
        InspectionReport $report,
        User $inspector
    ): void {
        foreach (['hr', 'admin'] as $role) {
            self::sendToRole(
                $role,
                'Inspection Report Submitted - WRPlumb',
                'Inspection Report Submitted',
                "{$inspector->name} submitted inspection report {$report->report_no}.",
                $role === 'hr'
                    ? self::routeIfExists('hr.inspection-reports.show', $report)
                    : self::routeIfExists('admin.quotations.index'),
                $role === 'hr' ? 'Review Report' : 'View Request',
                [
                    'Inspector' => $inspector->name ?: 'Inspector',
                    'Report No.' => $report->report_no ?: "#{$report->id}",
                    'Client' => $quotation->full_name ?: 'Client',
                    'Service' => $quotation->service_type ?: 'Not specified',
                ]
            );
        }

        $client = self::clientUser($quotation->email);

        self::sendToAddress(
            $quotation->email,
            $quotation->full_name ?: 'Client',
            'Site Inspection Completed - WRPlumb',
            'Site Inspection Completed',
            'The site inspection for your service request has been completed.',
            $client ? self::routeIfExists('client.requests.show', $quotation) : null,
            $client ? 'View Request' : null,
            [
                'Service' => $quotation->service_type ?: 'Not specified',
                'Inspector' => $inspector->name ?: 'Inspector',
                'Report No.' => $report->report_no ?: "#{$report->id}",
            ]
        );
    }

    public static function requestStatusChanged(
        QuotationRequest $quotation,
        string $status,
        User $worker
    ): void {
        if (!in_array($status, ['in_progress', 'completed'], true)) {
            return;
        }

        $client = self::clientUser($quotation->email);
        $completed = $status === 'completed';

        self::sendToAddress(
            $quotation->email,
            $quotation->full_name ?: 'Client',
            $completed ? 'Service Request Completed - WRPlumb' : 'Service Request In Progress - WRPlumb',
            $completed ? 'Service Request Completed' : 'Service Request In Progress',
            $completed
                ? 'Your service request has been completed.'
                : 'Your service request is now in progress.',
            $client ? self::routeIfExists('client.requests.show', $quotation) : null,
            $client ? 'View Request' : null,
            [
                'Service' => $quotation->service_type ?: 'Not specified',
                'Assigned Personnel' => $worker->name ?: 'Not specified',
            ]
        );

        if ($completed) {
            self::sendToRole(
                'admin',
                'Service Request Completed - WRPlumb',
                'Service Request Completed',
                "{$quotation->full_name}'s service request was completed by {$worker->name}.",
                self::routeIfExists('admin.quotations.index'),
                'View Request',
                [
                    'Client' => $quotation->full_name ?: 'Client',
                    'Service' => $quotation->service_type ?: 'Not specified',
                    'Assigned Personnel' => $worker->name ?: 'Not specified',
                ]
            );
        }
    }

    public static function leaveSubmitted(LeaveRequest $leaveRequest): void
    {
        $leaveRequest->loadMissing('inspector');

        self::sendToRole(
            'hr',
            'New Leave Request - WRPlumb',
            'New Leave Request',
            "{$leaveRequest->inspector?->name} submitted a leave request.",
            self::routeIfExists('hr.leave-requests.index'),
            'Review Leave Request',
            [
                'Inspector' => $leaveRequest->inspector?->name ?: 'Inspector',
                'Start Date' => self::dateValue($leaveRequest->start_date),
                'End Date' => self::dateValue($leaveRequest->end_date),
                'Reason' => $leaveRequest->reason ?: 'Not specified',
            ]
        );
    }

    public static function leaveReviewed(LeaveRequest $leaveRequest): void
    {
        $leaveRequest->loadMissing('inspector');
        if (!$leaveRequest->inspector) {
            return;
        }

        $approved = $leaveRequest->status === 'approved';

        self::sendToUser(
            $leaveRequest->inspector,
            $approved ? 'Leave Request Approved - WRPlumb' : 'Leave Request Declined - WRPlumb',
            $approved ? 'Leave Request Approved' : 'Leave Request Declined',
            $approved
                ? 'HR approved your leave request.'
                : 'HR declined your leave request.',
            self::routeIfExists('inspector.availability.index'),
            'View Availability',
            [
                'Start Date' => self::dateValue($leaveRequest->start_date),
                'End Date' => self::dateValue($leaveRequest->end_date),
                'Status' => ucfirst((string) $leaveRequest->status),
                'Review Notes' => $leaveRequest->review_notes ?: 'None',
            ]
        );
    }

    public static function invoiceCreated(Invoice $invoice): void
    {
        $invoice->loadMissing('quotation.request');
        $request = $invoice->quotation?->request;
        if (!$request?->email) {
            return;
        }

        $client = self::clientUser($request->email);

        self::sendToAddress(
            $request->email,
            $request->full_name ?: 'Client',
            "Invoice Ready - {$invoice->invoice_no}",
            'Invoice Ready',
            'A new invoice and payment schedule are ready for your service.',
            $client ? self::routeIfExists('client.invoices.show', $invoice) : null,
            $client ? 'View Invoice' : null,
            [
                'Invoice' => $invoice->invoice_no ?: "#{$invoice->id}",
                'Service' => $request->service_type ?: 'Not specified',
                'Total Amount' => self::money($invoice->total_amount),
                'Invoice Date' => self::dateValue($invoice->invoice_date),
                'Due Date' => self::dateValue($invoice->due_date),
            ]
        );
    }

    public static function paymentSubmitted(Payment $payment): void
    {
        $payment->loadMissing('invoice.quotation.request');
        $request = $payment->invoice?->quotation?->request;

        self::sendToRole(
            'hr',
            'Payment Submitted for Verification - WRPlumb',
            'Payment Submitted for Verification',
            "{$request?->full_name} submitted a payment for verification.",
            self::routeIfExists('hr.payments.show', $payment),
            'Review Payment',
            [
                'Client' => $request?->full_name ?: 'Client',
                'Payment No.' => $payment->payment_no ?: "#{$payment->id}",
                'Invoice' => $payment->invoice?->invoice_no ?: 'Not specified',
                'Amount' => self::money($payment->amount),
                'Method' => $payment->payment_method ?: 'Not specified',
                'Reference' => $payment->reference_number ?: 'Not specified',
            ]
        );
    }

    public static function paymentReviewed(Payment $payment): void
    {
        $payment->loadMissing('invoice.quotation.request');
        $request = $payment->invoice?->quotation?->request;
        if (!$request?->email) {
            return;
        }

        $confirmed = $payment->status === 'confirmed';
        $client = self::clientUser($request->email);

        self::sendToAddress(
            $request->email,
            $request->full_name ?: 'Client',
            $confirmed ? 'Payment Confirmed - WRPlumb' : 'Payment Rejected - WRPlumb',
            $confirmed ? 'Payment Confirmed' : 'Payment Rejected',
            $confirmed
                ? 'Your payment has been verified and confirmed.'
                : 'Your payment could not be verified.',
            $client ? self::routeIfExists('client.payments.show', $payment) : null,
            $client ? 'View Payment' : null,
            [
                'Payment No.' => $payment->payment_no ?: "#{$payment->id}",
                'Invoice' => $payment->invoice?->invoice_no ?: 'Not specified',
                'Amount' => self::money($payment->amount),
                'Status' => ucfirst(str_replace('_', ' ', (string) $payment->status)),
                'Verification Notes' => $payment->verification_notes ?: 'None',
            ]
        );
    }

    public static function receiptGenerated(Receipt $receipt): void
    {
        $receipt->loadMissing('payment.invoice.quotation.request');
        $request = $receipt->payment?->invoice?->quotation?->request;

        if (!$request?->email) {
            return;
        }

        $client = self::clientUser($request->email);

        self::sendToAddress(
            $request->email,
            $request->full_name ?: 'Client',
            "Receipt Ready - {$receipt->receipt_no}",
            'Official Receipt Ready',
            'An official receipt has been generated for your confirmed payment.',
            $client ? self::routeIfExists('client.receipts.show', $receipt) : null,
            $client ? 'View Receipt' : null,
            [
                'Receipt' => $receipt->receipt_no ?: "#{$receipt->id}",
                'Payment No.' => $receipt->payment?->payment_no ?: 'Not specified',
                'Amount Received' => self::money($receipt->amount_received),
                'Receipt Date' => self::dateValue($receipt->receipt_date),
                'Payment Method' => $receipt->payment_method ?: 'Not specified',
                'Reference' => $receipt->reference_number ?: 'Not specified',
            ]
        );
    }

    public static function contractGenerated(Contract $contract): void
    {
        self::contractClientEmail(
            $contract,
            'Contract Ready for Review - WRPlumb',
            'Contract Ready for Review',
            'A service contract is ready for your review.',
            'View Contract'
        );
    }

    public static function contractAccepted(Contract $contract): void
    {
        $contract->loadMissing('quotation.request');
        $request = $contract->quotation?->request;

        foreach (['hr', 'admin'] as $role) {
            self::sendToRole(
                $role,
                'Contract Accepted - WRPlumb',
                'Contract Accepted',
                "{$request?->full_name} accepted contract {$contract->contract_no}.",
                $role === 'hr'
                    ? self::routeIfExists('hr.contracts.show', $contract)
                    : self::routeIfExists('admin.quotations.index'),
                $role === 'hr' ? 'View Contract' : 'View Requests',
                [
                    'Client' => $request?->full_name ?: $contract->client_name ?: 'Client',
                    'Contract' => $contract->contract_no ?: "#{$contract->id}",
                    'Service' => $request?->service_type ?: 'Not specified',
                    'Total' => self::money($contract->total_contract_price),
                ]
            );
        }
    }

    public static function contractFinalized(Contract $contract): void
    {
        self::contractClientEmail(
            $contract,
            'Contract Finalized - WRPlumb',
            'Contract Finalized',
            'Your service contract has been finalized.',
            'View Contract'
        );
    }

    public static function contractVoided(Contract $contract): void
    {
        self::contractClientEmail(
            $contract,
            'Contract Voided - WRPlumb',
            'Contract Voided',
            'Your service contract has been voided. Please review the contract record for details.',
            'View Contract',
            [
                'Void Reason' => $contract->void_reason ?: 'Not specified',
            ]
        );
    }

    private static function contractClientEmail(
        Contract $contract,
        string $subject,
        string $heading,
        string $message,
        string $actionLabel,
        array $extraDetails = []
    ): void {
        $contract->loadMissing('quotation.request');
        $request = $contract->quotation?->request;
        if (!$request?->email) {
            return;
        }

        $client = self::clientUser($request->email);

        self::sendToAddress(
            $request->email,
            $request->full_name ?: $contract->client_name ?: 'Client',
            $subject,
            $heading,
            $message,
            $client ? self::routeIfExists('client.contracts.show', $contract) : null,
            $client ? $actionLabel : null,
            array_merge([
                'Contract' => $contract->contract_no ?: "#{$contract->id}",
                'Service' => $request->service_type ?: 'Not specified',
                'Contract Date' => self::dateValue($contract->contract_date),
                'Total' => self::money($contract->total_contract_price),
                'Status' => ucfirst((string) $contract->status),
            ], $extraDetails)
        );
    }

    public static function sendToRole(
        string $role,
        string $subject,
        string $heading,
        string $message,
        ?string $actionUrl = null,
        ?string $actionLabel = null,
        array $details = []
    ): void {
        User::query()
            ->where('role', $role)
            ->where('is_active', true)
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->get(['id', 'name', 'email', 'is_active'])
            ->each(function (User $user) use (
                $subject,
                $heading,
                $message,
                $actionUrl,
                $actionLabel,
                $details
            ) {
                self::sendToUser(
                    $user,
                    $subject,
                    $heading,
                    $message,
                    $actionUrl,
                    $actionLabel,
                    $details
                );
            });
    }

    public static function sendToUser(
        User $user,
        string $subject,
        string $heading,
        string $message,
        ?string $actionUrl = null,
        ?string $actionLabel = null,
        array $details = []
    ): void {
        if (!$user->email || !$user->is_active) {
            return;
        }

        self::sendToAddress(
            $user->email,
            $user->name ?: 'WRPlumb User',
            $subject,
            $heading,
            $message,
            $actionUrl,
            $actionLabel,
            $details,
            $user->id
        );
    }

    public static function sendToAddress(
        string $email,
        string $recipientName,
        string $subject,
        string $heading,
        string $message,
        ?string $actionUrl = null,
        ?string $actionLabel = null,
        array $details = [],
        ?int $userId = null
    ): void {
        if (trim($email) === '') {
            return;
        }

        try {
            Mail::send('emails.workflow-notification', [
                'recipientName' => $recipientName ?: 'WRPlumb User',
                'heading' => $heading,
                'bodyMessage' => $message,
                'actionUrl' => $actionUrl,
                'actionLabel' => $actionLabel,
                'details' => $details,
            ], function ($mail) use ($email, $recipientName, $subject) {
                $mail->to($email, $recipientName ?: null)
                    ->subject($subject);
            });
        } catch (Throwable $e) {
            Log::warning('WRPlumb workflow email failed.', [
                'user_id' => $userId,
                'email' => $email,
                'subject' => $subject,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private static function clientUser(?string $email): ?User
    {
        if (!$email) {
            return null;
        }

        return User::query()
            ->where('role', 'client')
            ->where('email', $email)
            ->where('is_active', true)
            ->first();
    }

    private static function routeIfExists(string $name, mixed $parameter = null): ?string
    {
        if (!Route::has($name)) {
            return null;
        }

        return $parameter === null
            ? route($name)
            : route($name, $parameter);
    }

    private static function dateValue(mixed $value): string
    {
        if (!$value) {
            return 'Not specified';
        }

        try {
            return Carbon::parse($value)->format('M d, Y');
        } catch (Throwable) {
            return (string) $value;
        }
    }

    private static function timeValue(mixed $value): string
    {
        if (!$value) {
            return 'Not specified';
        }

        try {
            return Carbon::parse($value)->format('h:i A');
        } catch (Throwable) {
            return (string) $value;
        }
    }

    private static function money(mixed $value): string
    {
        return 'PHP ' . number_format((float) $value, 2);
    }
}
