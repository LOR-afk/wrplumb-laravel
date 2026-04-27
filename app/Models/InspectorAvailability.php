<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InspectorAvailability extends Model
{
    protected $fillable = [
        'inspector_id',
        'availability_date',
        'start_time',
        'end_time',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'availability_date' => 'date',
        ];
    }

    public function inspector()
    {
        return $this->belongsTo(User::class, 'inspector_id');
    }
}