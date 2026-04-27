<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FaqEntry extends Model
{
    protected $fillable = [
        'question',
        'answer',
        'keywords',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}