<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuotationScopeItem extends Model
{
    protected $fillable = [
        'quotation_id',
        'description',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    public function quotation()
    {
        return $this->belongsTo(Quotation::class);
    }
}
