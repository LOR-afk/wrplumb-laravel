<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupportConversation extends Model
{
    protected $fillable = [
        'client_id',
        'status',
        'current_queue',
        'routed_to',
        'routed_at',
        'escalated_to',
        'escalated_at',
        'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'routed_at' => 'datetime',
            'escalated_at' => 'datetime',
            'resolved_at' => 'datetime',
        ];
    }

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function messages()
    {
        return $this->hasMany(SupportMessage::class, 'conversation_id');
    }
}