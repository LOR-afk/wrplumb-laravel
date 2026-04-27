<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobOrder extends Model
{
    protected $fillable = [
        'quotation_request_id',
        'worker_id',
        'job_order_no',
        'service_flow',
        'service_type',
        'project_type',
        'scheduled_date',
        'scheduled_time',
        'status',
        'scope_of_work',
        'admin_notes',
        'work_remarks',
        'completion_notes',
        'started_at',
        'completed_at',
        'cancelled_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_date' => 'date',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function quotationRequest()
    {
        return $this->belongsTo(QuotationRequest::class, 'quotation_request_id');
    }

    public function worker()
    {
        return $this->belongsTo(User::class, 'worker_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}