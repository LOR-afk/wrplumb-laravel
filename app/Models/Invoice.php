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

    public function getRemainingBalanceAttribute(): float
    {
        $paid = (float) $this->paymentSchedules()->sum('amount_paid');

        return max(0, (float) $this->total_amount - $paid);
    }
}