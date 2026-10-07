<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quotation extends Model
{
    protected $fillable = [
        'quotation_request_id',
        'quotation_no',
        'status',
        'quotation_format',
        'quotation_template_id',
        'project_name',
        'project_location',
        'subject',
        'custom_document_path',
        'custom_document_name',
        'custom_document_mime',
        'custom_document_size',
        'generated_document_path',
        'generated_document_name',
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
            'custom_document_size' => 'integer',
            'sent_at' => 'datetime',
            'approved_at' => 'datetime',
            'rejected_at' => 'datetime',
            'accepted_at' => 'datetime',
            'declined_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function request()
    {
        return $this->belongsTo(QuotationRequest::class, 'quotation_request_id');
    }

    public function items()
    {
        return $this->hasMany(QuotationItem::class, 'quotation_id');
    }

    public function scopeItems()
    {
        return $this->hasMany(QuotationScopeItem::class, 'quotation_id')
            ->orderBy('sort_order');
    }

    public function template()
    {
        return $this->belongsTo(QuotationTemplate::class, 'quotation_template_id');
    }

    public function preparedBy()
    {
        return $this->belongsTo(User::class, 'prepared_by');
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class, 'quotation_id');
    }

    public function contract()
    {
        return $this->hasOne(Contract::class);
    }

    public function scopeActive($query)
    {
        return $query->whereNull('archived_at');
    }

    public function scopeArchived($query)
    {
        return $query->whereNotNull('archived_at');
    }

    public function getIsCustomFormatAttribute(): bool
    {
        return $this->quotation_format === 'custom';
    }

    public function getCustomDocumentUrlAttribute(): ?string
    {
        if (!$this->custom_document_path) {
            return null;
        }

        return asset('storage/' . ltrim($this->custom_document_path, '/'));
    }
}
