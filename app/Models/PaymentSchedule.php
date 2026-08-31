<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentSchedule extends Model
{
    protected $fillable = [
        'invoice_id',
        'label',
        'percent',
        'amount_due',
        'amount_paid',
        'due_date',
        'status',
        'milestone_status',
        'ready_for_billing_at',
        'ready_for_billing_by',
        'milestone_notes',
        'notes',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'percent' => 'decimal:2',
            'amount_due' => 'decimal:2',
            'amount_paid' => 'decimal:2',
            'due_date' => 'date',
            'ready_for_billing_at' => 'datetime',
            'sort_order' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function readyForBillingBy()
    {
        return $this->belongsTo(User::class, 'ready_for_billing_by');
    }

    public function getIsReadyForBillingAttribute(): bool
    {
        return in_array($this->milestone_status, [
            'ready_for_billing',
            'billed',
            'paid',
        ], true);
    }

    public function getRemainingAmountAttribute(): float
    {
        return round(max(
            0,
            (float) $this->amount_due - (float) $this->amount_paid
        ), 2);
    }
}