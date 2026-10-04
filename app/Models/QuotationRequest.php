<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Quotation;
use App\Models\JobOrder;
use App\Models\InspectionReport;
use App\Models\InspectionStatusLog;
use App\Models\QuotationRequestImage;


class QuotationRequest extends Model
{
    protected $fillable = [
        'first_name',
        'middle_initial',
        'last_name',
        'email',
        'phone',
        'service_category',
        'service_type',
        'project_type',
        'preferred_date',
        'preferred_time',
        'address',
        'details',
        'status',
        'worker_id',
        'assigned_by',
        'assigned_at',
        'admin_notes',
        'inspector_notes',
        'inspected_at',
        'completed_at',
        'appointment_status',
        'appointment_date',
        'appointment_time',
        'approved_at',
        'rescheduled_at',
        'cancelled_at',
        'cancel_reason',
        'client_action_request',
        'client_requested_date',
        'client_requested_time',
        'client_request_reason',
        'client_requested_at',
        'client_request_reviewed_at',
        'client_request_review_notes',
        'client_action_status',
        'service_flow',
        'visit_purpose',
        'flow_source',
        'flow_override_reason',
        'ready_for_quotation_at',
        'forwarded_to_hr_by',
        'quotation_handoff_notes',
        'latitude',
        'longitude',
    ];

    protected function casts(): array
    {
        return [
            'preferred_date' => 'date',
            'preferred_time' => 'string',
            'appointment_date' => 'date',
            'appointment_time' => 'string',
            'client_requested_date' => 'date',
            'assigned_at' => 'datetime',
            'approved_at' => 'datetime',
            'rescheduled_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'inspected_at' => 'datetime',
            'completed_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'client_requested_at' => 'datetime',
            'client_request_reviewed_at' => 'datetime',
            'ready_for_quotation_at' => 'datetime',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
        ];
    }

    public function worker()
    {
        return $this->belongsTo(User::class, 'worker_id');
    }

    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function getFullNameAttribute(): string
    {
        return trim(
            $this->first_name . ' ' .
            ($this->middle_initial ? $this->middle_initial . '. ' : '') .
            $this->last_name
        );
    }

    public function quotation()
    {
        return $this->hasOne(Quotation::class, 'quotation_request_id');
    }

    public function jobOrder()
    {
        return $this->hasOne(JobOrder::class, 'quotation_request_id');
    }

    public function scopeActive($query)
    {
        return $query->whereNull('archived_at');
    }

    public function scopeArchived($query)
    {
        return $query->whereNotNull('archived_at');
    }

    public function forwardedToHrBy()
    {
        return $this->belongsTo(User::class, 'forwarded_to_hr_by');
    }

    public function inspectionReport()
    {
        return $this->hasOne(
            InspectionReport::class,
            'quotation_request_id'
        );
    }

    public function inspectionStatusLogs()
    {
        return $this->hasMany(
            InspectionStatusLog::class,
            'quotation_request_id'
        )->latest('recorded_at');
    }

    public function images()
    {
        return $this->hasMany(
            QuotationRequestImage::class,
            'quotation_request_id'
        )->latest();
    }
}
