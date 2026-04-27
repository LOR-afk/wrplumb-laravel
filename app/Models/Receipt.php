<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Receipt extends Model
{
    protected $fillable = [
        'payment_id',
        'receipt_no',
        'receipt_date',
        'amount_received',
        'payment_method',
        'reference_number',
        'notes',
        'issued_by',
        'issued_at',
    ];

    protected function casts(): array
    {
        return [
            'receipt_date' => 'date',
            'amount_received' => 'decimal:2',
            'issued_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    public function issuer()
    {
        return $this->belongsTo(User::class, 'issued_by');
    }
}