<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contract extends Model
{
    protected $fillable = [
        'quotation_id',
        'contract_no',
        'title',
        'contract_date',
        'start_date',
        'end_date',
        'status',
        'client_name',
        'client_address',
        'project_address',
        'scope_of_work',
        'payment_terms',
        'total_contract_price',
        'special_terms',
        'generated_by',
        'sent_at',
        'client_accepted_at',
        'finalized_at',
    ];

    protected function casts(): array
    {
        return [
            'contract_date' => 'date',
            'start_date' => 'date',
            'end_date' => 'date',
            'payment_terms' => 'array',
            'total_contract_price' => 'decimal:2',
            'sent_at' => 'datetime',
            'client_accepted_at' => 'datetime',
            'finalized_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function quotation()
    {
        return $this->belongsTo(Quotation::class);
    }

    public function generator()
    {
        return $this->belongsTo(User::class, 'generated_by');
    }
}