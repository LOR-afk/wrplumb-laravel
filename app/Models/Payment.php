<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Receipt;

class Payment extends Model
{
    protected $fillable = [
        'invoice_id',
        'payment_schedule_id',
        'submitted_by',
        'received_by',
        'verified_by',
        'payment_no',
        'payment_type',
        'payment_method',
        'reference_number',
        'proof_path',
        'amount',
        'payment_date',
        'status',
        'notes',
        'verified_at',
        'verification_notes',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'payment_date' => 'date',
            'verified_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function paymentSchedule()
    {
        return $this->belongsTo(PaymentSchedule::class);
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function submitter()
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function receipt()
    {
        return $this->hasOne(Receipt::class);
    }
}