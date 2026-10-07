<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuotationTemplate extends Model
{
    protected $fillable = [
        'name',
        'file_path',
        'original_name',
        'is_active',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function quotations()
    {
        return $this->hasMany(Quotation::class, 'quotation_template_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
