<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\PaymentSchedule;
use App\Models\Payment;

class Invoice extends Model
{
    protected $fillable = [
        'quotation_id',
        'invoice_no',
        'status',
        'invoice_date',
        'due_date',
        'description',
        'total_amount',
        'created_by',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'invoice_date' => 'date',
            'due_date' => 'date',
            'total_amount' => 'decimal:2',
            'paid_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function quotation()
    {
        return $this->belongsTo(Quotation::class);
    }

    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function paymentSchedules()
    {
        return $this->hasMany(PaymentSchedule::class)->orderBy('sort_order');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class)->latest('payment_date');
    }

    public function confirmedPayments()
    {
        return $this->hasMany(Payment::class)->where('status', 'confirmed');
    }

    public function getPaidAmountAttribute(): float
    {
        if ($this->relationLoaded('payments')) {
            return round((float) $this->payments
                ->where('status', 'confirmed')
                ->sum('amount'), 2);
        }

        return round((float) $this->payments()
            ->where('status', 'confirmed')
            ->sum('amount'), 2);
    }

    public function getRemainingBalanceAttribute(): float
    {
        return round(max(0, (float) $this->total_amount - $this->paid_amount), 2);
    }

    public function getPaymentProgressAttribute(): float
    {
        $total = (float) $this->total_amount;

        if ($total <= 0) {
            return 0;
        }

        return round(min(($this->paid_amount / $total) * 100, 100), 2);
    }

    public function syncPaymentStatus(): void
    {
        $paid = $this->paid_amount;
        $total = (float) $this->total_amount;

        if ($paid <= 0) {
            $this->status = 'unpaid';
            $this->paid_at = null;
        } elseif ($paid < $total) {
            $this->status = 'partially_paid';
            $this->paid_at = null;
        } else {
            $this->status = 'paid';
            $this->paid_at = $this->paid_at ?? now();
        }

        $this->saveQuietly();
    }
}