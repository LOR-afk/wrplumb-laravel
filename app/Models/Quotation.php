<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Invoice;
use App\Models\Contract;

class Quotation extends Model
{
    protected $fillable = [
        'quotation_request_id',
        'quotation_no',
        'status',
        'notes',
        'payment_plan',
        'payment_terms_json',
        'materials_cost',
        'labor_cost',
        'miscellaneous_cost',
        'subtotal_amount',
        'tax_rate',
        'tax_amount',
        'grand_total',
        'prepared_by',
        'sent_at',
        'approved_at',
        'rejected_at',
        'acceptance_token',
        'client_response',
        'accepted_at',
        'declined_at',
    ];

    protected function casts(): array
    {
        return [
            'payment_terms_json' => 'array',
            'materials_cost' => 'decimal:2',
            'labor_cost' => 'decimal:2',
            'miscellaneous_cost' => 'decimal:2',
            'subtotal_amount' => 'decimal:2',
            'tax_rate' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'grand_total' => 'decimal:2',
            'sent_at' => 'datetime',
            'approved_at' => 'datetime',
            'rejected_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'accepted_at' => 'datetime',
            'declined_at' => 'datetime',
        ];
    }

    public function request()
    {
        return $this->belongsTo(QuotationRequest::class, 'quotation_request_id');
    }

    public function items()
    {
        return $this->hasMany(QuotationItem::class);
    }

    public function preparedBy()
    {
        return $this->belongsTo(User::class, 'prepared_by');
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class);
    }

    public function contract()
    {
        return $this->hasOne(Contract::class);
    }
}