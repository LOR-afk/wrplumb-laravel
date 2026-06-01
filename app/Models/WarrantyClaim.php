<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WarrantyClaim extends Model
{
    protected $fillable = [
        'job_order_id',
        'client_id',
        'claim_no',
        'issue_description',
        'preferred_date',
        'preferred_time',
        'status',
        'eligibility_status',
        'warranty_expires_at',
        'admin_notes',
        'reviewed_by',
        'reviewed_at',
        'approved_at',
        'rejected_at',
        'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'preferred_date' => 'date',
            'warranty_expires_at' => 'datetime',
            'reviewed_at' => 'datetime',
            'approved_at' => 'datetime',
            'rejected_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function jobOrder()
    {
        return $this->belongsTo(JobOrder::class);
    }

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function backJob()
    {
        return $this->hasOne(BackJob::class);
    }

    public function getIsPendingAttribute(): bool
    {
        return $this->status === 'pending';
    }

    public function getIsApprovedAttribute(): bool
    {
        return $this->status === 'approved';
    }

    public function getIsRejectedAttribute(): bool
    {
        return $this->status === 'rejected';
    }
}