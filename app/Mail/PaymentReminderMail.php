<?php

namespace App\Mail;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Invoice $invoice
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Payment Reminder - Invoice ' . $this->invoice->invoice_no
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.payment-reminder'
        );
    }

    public function attachments(): array
    {
        return [];
    }
}