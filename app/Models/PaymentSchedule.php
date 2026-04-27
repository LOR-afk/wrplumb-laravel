<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Payment;

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
}