<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InspectionStatusLog extends Model
{
    protected $fillable = [
        'quotation_request_id',
        'inspection_report_id',
        'updated_by',
        'status',
        'notes',
        'recorded_at',
    ];

    protected function casts(): array
    {
        return [
            'recorded_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function quotationRequest()
    {
        return $this->belongsTo(
            QuotationRequest::class,
            'quotation_request_id'
        );
    }

    public function inspectionReport()
    {
        return $this->belongsTo(InspectionReport::class);
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}