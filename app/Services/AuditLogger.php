<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class AuditLogger
{
    public function log(
        string $module,
        string $action,
        ?Model $subject = null,
        ?string $description = null,
        ?array $oldValues = null,
        ?array $newValues = null
    ): AuditLog {
        $request = request();

        return AuditLog::create([
            'user_id' => Auth::id(),
            'module' => $module,
            'action' => $action,
            'subject_type' => $subject ? get_class($subject) : null,
            'subject_id' => $subject?->getKey(),
            'description' => $description,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
        ]);
    }
}
