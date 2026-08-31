<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InspectionReport extends Model
{
    protected $fillable = [
        'quotation_request_id',
        'inspector_id',
        'report_no',
        'findings',
        'recommendations',
        'client_visible_notes',
        'internal_notes',
        'estimated_material_cost',
        'estimated_labor_cost',
        'estimated_miscellaneous_cost',
        'estimated_total_cost',
        'status',
        'inspection_started_at',
        'submitted_at',
        'reviewed_at',
        'reviewed_by',
        'review_notes',
    ];

    protected function casts(): array
    {
        return [
            'estimated_material_cost' => 'decimal:2',
            'estimated_labor_cost' => 'decimal:2',
            'estimated_miscellaneous_cost' => 'decimal:2',
            'estimated_total_cost' => 'decimal:2',
            'inspection_started_at' => 'datetime',
            'submitted_at' => 'datetime',
            'reviewed_at' => 'datetime',
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

    public function inspector()
    {
        return $this->belongsTo(User::class, 'inspector_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function statusLogs()
    {
        return $this->hasMany(InspectionStatusLog::class)
            ->latest('recorded_at');
    }

    public function photos()
    {
        return $this->hasMany(InspectionPhoto::class)
            ->latest();
    }

    public function checklistItems()
    {
        return $this->hasMany(InspectionChecklistItem::class)
            ->orderBy('id');
    }

    public function materialItems()
    {
        return $this->hasMany(InspectionMaterialItem::class)
            ->orderBy('id');
}
}