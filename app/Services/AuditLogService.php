<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request as RequestFacade;

class AuditLogService
{
    public static function log(
        string $action,
        ?string $module = null,
        ?Model $record = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?string $description = null
    ): AuditLog {
        $user = Auth::user();

        return AuditLog::create([
            'user_id' => $user?->id,
            'user_name' => $user?->name,
            'user_email' => $user?->email,
            'role' => $user?->role,

            'action' => $action,
            'module' => $module,

            'record_type' => $record ? get_class($record) : null,
            'record_id' => $record?->getKey(),

            'old_values' => $oldValues,
            'new_values' => $newValues,
            'description' => $description,

            'ip_address' => RequestFacade::ip(),
            'user_agent' => RequestFacade::userAgent(),
        ]);
    }
}