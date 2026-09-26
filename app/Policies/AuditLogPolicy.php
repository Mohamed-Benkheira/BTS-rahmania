<?php

namespace App\Policies;

use App\Models\User;

class AuditLogPolicy extends GenericPolicy
{
    protected static string $resource = 'audit logs';

    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin') || $this->permission($user, 'view');
    }

    public function view(User $user, object $model): bool
    {
        return $user->hasRole('admin') || $this->permission($user, 'view');
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, object $model): bool
    {
        return false;
    }

    public function delete(User $user, object $model): bool
    {
        return false;
    }

    public function restore(User $user, object $model): bool
    {
        return false;
    }

    public function forceDelete(User $user, object $model): bool
    {
        return false;
    }
}
