<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\Quotation;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Contract;
use App\Models\Receipt;
use App\Models\JobOrder;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'username',
        'first_name',
        'middle_initial',
        'last_name',
        'email',
        'phone',
        'address',
        'role',
        'is_active',
        'password',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function adminOtps()
    {
        return $this->hasMany(AdminLoginOtp::class);
    }

    public function assignedQuotations()
    {
        return $this->hasMany(QuotationRequest::class, 'worker_id');
    }

    public function inspectorAvailabilities()
    {
        return $this->hasMany(\App\Models\InspectorAvailability::class, 'inspector_id');
    }
    public function alerts()
    {
    return $this->hasMany(\App\Models\UserAlert::class)->latest();
    }

    public function preparedQuotations()
    {
        return $this->hasMany(Quotation::class, 'prepared_by');
    }

    public function createdInvoices()
    {
        return $this->hasMany(Invoice::class, 'created_by');
    }

    public function receivedPayments()
    {
        return $this->hasMany(Payment::class, 'received_by');
    }

    public function submittedPayments()
    {
        return $this->hasMany(Payment::class, 'submitted_by');
    }

    public function verifiedPayments()
    {
        return $this->hasMany(Payment::class, 'verified_by');
    }

    public function generatedContracts()
    {
        return $this->hasMany(Contract::class, 'generated_by');
    }

    public function issuedReceipts()
    {
        return $this->hasMany(Receipt::class, 'issued_by');
    }

    public function assignedJobOrders()
    {
        return $this->hasMany(JobOrder::class, 'worker_id');
    }

    public function createdJobOrders()
    {
        return $this->hasMany(JobOrder::class, 'created_by');
    }
}