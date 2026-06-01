<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BackJob extends Model
{
    protected $fillable = [
        'warranty_claim_id',
        'original_job_order_id',
        'worker_id',
        'backjob_no',
        'reason',
        'scheduled_date',
        'scheduled_time',
        'status',
        'resolution_notes',
        'admin_notes',
        'created_by',
        'scheduled_by',
        'started_at',
        'resolved_at',
        'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_date' => 'date',
            'started_at' => 'datetime',
            'resolved_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function warrantyClaim()
    {
        return $this->belongsTo(WarrantyClaim::class);
    }

    public function originalJobOrder()
    {
        return $this->belongsTo(JobOrder::class, 'original_job_order_id');
    }

    public function worker()
    {
        return $this->belongsTo(User::class, 'worker_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scheduler()
    {
        return $this->belongsTo(User::class, 'scheduled_by');
    }

    public function getIsScheduledAttribute(): bool
    {
        return $this->status === 'scheduled';
    }

    public function getIsInProgressAttribute(): bool
    {
        return $this->status === 'in_progress';
    }

    public function getIsResolvedAttribute(): bool
    {
        return $this->status === 'resolved';
    }

    public function getIsCancelledAttribute(): bool
    {
        return $this->status === 'cancelled';
    }
}