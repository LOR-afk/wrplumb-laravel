<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuotationRequestImage extends Model
{
    protected $fillable = [
        'quotation_request_id',
        'uploaded_by',
        'file_path',
        'original_name',
        'mime_type',
        'file_size',
    ];

    public function quotationRequest()
    {
        return $this->belongsTo(
            QuotationRequest::class,
            'quotation_request_id'
        );
    }

    public function uploadedBy()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getUrlAttribute(): string
    {
        return asset('storage/' . $this->file_path);
    }
}
