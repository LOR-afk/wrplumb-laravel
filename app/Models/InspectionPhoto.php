<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InspectionPhoto extends Model
{
    protected $fillable = [
        'inspection_report_id',
        'uploaded_by',
        'category',
        'file_path',
        'original_name',
        'mime_type',
        'file_size',
        'caption',
    ];

    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function inspectionReport()
    {
        return $this->belongsTo(InspectionReport::class);
    }

    public function uploadedBy()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getUrlAttribute(): string
    {
        return asset('storage/' . $this->file_path);
    }

    public function getCategoryLabelAttribute(): string
    {
        return match ($this->category) {
            'before' => 'Before Inspection',
            'during' => 'During Inspection',
            'after' => 'After Inspection',
            default => ucfirst($this->category),
        };
    }
}