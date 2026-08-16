<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class AuditService
{
    /**
     * @param  array<string, mixed>|null  $oldValues
     * @param  array<string, mixed>|null  $newValues
     */
    public function log(
        string $action,
        Model $auditable,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?User $user = null,
    ): AuditLog {
        return AuditLog::create([
            'user_id' => $user->id ?? auth()->id(),
            'action' => $action,
            'auditable_type' => $auditable::class,
            'auditable_id' => $auditable->getKey(),
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    public function assignmentCreated(Model $assignment, ?User $user = null): AuditLog
    {
        return $this->log('assignment.created', $assignment, null, $assignment->toArray(), $user);
    }

    public function assignmentTransition(Model $assignment, string $toStatus, ?string $oldStatus = null, ?User $user = null): AuditLog
    {
        return $this->log(
            "assignment.{$toStatus}",
            $assignment,
            $oldStatus ? ['status' => $oldStatus] : null,
            ['status' => $toStatus],
            $user,
        );
    }
}
