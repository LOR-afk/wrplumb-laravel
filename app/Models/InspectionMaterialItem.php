<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InspectionMaterialItem extends Model
{
    protected $fillable = [
        'inspection_report_id',
        'item_name',
        'quantity',
        'unit',
        'unit_cost',
        'subtotal',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'unit_cost' => 'decimal:2',
            'subtotal' => 'decimal:2',
        ];
    }

    public function inspectionReport()
    {
        return $this->belongsTo(InspectionReport::class);
    }
}