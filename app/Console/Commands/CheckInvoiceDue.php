<?php

namespace App\Console\Commands;

use App\Mail\PaymentReminderMail;
use App\Models\Invoice;
use App\Models\UserAlert;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class CheckInvoiceDue extends Command
{
    protected $signature = 'invoices:check-due';

    protected $description = 'Check due invoices and send client payment reminders';

    public function handle()
    {
        $invoices = Invoice::with('quotation.request.user')
            ->where('status', 'unpaid')
            ->whereDate('due_date', '>=', today())
            ->whereDate('due_date', '<=', today()->addDays(3))
            ->get();

        $alertCount = 0;
        $emailCount = 0;

        foreach ($invoices as $invoice) {
            $quotationRequest = $invoice->quotation?->request;
            $user = $quotationRequest?->user;

            if (!$user) {
                continue;
            }

            $email = $quotationRequest?->email ?? $user->email;

            $invoiceLink = route(
                'client.invoices.show',
                $invoice
            );

            $alertExists = UserAlert::where('user_id', $user->id)
                ->where('type', 'payment_due')
                ->where('link', $invoiceLink)
                ->exists();

            if (!$alertExists) {
                UserAlert::create([
                    'user_id' => $user->id,
                    'type' => 'payment_due',
                    'title' => 'Payment Reminder',
                    'message' => 'Invoice ' . $invoice->invoice_no .
                        ' is due on ' .
                        \Carbon\Carbon::parse($invoice->due_date)->format('F d, Y') .
                        '.',
                    'link' => $invoiceLink,
                    'is_read' => false,
                ]);

                $alertCount++;

                if ($email) {
                    Mail::to($email)->send(
                        new PaymentReminderMail($invoice)
                    );

                    $emailCount++;
                }
            }
        }

        $this->info(
            "Payment reminders completed. Alerts: {$alertCount}, Emails: {$emailCount}"
        );

        return self::SUCCESS;
    }
}