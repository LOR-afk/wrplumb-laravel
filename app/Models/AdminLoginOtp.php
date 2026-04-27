<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminLoginOtp extends Model
{
    protected $fillable = [
        'user_id',
        'otp_code',
        'expires_at',
        'used_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'used_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}